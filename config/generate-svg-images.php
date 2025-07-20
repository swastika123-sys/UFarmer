<?php
/**
 * UFarmer SVG Image Generator
 * Creates high-quality SVG placeholder images for different categories
 */

class SVGImageGenerator {
    
    private $baseDir;
    
    public function __construct($baseDir = '../assets/images/') {
        $this->baseDir = $baseDir;
    }
    
    /**
     * Generate all category placeholder images
     */
    public function generateAllImages() {
        $images = [
            // Vegetables
            'vegetables/tomatoes-fresh.svg' => $this->generateTomatoSVG(),
            'vegetables/leafy-greens.svg' => $this->generateLeafyGreensSVG(),
            'vegetables/carrots-organic.svg' => $this->generateCarrotsSVG(),
            'vegetables/spinach-fresh.svg' => $this->generateSpinachSVG(),
            'vegetables/potatoes-russet.svg' => $this->generatePotatoesSVG(),
            'vegetables/corn-sweet.svg' => $this->generateCornSVG(),
            'vegetables/bell-peppers.svg' => $this->generatePeppersSVG(),
            
            // Fruits
            'fruits/strawberries-fresh.svg' => $this->generateStrawberriesSVG(),
            'fruits/apples-variety.svg' => $this->generateApplesSVG(),
            'fruits/berries-mixed.svg' => $this->generateBerriesSVG(),
            'fruits/peaches-fresh.svg' => $this->generatePeachesSVG(),
            'fruits/pears-golden.svg' => $this->generatePearsSVG(),
            
            // Herbs
            'herbs/basil-fresh.svg' => $this->generateBasilSVG(),
            'herbs/herbs-mixed.svg' => $this->generateMixedHerbsSVG(),
            
            // Dairy
            'dairy/farm-eggs.svg' => $this->generateEggsSVG(),
            'dairy/fresh-milk.svg' => $this->generateMilkSVG(),
            'dairy/farm-cheese.svg' => $this->generateCheeseSVG(),
            'dairy/farm-butter.svg' => $this->generateButterSVG(),
            
            // Farmers
            'farmers/farmer-organic-male.svg' => $this->generateFarmerMaleSVG(),
            'farmers/farmer-female-greenhouse.svg' => $this->generateFarmerFemaleSVG(),
            'farmers/farmer-elderly-wise.svg' => $this->generateFarmerElderlySVG(),
            'farmers/farmer-young-energetic.svg' => $this->generateFarmerYoungSVG(),
            
            // Category defaults
            'vegetables/vegetables-mixed.svg' => $this->generateMixedVegetablesSVG(),
            'fruits/fruits-basket.svg' => $this->generateFruitBasketSVG(),
            'herbs/herbs-garden.svg' => $this->generateHerbGardenSVG(),
            'dairy/dairy-products.svg' => $this->generateDairyProductsSVG(),
        ];
        
        $created = 0;
        foreach ($images as $filename => $svg) {
            $filepath = $this->baseDir . $filename;
            $dir = dirname($filepath);
            
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
            if (file_put_contents($filepath, $svg)) {
                echo "✅ Created: $filename<br>";
                $created++;
            } else {
                echo "❌ Failed: $filename<br>";
            }
        }
        
        return $created;
    }
    
    /**
     * Generate tomato SVG
     */
    private function generateTomatoSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="tomatoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#FF6B6B"/>
            <stop offset="100%" style="stop-color:#E53E3E"/>
        </linearGradient>
        <linearGradient id="leafGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#68D391"/>
            <stop offset="100%" style="stop-color:#38A169"/>
        </linearGradient>
    </defs>
    <rect width="400" height="300" fill="#F7FAFC"/>
    
    <!-- Main tomato body -->
    <circle cx="200" cy="180" r="80" fill="url(#tomatoGradient)"/>
    
    <!-- Tomato indent lines -->
    <path d="M140 160 Q200 140 260 160" stroke="#E53E3E" stroke-width="2" fill="none"/>
    <path d="M160 140 Q200 120 240 140" stroke="#E53E3E" stroke-width="2" fill="none"/>
    
    <!-- Stem and leaves -->
    <rect x="195" y="80" width="10" height="20" fill="#8B4513" rx="2"/>
    <path d="M180 90 Q185 75 190 90" fill="url(#leafGradient)"/>
    <path d="M210 90 Q215 75 220 90" fill="url(#leafGradient)"/>
    <path d="M190 85 Q200 70 210 85" fill="url(#leafGradient)"/>
    
