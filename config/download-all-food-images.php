<?php
// UFarmer: Download images for all foods in the demo database using Unsplash search
$foods = [
    'Organic Tomatoes',
    'Fresh Basil',
    'Baby Spinach',
    'Organic Carrots',
    'Fresh Strawberries',
    'Mixed Greens',
    'Cherry Tomatoes',
    'Bell Peppers',
    'Bok Choy',
    'Daikon Radish',
    'Napa Cabbage',
    'Shiitake Mushrooms',
    'Fresh Whole Milk',
    'Artisan Cheese',
    'Greek Yogurt',
    'Farm Butter',
    'Honeycrisp Apples',
    'Fresh Peaches',
    'Pears',
    'Plums',
];

$baseDir = __DIR__ . '/../assets/uploads/foods';
if (!is_dir($baseDir)) mkdir($baseDir, 0755, true);

function unsplash_search_url($query) {
    $q = urlencode($query);
    return "https://source.unsplash.com/800x600/?$q,food,organic";
}

foreach ($foods as $food) {
    $filename = strtolower(str_replace([' ',"'"], ['-',''], $food)) . '.jpg';
    $dest = "$baseDir/$filename";
    if (!file_exists($dest)) {
        $url = unsplash_search_url($food);
        $imgData = @file_get_contents($url);
        if ($imgData) {
            file_put_contents($dest, $imgData);
            echo "Downloaded: $filename\n";
        } else {
            echo "Failed to download: $filename from $url\n";
        }
    } else {
        echo "Already exists: $dest\n";
    }
}
echo "Done.\n";
