<?php
/**
 * UFarmer Product Image Assignment System
 * Assigns appropriate images to existing products based on their names and categories
 */

require_once '../includes/functions.php';

echo "<h1>UFarmer Product Image Assignment</h1>";

// Product name to image mapping
$productImageMap = [
    // Vegetables
    'tomatoes' => 'vegetables/tomatoes-premium.jpg',
    'heirloom tomatoes' => 'vegetables/tomatoes-heirloom.jpg', 
    'organic tomatoes' => 'vegetables/tomatoes-organic.jpg',
    'cherry tomatoes' => 'vegetables/tomatoes-fresh.jpg',
    'spinach' => 'vegetables/spinach-fresh.jpg',
    'organic spinach' => 'vegetables/spinach-organic.jpg',
    'baby spinach' => 'vegetables/spinach-baby.jpg',
    'carrots' => 'vegetables/carrots-orange.jpg',
    'organic carrots' => 'vegetables/carrots-organic.jpg',
    'potatoes' => 'vegetables/potatoes-russet.jpg',
    'sweet potatoes' => 'vegetables/potatoes-sweet.jpg',
    'corn' => 'vegetables/corn-sweet.jpg',
    'sweet corn' => 'vegetables/corn-sweet.jpg',
    'lettuce' => 'vegetables/lettuce-green.jpg',
    'mixed salad greens' => 'vegetables/salad-mixed.jpg',
    'mixed greens' => 'vegetables/leafy-greens.jpg',
    'salad greens' => 'vegetables/salad-mixed.jpg',
    'bell peppers' => 'vegetables/peppers-bell.jpg',
    'peppers' => 'vegetables/peppers-mixed.jpg',
    'cucumbers' => 'vegetables/cucumbers-fresh.jpg',
    'zucchini' => 'vegetables/zucchini-green.jpg',
    'broccoli' => 'vegetables/broccoli-fresh.jpg',
    'cauliflower' => 'vegetables/cauliflower-white.jpg',
    'cabbage' => 'vegetables/cabbage-green.jpg',
    'kale' => 'vegetables/kale-curly.jpg',
    'onions' => 'vegetables/onions-yellow.jpg',
    'garlic' => 'vegetables/garlic-bulbs.jpg',
    'beets' => 'vegetables/beets-red.jpg',
    'radishes' => 'vegetables/radishes-red.jpg',
    'turnips' => 'vegetables/turnips-white.jpg',
    'bok choy' => 'vegetables/bok-choy.jpg',
    'baby bok choy' => 'vegetables/bok-choy-baby.jpg',
    
    // Fruits
    'strawberries' => 'fruits/strawberries-fresh.jpg',
    'fresh strawberries' => 'fruits/strawberries-premium.jpg',
    'organic strawberries' => 'fruits/strawberries-organic.jpg',
    'apples' => 'fruits/apples-mixed.jpg',
    'honeycrisp apples' => 'fruits/apples-honeycrisp.jpg',
    'granny smith apples' => 'fruits/apples-green.jpg',
    'berries' => 'fruits/berries-mixed.jpg',
    'mixed berries' => 'fruits/berries-assorted.jpg',
    'blueberries' => 'fruits/blueberries-fresh.jpg',
    'blackberries' => 'fruits/blackberries-ripe.jpg',
    'raspberries' => 'fruits/raspberries-red.jpg',
    'peaches' => 'fruits/peaches-ripe.jpg',
    'fresh peaches' => 'fruits/peaches-fresh.jpg',
    'pears' => 'fruits/pears-golden.jpg',
    'plums' => 'fruits/plums-purple.jpg',
    'cherries' => 'fruits/cherries-sweet.jpg',
    'grapes' => 'fruits/grapes-purple.jpg',
    'oranges' => 'fruits/oranges-navel.jpg',
    'lemons' => 'fruits/lemons-meyer.jpg',
    'limes' => 'fruits/limes-green.jpg',
    'watermelon' => 'fruits/watermelon-red.jpg',
    'cantaloupe' => 'fruits/cantaloupe-orange.jpg',
    'honeydew' => 'fruits/honeydew-green.jpg',
    
    // Herbs
    'basil' => 'herbs/basil-sweet.jpg',
    'fresh basil' => 'herbs/basil-fresh.jpg',
    'organic basil' => 'herbs/basil-organic.jpg',
    'cilantro' => 'herbs/cilantro-fresh.jpg',
    'parsley' => 'herbs/parsley-flat.jpg',
    'oregano' => 'herbs/oregano-fresh.jpg',
    'thyme' => 'herbs/thyme-garden.jpg',
    'rosemary' => 'herbs/rosemary-fresh.jpg',
    'sage' => 'herbs/sage-purple.jpg',
    'mint' => 'herbs/mint-spearmint.jpg',
    'dill' => 'herbs/dill-fresh.jpg',
    'chives' => 'herbs/chives-green.jpg',
    'herbs' => 'herbs/herbs-mixed.jpg',
    'mixed herbs' => 'herbs/herbs-assorted.jpg',
    
    // Dairy & Farm Products
    'eggs' => 'dairy/eggs-brown.jpg',
    'farm eggs' => 'dairy/eggs-farm-fresh.jpg',
    'free range eggs' => 'dairy/eggs-free-range.jpg',
    'organic eggs' => 'dairy/eggs-organic.jpg',
    'milk' => 'dairy/milk-whole.jpg',
    'fresh milk' => 'dairy/milk-farm-fresh.jpg',
    'whole milk' => 'dairy/milk-whole-glass.jpg',
    'raw milk' => 'dairy/milk-raw.jpg',
    'cheese' => 'dairy/cheese-farmhouse.jpg',
    'farm cheese' => 'dairy/cheese-artisan.jpg',
    'goat cheese' => 'dairy/cheese-goat.jpg',
    'cheddar cheese' => 'dairy/cheese-cheddar.jpg',
    'butter' => 'dairy/butter-fresh.jpg',
    'farm butter' => 'dairy/butter-homemade.jpg',
    'yogurt' => 'dairy/yogurt-plain.jpg',
    'greek yogurt' => 'dairy/yogurt-greek.jpg',
    'cream' => 'dairy/cream-heavy.jpg',
    'sour cream' => 'dairy/cream-sour.jpg',
    
    // Specialty & Processed
    'honey' => 'specialty/honey-raw.jpg',
    'raw honey' => 'specialty/honey-wildflower.jpg',
    'maple syrup' => 'specialty/syrup-maple.jpg',
    'jam' => 'specialty/jam-strawberry.jpg',
    'preserves' => 'specialty/preserves-mixed.jpg',
    'pickles' => 'specialty/pickles-dill.jpg',
    'salsa' => 'specialty/salsa-fresh.jpg',
    'bread' => 'specialty/bread-sourdough.jpg',
    'sourdough bread' => 'specialty/bread-artisan.jpg',
    
    // Nuts & Seeds
    'almonds' => 'nuts/almonds-raw.jpg',
    'walnuts' => 'nuts/walnuts-shelled.jpg',
    'sunflower seeds' => 'seeds/sunflower-seeds.jpg',
    'pumpkin seeds' => 'seeds/pumpkin-seeds.jpg'
];