    <!-- Highlight -->
    <ellipse cx="170" cy="150" rx="15" ry="20" fill="#FFB3B3" opacity="0.6"/>
    
    <!-- Label -->
    <text x="200" y="280" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Fresh Tomatoes</text>
</svg>';
    }
    
    /**
     * Generate leafy greens SVG
     */
    private function generateLeafyGreensSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="leafGreen1" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#68D391"/>
            <stop offset="100%" style="stop-color:#38A169"/>
        </linearGradient>
        <linearGradient id="leafGreen2" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#9AE6B4"/>
            <stop offset="100%" style="stop-color:#68D391"/>
        </linearGradient>
    </defs>
    <rect width="400" height="300" fill="#F0FFF4"/>
    
    <!-- Back leaves -->
    <path d="M120 180 Q150 120 180 180 Q150 220 120 180" fill="url(#leafGreen2)"/>
    <path d="M220 180 Q250 120 280 180 Q250 220 220 180" fill="url(#leafGreen2)"/>
    
    <!-- Front leaves -->
    <path d="M160 200 Q200 140 240 200 Q200 240 160 200" fill="url(#leafGreen1)"/>
    <path d="M140 160 Q180 100 220 160 Q180 200 140 160" fill="url(#leafGreen1)"/>
    
    <!-- Leaf veins -->
    <path d="M160 200 Q200 170 240 200" stroke="#38A169" stroke-width="2" fill="none"/>
    <path d="M140 160 Q180 130 220 160" stroke="#38A169" stroke-width="2" fill="none"/>
    
    <!-- Label -->
    <text x="200" y="280" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Fresh Salad Greens</text>
</svg>';
    }
    
    /**
     * Generate farmer male SVG
     */
    private function generateFarmerMaleSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="skinTone" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#F7C6A0"/>
            <stop offset="100%" style="stop-color:#E8B08A"/>
        </linearGradient>
        <linearGradient id="shirtColor" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#4299E1"/>
            <stop offset="100%" style="stop-color:#2B6CB0"/>
        </linearGradient>
    </defs>
    <rect width="400" height="300" fill="#E6FFFA"/>
    
    <!-- Body -->
    <rect x="160" y="160" width="80" height="100" fill="url(#shirtColor)" rx="10"/>
    
    <!-- Head -->
    <circle cx="200" cy="120" r="35" fill="url(#skinTone)"/>
    
    <!-- Hat -->
    <path d="M165 100 Q200 85 235 100 Q235 95 165 95" fill="#8B4513"/>
    <ellipse cx="200" cy="95" rx="40" ry="8" fill="#A0522D"/>
    
    <!-- Eyes -->
    <circle cx="185" cy="115" r="3" fill="#2D3748"/>
    <circle cx="215" cy="115" r="3" fill="#2D3748"/>
    
    <!-- Mouth -->
    <path d="M190 130 Q200 135 210 130" stroke="#2D3748" stroke-width="2" fill="none"/>
    
    <!-- Arms -->
    <rect x="130" y="170" width="25" height="60" fill="url(#shirtColor)" rx="12"/>
    <rect x="245" y="170" width="25" height="60" fill="url(#shirtColor)" rx="12"/>
    
    <!-- Hands -->
    <circle cx="142" cy="240" r="12" fill="url(#skinTone)"/>
    <circle cx="258" cy="240" r="12" fill="url(#skinTone)"/>
    
    <!-- Farm tool (hoe) -->
    <rect x="250" y="180" width="4" height="40" fill="#8B4513"/>
    <rect x="245" y="175" width="14" height="6" fill="#696969"/>
    
    <!-- Label -->
    <text x="200" y="290" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#2D3748" font-weight="bold">Local Farmer</text>
