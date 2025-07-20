<?php
/**
 * UFarmer Image Setup and Management
 * Downloads and sets up royalty-free images for the platform
 */

require_once '../includes/functions.php';

echo "<h1>UFarmer Image Enhancement Setup</h1>";

// Create directory structure
$imageDirectories = [
    '../assets/images/farmers',
    '../assets/images/products',
    '../assets/images/categories',
    '../assets/images/backgrounds',
    '../assets/images/optimized'
];

foreach ($imageDirectories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir<br>";
    }
}

// High-quality royalty-free image URLs (using Unsplash API with specific farming/agriculture themes)
$imageCollections = [
    'farmers' => [
        [
            'name' => 'farmer-portrait-1.jpg',
            'url' => 'https://images.unsplash.com/photo-1500651230702-0e2d8219d0d2?w=500&h=500&fit=crop&crop=face',
            'description' => 'Smiling male farmer in field'
        ],
        [
            'name' => 'farmer-portrait-2.jpg', 
            'url' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=500&h=500&fit=crop&crop=face',
            'description' => 'Female farmer with vegetables'
        ],
        [
            'name' => 'farmer-portrait-3.jpg',
            'url' => 'https://images.unsplash.com/photo-1566004100631-35d015d6a491?w=500&h=500&fit=crop&crop=face', 
            'description' => 'Older farmer with experience'
        ],
        [
            'name' => 'farmer-portrait-4.jpg',
            'url' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=500&h=500&fit=crop&crop=face',
            'description' => 'Young farmer with organic produce'
        ],
        [
            'name' => 'farmer-portrait-5.jpg',
            'url' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=500&h=500&fit=crop&crop=face',
            'description' => 'Female organic farmer'
        ]
    ],
    
    'products' => [
        // Vegetables
        [
            'name' => 'tomatoes-fresh.jpg',
            'url' => 'https://images.unsplash.com/photo-1546470427-ac4e015d2fd0?w=400&h=300&fit=crop',
            'description' => 'Fresh red tomatoes on vine',
            'category' => 'vegetables'
        ],
        [
            'name' => 'leafy-greens.jpg', 
            'url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400&h=300&fit=crop',
            'description' => 'Mixed salad greens',
            'category' => 'vegetables'
        ],
        [
            'name' => 'carrots-organic.jpg',
            'url' => 'https://images.unsplash.com/photo-1445282768818-728615cc910a?w=400&h=300&fit=crop',
            'description' => 'Fresh organic carrots with tops',
            'category' => 'vegetables'
        ],
        [
            'name' => 'spinach-fresh.jpg',
            'url' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=300&fit=crop',
            'description' => 'Fresh spinach leaves',
            'category' => 'vegetables'
        ],
        [
            'name' => 'potatoes-russet.jpg',
            'url' => 'https://images.unsplash.com/photo-1566806829-02cc9b5a8a6b?w=400&h=300&fit=crop',
            'description' => 'Fresh potatoes from farm',
            'category' => 'vegetables'
        ],
        [
            'name' => 'corn-sweet.jpg',
            'url' => 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=400&h=300&fit=crop',
            'description' => 'Sweet corn on the cob',
            'category' => 'vegetables'
        ],
        [
            'name' => 'bell-peppers.jpg',
            'url' => 'https://images.unsplash.com/photo-1525607551316-4a8e16d1f9ba?w=400&h=300&fit=crop',
            'description' => 'Colorful bell peppers',
            'category' => 'vegetables'
        ],
        
        // Fruits
        [
            'name' => 'strawberries-fresh.jpg',
            'url' => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=400&h=300&fit=crop',
            'description' => 'Fresh ripe strawberries',
            'category' => 'fruits'
        ],
        [
            'name' => 'apples-variety.jpg',
            'url' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&h=300&fit=crop',
            'description' => 'Mixed variety apples',
            'category' => 'fruits'
        ],
        [
            'name' => 'berries-mixed.jpg',
            'url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
            'description' => 'Mixed berries assortment',
            'category' => 'fruits'
        ],
        [
            'name' => 'peaches-fresh.jpg',
            'url' => 'https://images.unsplash.com/photo-1563114773-84221bd62daa?w=400&h=300&fit=crop',
            'description' => 'Fresh ripe peaches',
            'category' => 'fruits'
        ],
        [
            'name' => 'pears-golden.jpg',
            'url' => 'https://images.unsplash.com/photo-1571575173700-afb9492e6a50?w=400&h=300&fit=crop',
            'description' => 'Golden pears',
            'category' => 'fruits'
        ],
        
        // Herbs
        [
            'name' => 'basil-fresh.jpg',
            'url' => 'https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=400&h=300&fit=crop',
            'description' => 'Fresh basil herbs',
            'category' => 'herbs'
        ],
        [
            'name' => 'herbs-mixed.jpg',
            'url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
            'description' => 'Mixed fresh herbs',
            'category' => 'herbs'
        ],
        
        // Dairy & Farm Products
        [
            'name' => 'farm-eggs.jpg',
            'url' => 'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=400&h=300&fit=crop',
            'description' => 'Fresh farm eggs',
            'category' => 'dairy'
        ],
        [
            'name' => 'fresh-milk.jpg',
            'url' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&h=300&fit=crop',
            'description' => 'Fresh farm milk',
            'category' => 'dairy'
        ],
        [
            'name' => 'farm-cheese.jpg',
            'url' => 'https://images.unsplash.com/photo-1552767059-ce182ead6c1b?w=400&h=300&fit=crop',
            'description' => 'Artisan farm cheese',
            'category' => 'dairy'
        ]
    ],
    
    'backgrounds' => [
        [
            'name' => 'farm-landscape.jpg',
            'url' => 'https://images.unsplash.com/photo-1500651230702-0e2d8219d0d2?w=1200&h=600&fit=crop',
            'description' => 'Beautiful farm landscape'
        ],
        [
            'name' => 'greenhouse-interior.jpg',
            'url' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1200&h=600&fit=crop',
            'description' => 'Greenhouse with growing plants'
        ],
        [
            'name' => 'farmers-market.jpg',
            'url' => 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1200&h=600&fit=crop',
            'description' => 'Bustling farmers market scene'
        ]
    ]
];