// Farmer profile image assignment based on farm names
$farmerImageMap = [
    'green valley' => 'farmers/farmer-organic-male.jpg',
    'sunshine' => 'farmers/farmer-female-greenhouse.jpg', 
    'harvest moon' => 'farmers/farmer-elderly-wise.jpg',
    'meadowbrook' => 'farmers/farmer-dairy-specialist.jpg',
    'golden harvest' => 'farmers/farmer-orchard-keeper.jpg',
    'valley fresh' => 'farmers/farmer-young-energetic.jpg',
    'mountain view' => 'farmers/farmer-highland-grower.jpg',
    'riverside' => 'farmers/farmer-irrigation-expert.jpg',
    'prairie winds' => 'farmers/farmer-grain-specialist.jpg',
    'coastal' => 'farmers/farmer-seaside-grower.jpg'
];

// Category default images
$categoryDefaults = [
    'vegetables' => 'vegetables/vegetables-mixed.jpg',
    'fruits' => 'fruits/fruits-basket.jpg', 
    'herbs' => 'herbs/herbs-garden.jpg',
    'dairy' => 'dairy/dairy-products.jpg',
    'specialty' => 'specialty/farm-products.jpg',
    'nuts' => 'nuts/nuts-mixed.jpg',
    'seeds' => 'seeds/seeds-variety.jpg'
];