</svg>';
    }
    
    /**
     * Generate mixed vegetables SVG
     */
    private function generateMixedVegetablesSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="carrotGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#FF8C42"/>
            <stop offset="100%" style="stop-color:#FF6B35"/>
        </linearGradient>
        <linearGradient id="tomatoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#FF6B6B"/>
            <stop offset="100%" style="stop-color:#E53E3E"/>
        </linearGradient>
        <linearGradient id="leafGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#68D391"/>
            <stop offset="100%" style="stop-color:#38A169"/>
        </linearGradient>
    </defs>
    <rect width="400" height="300" fill="#F7FAFC"/>
    
    <!-- Carrot -->
    <path d="M100 200 L110 120 L120 200 Z" fill="url(#carrotGrad)"/>
    <path d="M105 120 Q108 110 115 115" stroke="url(#leafGrad)" stroke-width="3" fill="none"/>
    
    <!-- Tomato -->
    <circle cx="200" cy="160" r="40" fill="url(#tomatoGrad)"/>
    <path d="M180 130 Q200 120 220 130" fill="url(#leafGrad)"/>
    
    <!-- Bell Pepper -->
    <path d="M280 140 Q300 120 320 140 L320 200 Q300 220 280 200 Z" fill="#68D391"/>
    <rect x="295" y="130" width="10" height="15" fill="#8B4513"/>
    
    <!-- Leafy greens -->
    <path d="M120 240 Q140 220 160 240 Q140 260 120 240" fill="url(#leafGrad)"/>
    <path d="M240 240 Q260 220 280 240 Q260 260 240 240" fill="url(#leafGrad)"/>
    
    <!-- Background pattern -->
    <pattern id="vegPattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
        <circle cx="20" cy="20" r="2" fill="#E2E8F0" opacity="0.3"/>
    </pattern>
    <rect width="400" height="300" fill="url(#vegPattern)"/>
    
    <!-- Label -->
    <text x="200" y="285" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Fresh Vegetables</text>
</svg>';
    }
    
    /**
     * Generate more SVG images... (truncated for brevity)
     */
    private function generateApplesSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <radialGradient id="redApple" cx="30%" cy="30%">
            <stop offset="0%" style="stop-color:#FF8A80"/>
            <stop offset="100%" style="stop-color:#F44336"/>
        </radialGradient>
        <radialGradient id="greenApple" cx="30%" cy="30%">
            <stop offset="0%" style="stop-color:#AED581"/>
            <stop offset="100%" style="stop-color:#689F38"/>
        </radialGradient>
    </defs>
    <rect width="400" height="300" fill="#FFF8E1"/>
    
    <!-- Red apple -->
    <circle cx="160" cy="150" r="50" fill="url(#redApple)"/>
    <path d="M155 110 Q160 100 165 110" fill="#4CAF50"/>
    <rect x="158" y="100" width="4" height="15" fill="#8B4513"/>
    
    <!-- Green apple -->
    <circle cx="240" cy="150" r="50" fill="url(#greenApple)"/>
    <path d="M235 110 Q240 100 245 110" fill="#4CAF50"/>
    <rect x="238" y="100" width="4" height="15" fill="#8B4513"/>
    
    <!-- Highlights -->
    <ellipse cx="140" cy="130" rx="8" ry="12" fill="#FFCDD2" opacity="0.8"/>
    <ellipse cx="220" cy="130" rx="8" ry="12" fill="#DCEDC8" opacity="0.8"/>
    
    <text x="200" y="280" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Fresh Apples</text>
</svg>';
    }
    
    private function generateStrawberriesSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <radialGradient id="strawberry" cx="30%" cy="20%">
            <stop offset="0%" style="stop-color:#FF8A80"/>
            <stop offset="100%" style="stop-color:#E91E63"/>
        </radialGradient>
    </defs>
    <rect width="400" height="300" fill="#FCE4EC"/>
    
    <!-- Strawberries -->
    <path d="M150 120 Q170 100 190 120 L185 180 Q170 190 155 180 Z" fill="url(#strawberry)"/>
    <path d="M210 120 Q230 100 250 120 L245 180 Q230 190 215 180 Z" fill="url(#strawberry)"/>
    
    <!-- Strawberry tops -->
    <path d="M150 120 Q160 110 170 115 Q180 110 190 120" fill="#4CAF50"/>
    <path d="M210 120 Q220 110 230 115 Q240 110 250 120" fill="#4CAF50"/>
    
    <!-- Seeds -->
    <circle cx="160" cy="140" r="2" fill="#FFF"/>
    <circle cx="175" cy="150" r="2" fill="#FFF"/>
    <circle cx="165" cy="160" r="2" fill="#FFF"/>
    <circle cx="220" cy="140" r="2" fill="#FFF"/>
    <circle cx="235" cy="150" r="2" fill="#FFF"/>
    <circle cx="225" cy="160" r="2" fill="#FFF"/>
    
    <text x="200" y="280" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Fresh Strawberries</text>
