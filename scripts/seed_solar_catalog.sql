INSERT INTO `products` (`name`, `brand`, `category`, `details`, `price`, `image_01`, `image_02`, `image_03`)
SELECT 'Solar Panel 400W Mono - Model A', 'Solar Store', 'Panels', 'High-efficiency mono PERC solar panel, 400W. Ideal for residential and commercial use.', 160, 'seed-solar-panels.jpg', 'seed-solar-panels.jpg', 'seed-solar-panels.jpg'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `brand` = 'Solar Store' AND `name` = 'Solar Panel 400W Mono - Model A');

INSERT INTO `products` (`name`, `brand`, `category`, `details`, `price`, `image_01`, `image_02`, `image_03`)
SELECT 'Hybrid Inverter 5kW', 'Solar Store', 'Inverters', '5kW hybrid inverter with built-in MPPT and battery charging support.', 850, 'inverter-1.svg', 'inverter-1.svg', 'inverter-1.svg'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `brand` = 'Solar Store' AND `name` = 'Hybrid Inverter 5kW');

INSERT INTO `products` (`name`, `brand`, `category`, `details`, `price`, `image_01`, `image_02`, `image_03`)
SELECT 'Lithium Battery 5kWh', 'Solar Store', 'Batteries', 'Reliable 5kWh lithium battery with long cycle life and BMS.', 1200, 'seed-solar-battery.jpg', 'seed-solar-battery.jpg', 'seed-solar-battery.jpg'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `brand` = 'Solar Store' AND `name` = 'Lithium Battery 5kWh');

INSERT INTO `products` (`name`, `brand`, `category`, `details`, `price`, `image_01`, `image_02`, `image_03`)
SELECT 'Roof Mounting Kit (Set)', 'Solar Store', 'Mounting', 'Adjustable roof mounting kit for residential solar panels.', 120, 'mounting-1.svg', 'mounting-1.svg', 'mounting-1.svg'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `brand` = 'Solar Store' AND `name` = 'Roof Mounting Kit (Set)');