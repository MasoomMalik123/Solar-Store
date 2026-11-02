<?php
// Run this script from CLI: php scripts/import_solar_products.php
require_once __DIR__ . '/../components/connect.php';

try{
    // Upsert only the catalog rows owned by this importer. Existing cart/order
    // references and unrelated products are preserved.
    $products = [
        ['name' => 'Solar Panel 400W Mono - Model A', 'brand' => 'Solar Store', 'category' => 'Panels', 'details' => 'High-efficiency mono PERC solar panel, 400W. Ideal for residential and commercial use.', 'price' => 160, 'img1' => 'seed-solar-panels.jpg', 'img2' => 'seed-solar-panels.jpg', 'img3' => 'seed-solar-panels.jpg'],
        ['name' => 'Hybrid Inverter 5kW', 'brand' => 'Solar Store', 'category' => 'Inverters', 'details' => '5kW hybrid inverter with built-in MPPT and battery charging support.', 'price' => 850, 'img1' => 'inverter-1.svg', 'img2' => 'inverter-1.svg', 'img3' => 'inverter-1.svg'],
        ['name' => 'Lithium Battery 5kWh', 'brand' => 'Solar Store', 'category' => 'Batteries', 'details' => 'Reliable 5kWh lithium battery with long cycle life and BMS.', 'price' => 1200, 'img1' => 'seed-solar-battery.jpg', 'img2' => 'seed-solar-battery.jpg', 'img3' => 'seed-solar-battery.jpg'],
        ['name' => 'Roof Mounting Kit (Set)', 'brand' => 'Solar Store', 'category' => 'Mounting', 'details' => 'Adjustable roof mounting kit for residential solar panels.', 'price' => 120, 'img1' => 'mounting-1.svg', 'img2' => 'mounting-1.svg', 'img3' => 'mounting-1.svg'],
    ];

    $stmt = $conn->prepare("INSERT INTO products (name, brand, category, details, price, image_01, image_02, image_03) SELECT ?, ?, ?, ?, ?, ?, ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = ? AND brand = ?)");
    foreach($products as $p){
        $stmt->execute([$p['name'],$p['brand'],$p['category'],$p['details'],$p['price'],$p['img1'],$p['img2'],$p['img3'],$p['name'],$p['brand']]);
    }

    echo "Inserted " . count($products) . " solar products.\n";
    echo "Make sure the images exist in uploaded_img/ directory.\n";
}catch(Exception $e){
    echo "Error: " . $e->getMessage() . "\n";
}