</svg>';
    }
    
    private function generateBasilSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="basilGreen" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#81C784"/>
            <stop offset="100%" style="stop-color:#4CAF50"/>
        </linearGradient>
    </defs>
    <rect width="400" height="300" fill="#F1F8E9"/>
    
    <!-- Basil stems -->
    <rect x="195" y="180" width="4" height="60" fill="#8BC34A"/>
    <rect x="205" y="180" width="4" height="60" fill="#8BC34A"/>
    
    <!-- Basil leaves -->
    <ellipse cx="180" cy="160" rx="15" ry="25" fill="url(#basilGreen)" transform="rotate(-15 180 160)"/>
    <ellipse cx="220" cy="160" rx="15" ry="25" fill="url(#basilGreen)" transform="rotate(15 220 160)"/>
    <ellipse cx="170" cy="140" rx="12" ry="20" fill="url(#basilGreen)" transform="rotate(-25 170 140)"/>
    <ellipse cx="230" cy="140" rx="12" ry="20" fill="url(#basilGreen)" transform="rotate(25 230 140)"/>
    <ellipse cx="185" cy="120" rx="18" ry="28" fill="url(#basilGreen)"/>
    <ellipse cx="215" cy="120" rx="18" ry="28" fill="url(#basilGreen)"/>
    
    <!-- Leaf veins -->
    <path d="M185 105 L185 135" stroke="#388E3C" stroke-width="1"/>
    <path d="M215 105 L215 135" stroke="#388E3C" stroke-width="1"/>
    
    <text x="200" y="280" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Fresh Basil</text>
</svg>';
    }
    
    private function generateEggsSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <radialGradient id="eggWhite" cx="30%" cy="20%">
            <stop offset="0%" style="stop-color:#FFFDE7"/>
            <stop offset="100%" style="stop-color:#F5F5DC"/>
        </radialGradient>
        <radialGradient id="eggBrown" cx="30%" cy="20%">
            <stop offset="0%" style="stop-color:#D7CCC8"/>
            <stop offset="100%" style="stop-color:#8D6E63"/>
        </radialGradient>
    </defs>
    <rect width="400" height="300" fill="#FFF8E1"/>
    
    <!-- Eggs -->
    <ellipse cx="150" cy="150" rx="25" ry="35" fill="url(#eggWhite)"/>
    <ellipse cx="200" cy="150" rx="25" ry="35" fill="url(#eggBrown)"/>
    <ellipse cx="250" cy="150" rx="25" ry="35" fill="url(#eggWhite)"/>
    <ellipse cx="175" cy="180" rx="25" ry="35" fill="url(#eggBrown)"/>
    <ellipse cx="225" cy="180" rx="25" ry="35" fill="url(#eggWhite)"/>
    
    <text x="200" y="280" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Farm Fresh Eggs</text>
</svg>';
    }
    
    private function generateMilkSVG() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="milkBottle" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#FFFFFF"/>
            <stop offset="100%" style="stop-color:#F5F5F5"/>
        </linearGradient>
        <linearGradient id="milkContent" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#FFFDE7"/>
            <stop offset="100%" style="stop-color:#FFF8E1"/>
        </linearGradient>
    </defs>
    <rect width="400" height="300" fill="#E3F2FD"/>
    
    <!-- Milk bottle -->
    <rect x="160" y="120" width="80" height="140" fill="url(#milkBottle)" rx="10"/>
    <rect x="180" y="100" width="40" height="30" fill="url(#milkBottle)" rx="5"/>
    
    <!-- Milk content -->
    <rect x="165" y="140" width="70" height="115" fill="url(#milkContent)" rx="8"/>
    
    <!-- Bottle cap -->
    <rect x="175" y="95" width="50" height="15" fill="#FF5722" rx="7"/>
    
    <!-- Label -->
    <rect x="170" y="160" width="60" height="40" fill="#FFF" rx="3"/>
    <text x="200" y="175" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" fill="#2D3748">FARM</text>
    <text x="200" y="190" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" fill="#2D3748">FRESH</text>
    
    <text x="200" y="290" text-anchor="middle" font-family="Arial, sans-serif" font-size="16" fill="#2D3748" font-weight="bold">Fresh Milk</text>