// Function to download and save images
function downloadImage($url, $savePath, $description) {
    echo "Downloading: $description to $savePath<br>";
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'UFarmer Image Setup/1.0');
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $imageData !== false) {
        file_put_contents($savePath, $imageData);
        echo "✅ Successfully downloaded: $description<br>";
        return true;
    } else {
        echo "❌ Failed to download: $description (HTTP $httpCode)<br>";
        return false;
    }
}

// Download all images
foreach ($imageCollections as $category => $images) {
    echo "<h2>Downloading $category images...</h2>";
    
    foreach ($images as $image) {
        $savePath = "../assets/images/$category/" . $image['name'];
        downloadImage($image['url'], $savePath, $image['description']);
        
        // Add a small delay to be respectful to the API
        usleep(200000); // 200ms delay
    }
}

// Create improved default images
echo "<h2>Creating improved default images...</h2>";

// Copy best farmer image as new default
$bestFarmerImage = '../assets/images/farmers/farmer-portrait-1.jpg';
if (file_exists($bestFarmerImage)) {
    copy($bestFarmerImage, '../assets/images/default-farmer.jpg');
    echo "✅ Updated default farmer image<br>";
}

// Copy best product image as new default
$bestProductImage = '../assets/images/products/tomatoes-fresh.jpg';
if (file_exists($bestProductImage)) {
    copy($bestProductImage, '../assets/images/default-product.jpg');
    echo "✅ Updated default product image<br>";
}

echo "<h2>Image Enhancement Setup Complete!</h2>";
echo "<p>All royalty-free images have been downloaded and organized. The platform now has professional agricultural imagery.</p>";

// Generate image mapping for products
echo "<h3>Image Assignment Recommendations:</h3>";
echo "<ul>";
echo "<li><strong>Tomatoes:</strong> tomatoes-fresh.jpg</li>";
echo "<li><strong>Salad Greens:</strong> leafy-greens.jpg</li>";
echo "<li><strong>Carrots:</strong> carrots-organic.jpg</li>";
echo "<li><strong>Spinach:</strong> spinach-fresh.jpg</li>";
echo "<li><strong>Potatoes:</strong> potatoes-russet.jpg</li>";
echo "<li><strong>Corn:</strong> corn-sweet.jpg</li>";
echo "<li><strong>Strawberries:</strong> strawberries-fresh.jpg</li>";
echo "<li><strong>Apples:</strong> apples-variety.jpg</li>";
echo "<li><strong>Mixed Berries:</strong> berries-mixed.jpg</li>";
echo "<li><strong>Basil:</strong> basil-fresh.jpg</li>";
echo "<li><strong>Farm Eggs:</strong> farm-eggs.jpg</li>";
echo "<li><strong>Fresh Milk:</strong> fresh-milk.jpg</li>";
echo "</ul>";

?>
