<?php
$pageTitle = 'My Products';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a farmer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL);
    exit();
}

$farmer = getFarmerByUserId($_SESSION['user_id']);

// If no farmer profile exists, redirect to setup
if (!$farmer) {
    header('Location: setup.php');
    exit();
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected_products = $_POST['selected_products'] ?? [];
    
    if (!empty($selected_products) && in_array($action, ['activate', 'deactivate', 'delete'])) {
        global $pdo;
        
        try {
            if ($action === 'delete') {
                $placeholders = str_repeat('?,', count($selected_products) - 1) . '?';
                $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders) AND farmer_id = ?");
                $stmt->execute(array_merge($selected_products, [$farmer['id']]));
                showNotification(count($selected_products) . ' products deleted successfully!', 'success');
            } else {
                $is_active = $action === 'activate' ? 1 : 0;
                $placeholders = str_repeat('?,', count($selected_products) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE products SET is_active = ? WHERE id IN ($placeholders) AND farmer_id = ?");
                $stmt->execute(array_merge([$is_active], $selected_products, [$farmer['id']]));
                $actionText = $action === 'activate' ? 'activated' : 'deactivated';
                showNotification(count($selected_products) . " products {$actionText} successfully!", 'success');
            }
        } catch (PDOException $e) {
            showNotification('Error performing bulk action: ' . $e->getMessage(), 'error');
        }
    }
}

// Get filter parameters
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$order = $_GET['order'] ?? 'asc';

// Build query
global $pdo;
$where_conditions = ['farmer_id = ?'];
$params = [$farmer['id']];

if (!empty($category)) {
    $where_conditions[] = 'category = ?';
    $params[] = $category;
}

if ($status !== '') {
    $where_conditions[] = 'is_active = ?';
    $params[] = (int)$status;
}

if (!empty($search)) {
    $where_conditions[] = '(name LIKE ? OR description LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$where_clause = implode(' AND ', $where_conditions);

// Validate sort column
$valid_sorts = ['name', 'category', 'price', 'stock_quantity', 'created_at', 'is_active'];
if (!in_array($sort, $valid_sorts)) {
    $sort = 'name';
}

$order_clause = $sort . ' ' . ($order === 'desc' ? 'DESC' : 'ASC');

$stmt = $pdo->prepare("SELECT * FROM products WHERE {$where_clause} ORDER BY {$order_clause}");
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categoriesStmt = $pdo->prepare("SELECT DISTINCT category FROM products WHERE farmer_id = ? ORDER BY category");
$categoriesStmt->execute([$farmer['id']]);
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

include '../../components/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h2 class="mb-0">🌱 My Products</h2>
                <p class="text-muted mb-0">Manage all your products in one place</p>
            </div>
        </div>
        <a href="add-product.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search Products</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Product name or description...">
                </div>
                
                <div class="col-md-2">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                        <option value="category" <?php echo $sort === 'category' ? 'selected' : ''; ?>>Category</option>
                        <option value="price" <?php echo $sort === 'price' ? 'selected' : ''; ?>>Price</option>
                        <option value="stock_quantity" <?php echo $sort === 'stock_quantity' ? 'selected' : ''; ?>>Stock</option>
                        <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date Added</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label for="order" class="form-label">Order</label>
                    <select class="form-select" id="order" name="order">
                        <option value="asc" <?php echo $order === 'asc' ? 'selected' : ''; ?>>Asc</option>
                        <option value="desc" <?php echo $order === 'desc' ? 'selected' : ''; ?>>Desc</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="products.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Products 
                    <span class="badge bg-primary"><?php echo count($products); ?></span>
                </h5>
                
                <!-- Bulk Actions -->
                <form method="POST" id="bulkActionForm" class="d-flex align-items-center">
                    <select name="bulk_action" class="form-select form-select-sm me-2" style="width: auto;">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirmBulkAction()">
                        Apply
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($products)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-seedling fa-4x text-muted mb-3"></i>
                    <h5>No Products Found</h5>
                    <?php if (!empty($search) || !empty($category) || $status !== ''): ?>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                        <a href="products.php" class="btn btn-outline-primary">Clear Filters</a>
                    <?php else: ?>
                        <p class="text-muted">Start by adding your first product to begin selling</p>
                        <a href="add-product.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Your First Product
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                                </th>
                                <th width="80">Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="selected_products[]" 
                                               value="<?php echo $product['id']; ?>"
                                               form="bulkActionForm"
                                               class="product-checkbox">
                                    </td>
                                    <td>
                                        <img src="<?php echo $product['image'] ? UPLOAD_URL . $product['image'] : SITE_URL . '/assets/images/default-product.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-thumb">
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                            <small class="text-muted"><?php echo strlen($product['description']) > 50 ? substr(htmlspecialchars($product['description']), 0, 50) . '...' : htmlspecialchars($product['description']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge"><?php echo htmlspecialchars($product['category']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo formatPrice($product['price']); ?></strong>
                                        <small class="text-muted d-block">per <?php echo htmlspecialchars($product['unit']); ?></small>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?php echo $product['stock_quantity'] > 10 ? 'high' : ($product['stock_quantity'] > 0 ? 'low' : 'out'); ?>">
                                            <?php echo $product['stock_quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($product['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-outline-primary"
                                               title="Edit Product">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger"
                                                    onclick="deleteProduct(<?php echo $product['id']; ?>)"
                                                    title="Delete Product">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.product-thumb {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
}

.category-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    background: var(--light-green);
    color: var(--dark-green);
}

.stock-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.stock-badge.high {
    background: #d4edda;
    color: #155724;
}

.stock-badge.low {
    background: #fff3cd;
    color: #856404;
}

.stock-badge.out {
    background: #f8d7da;
    color: #721c24;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.card-header {
    background: linear-gradient(135deg, var(--light-green), rgba(76, 175, 80, 0.1));
    border-bottom: 1px solid #eee;
}

.card-header h5 {
    color: var(--dark-green);
}

.table th {
    background: var(--light-green);
    color: var(--dark-green);
    font-weight: 600;
    border-bottom: 2px solid var(--primary-green);
}

.table tbody tr:hover {
    background: rgba(76, 175, 80, 0.05);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.empty-state {
    color: var(--gray-medium);
}

@media (max-width: 768px) {
    .d-flex.align-items-center.justify-content-between {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .product-thumb {
        width: 40px;
        height: 40px;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
}
</style>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function confirmBulkAction() {
    const action = document.querySelector('select[name="bulk_action"]').value;
    const selected = document.querySelectorAll('.product-checkbox:checked').length;
    
    if (!action) {
        alert('Please select an action');
        return false;
    }
    
    if (selected === 0) {
        alert('Please select at least one product');
        return false;
    }
    
    let message = '';
    switch(action) {
        case 'delete':
            message = `Are you sure you want to delete ${selected} product(s)? This action cannot be undone.`;
            break;
        case 'activate':
            message = `Are you sure you want to activate ${selected} product(s)?`;
            break;
        case 'deactivate':
            message = `Are you sure you want to deactivate ${selected} product(s)?`;
            break;
    }
    
    return confirm(message);
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        // Create a form to submit the delete action
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="bulk_action" value="delete">
            <input type="hidden" name="selected_products[]" value="${productId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Update select all checkbox when individual checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('product-checkbox')) {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const selectAll = document.getElementById('selectAll');
        const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
        
        selectAll.checked = checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }
});
</script>

<?php include '../../components/footer.php'; ?>