</svg>';
    }
    
    // Additional methods for other images...
    private function generateCheeseSVG() { return $this->generateGenericSVG("🧀", "#FFD54F", "Farm Cheese"); }
    private function generateButterSVG() { return $this->generateGenericSVG("🧈", "#FFF176", "Farm Butter"); }
    private function generateCarrotsSVG() { return $this->generateGenericSVG("🥕", "#FF8A65", "Organic Carrots"); }
    private function generateSpinachSVG() { return $this->generateGenericSVG("🥬", "#66BB6A", "Fresh Spinach"); }
    private function generatePotatoesSVG() { return $this->generateGenericSVG("🥔", "#8D6E63", "Farm Potatoes"); }
    private function generateCornSVG() { return $this->generateGenericSVG("🌽", "#FFD54F", "Sweet Corn"); }
    private function generatePeppersSVG() { return $this->generateGenericSVG("🫑", "#66BB6A", "Bell Peppers"); }
    private function generateBerriesSVG() { return $this->generateGenericSVG("🫐", "#7E57C2", "Mixed Berries"); }
    private function generatePeachesSVG() { return $this->generateGenericSVG("🍑", "#FFB74D", "Fresh Peaches"); }
    private function generatePearsSVG() { return $this->generateGenericSVG("🍐", "#9CCC65", "Golden Pears"); }
    private function generateMixedHerbsSVG() { return $this->generateGenericSVG("🌿", "#81C784", "Mixed Herbs"); }
    private function generateFarmerFemaleSVG() { return $this->generateGenericSVG("👩‍🌾", "#4CAF50", "Local Farmer"); }
    private function generateFarmerElderlySVG() { return $this->generateGenericSVG("🧑‍🌾", "#6D4C41", "Experienced Farmer"); }
    private function generateFarmerYoungSVG() { return $this->generateGenericSVG("👨‍🌾", "#4CAF50", "Young Farmer"); }
    private function generateFruitBasketSVG() { return $this->generateGenericSVG("🧺", "#8D6E63", "Fresh Fruits"); }
    private function generateHerbGardenSVG() { return $this->generateGenericSVG("🌱", "#66BB6A", "Garden Herbs"); }
    private function generateDairyProductsSVG() { return $this->generateGenericSVG("🥛", "#FFF176", "Dairy Products"); }
    
    /**
     * Generate a generic emoji-based SVG
     */
    private function generateGenericSVG($emoji, $color, $label) {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<svg width=\"400\" height=\"300\" viewBox=\"0 0 400 300\" xmlns=\"http://www.w3.org/2000/svg\">
    <defs>
        <linearGradient id=\"bgGradient\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\">
            <stop offset=\"0%\" style=\"stop-color:{$color}30\"/>
            <stop offset=\"100%\" style=\"stop-color:{$color}60\"/>
        </linearGradient>
    </defs>
    <rect width=\"400\" height=\"300\" fill=\"url(#bgGradient)\"/>
    <circle cx=\"200\" cy=\"150\" r=\"60\" fill=\"{$color}\" opacity=\"0.2\"/>
    <text x=\"200\" y=\"165\" text-anchor=\"middle\" font-family=\"Arial, sans-serif\" font-size=\"60\">{$emoji}</text>
    <text x=\"200\" y=\"280\" text-anchor=\"middle\" font-family=\"Arial, sans-serif\" font-size=\"16\" fill=\"#2D3748\" font-weight=\"bold\">{$label}</text>
</svg>";
    }
}

// Run the generator
echo "<h1>UFarmer SVG Image Generator</h1>";

$generator = new SVGImageGenerator();
$created = $generator->generateAllImages();

echo "<h2>Image Generation Complete!</h2>";
echo "<p>Created <strong>$created</strong> high-quality SVG placeholder images.</p>";
echo "<p>These images provide beautiful, scalable fallbacks for all product categories and farmer profiles.</p>";

echo "<h3>Benefits of SVG Images:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Scalable:</strong> Perfect quality at any size</li>";
echo "<li>✅ <strong>Small file size:</strong> Fast loading</li>";
echo "<li>✅ <strong>Reliable:</strong> No external dependencies</li>";
echo "<li>✅ <strong>Accessible:</strong> Works on all devices</li>";
echo "<li>✅ <strong>Professional:</strong> Consistent brand appearance</li>";
echo "</ul>";

?>