/**
 * Find the best matching image for a product name
 */
function findProductImage($productName, $category = null) {
    global $productImageMap, $categoryDefaults;
    
    $name = strtolower(trim($productName));
    
    // First, try exact match
    if (isset($productImageMap[$name])) {
        return $productImageMap[$name];
    }
    
    // Then try partial matches
    foreach ($productImageMap as $pattern => $image) {
        if (strpos($name, $pattern) !== false || strpos($pattern, $name) !== false) {
            return $image;
        }
    }
    
    // Finally, use category default
    if ($category && isset($categoryDefaults[$category])) {
        return $categoryDefaults[$category];
    }
    
    return 'default-product.jpg';
}

/**
 * Find the best matching image for a farmer
 */
function findFarmerImage($farmName) {
    global $farmerImageMap;
    
    $name = strtolower(trim($farmName));
    
    foreach ($farmerImageMap as $pattern => $image) {
        if (strpos($name, $pattern) !== false) {
            return $image;
        }
    }
    
    return 'default-farmer.jpg';
}

// Update product images in database
try {
    $stmt = $pdo->prepare("SELECT id, name, category FROM products");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updatedProducts = 0;
    
    echo "<h2>Updating Product Images</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Product Name</th><th>Category</th><th>Assigned Image</th><th>Status</th></tr>";
    
    foreach ($products as $product) {
        $imageFile = findProductImage($product['name'], $product['category']);
        
        // Update the product with the new image
        $updateStmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        $success = $updateStmt->execute([$imageFile, $product['id']]);
        
        $status = $success ? "✅ Updated" : "❌ Failed";
        if ($success) $updatedProducts++;
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>" . htmlspecialchars($product['category'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($imageFile) . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p><strong>Updated $updatedProducts out of " . count($products) . " products</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error updating products: " . $e->getMessage() . "</p>";
}

// Update farmer images in database
try {
    $stmt = $pdo->prepare("SELECT id, farm_name FROM farmers");
    $stmt->execute();
    $farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updatedFarmers = 0;
    
    echo "<h2>Updating Farmer Profile Images</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Farm Name</th><th>Assigned Image</th><th>Status</th></tr>";
    
    foreach ($farmers as $farmer) {
        $imageFile = findFarmerImage($farmer['farm_name']);
        
        // Update the farmer with the new image
        $updateStmt = $pdo->prepare("UPDATE farmers SET profile_image = ? WHERE id = ?");
        $success = $updateStmt->execute([$imageFile, $farmer['id']]);
        
        $status = $success ? "✅ Updated" : "❌ Failed";
        if ($success) $updatedFarmers++;
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($farmer['farm_name']) . "</td>";
        echo "<td>" . htmlspecialchars($imageFile) . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p><strong>Updated $updatedFarmers out of " . count($farmers) . " farmers</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error updating farmers: " . $e->getMessage() . "</p>";
}

echo "<h2>Image Assignment Complete!</h2>";
echo "<p>All products and farmers now have appropriate image assignments. The system will fall back to default images if specific files are not found.</p>";

?>
