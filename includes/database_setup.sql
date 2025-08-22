-- Create database
CREATE DATABASE biofuel;
USE biofuel;
-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    ingredients TEXT NOT NULL,
    benefits TEXT NOT NULL,
    caffeine_source VARCHAR(100) NOT NULL,
    caffeine_mg INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- User carts table 
CREATE TABLE user_carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY (user_id, product_id) -- Ensures one row per user-product combination
);

-- User addresses table
CREATE TABLE user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(50) NOT NULL,
    zip VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    is_default BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- User payment methods table
CREATE TABLE user_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    payment_type ENUM('visa', 'mastercard', 'paypal') NOT NULL,
    paypal_email VARCHAR(100),
    paypal_username VARCHAR(100),
    cc_name VARCHAR(100),
    cc_number VARCHAR(50),
    cc_expiration VARCHAR(10),
    cc_cvv VARCHAR(4),
    is_default BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
-- Insert sample products
INSERT INTO products (name, description, price, image_path, ingredients, benefits, caffeine_source, caffeine_mg) VALUES
-- Insert single products
('Solar Citrus', 'A zesty mix of lemon, lime, and a hint of orange for an electrifying boost.', 19.99, 'assets\\images\\lime drink.jpg', 'Carbonated water,
Natural lemon, lime, and orange juices (from concentrate), Green tea extract, Organic cane sugar, Citric acid, Vitamin B6, B12, and C, Natural flavors,
Ginseng extract, Guarana seed extract, Electrolytes (Potassium, Magnesium)', 'Green tea extract provides a clean, steady caffeine boost along
with antioxidant support, while guarana seed extract enhances alertness and delivers longer-lasting energy. Vitamin C contributes to immune
system support, and sea salt promotes natural hydration and electrolyte balance. Additionally, B-vitamins help convert food into energy,
supporting overall vitality throughout the day.', 'Green tea extract, Guarana seed extract', 120),


('Blue Surge', 'Refreshing blue raspberry with light cooling mint — crisp and energizing.', 19.99, 'assets\\images\\blue drink.jpg',
'Carbonated water, Blue raspberry juice (from concentrate), Organic cane sugar, Peppermint leaf extract, Green coffee bean extract,
Vitamin B-complex (B3, B6, B12), Natural flavors, Citric acid, L-theanine, Electrolytes', 'Yerba mate energizes the body without causing
jitters and is rich in antioxidants, making it a powerful natural stimulant. L-theanine provides a calming effect that helps smooth out the
caffeine spike, promoting focused energy without crashes. Natural mint adds a refreshing boost for the mind, helping to reduce fatigue and
enhance mental clarity. Combined with B-vitamins, which support sustained energy, this blend offers a balanced and effective way to stay alert
and refreshed.', 'Yerba mate extract, Natural caffeine from coffee beans', 140),


('Berry Voltage', 'A powerful blend of acai, blackberry, and strawberry. Sweet, tart, and bold.', 19.99, 'assets\\images\\pink drink.jpg',
'Carbonated water, Acai puree, Blackberry and strawberry juices (from concentrate), Organic cane sugar, Yerba mate extract, Vitamin C, B6, B12,
Natural berry flavors, Citric acid, Panax ginseng, Electrolytes', 'Green coffee bean extract provides a smooth source of caffeine and is rich in
chlorogenic acids, offering both energy and antioxidant benefits. Ginseng supports overall vitality by enhancing energy, focus, and stamina, while
elderberry delivers powerful immune support and antioxidant protection. B-vitamins round out the blend by boosting energy levels and helping to
reduce fatigue, making this combination ideal for sustained mental and physical performance.', 'Green coffee bean extract, Ginseng', 130),


('Tropic Ignite', 'Tropical fusion of mango, pineapple, and guava with a spicy ginger twist.', 19.99, 'assets\\images\\orange drink.jpg', 'Natural
 tropical fruit flavors (pineapple, mango, passionfruit), Carbonated water, Organic cane sugar, Citric acid, Green tea extract, Yerba mate extract,
  Sea salt (electrolytes), Vitamin C (ascorbic acid), B-vitamins (B3, B5, B6, B12), Stevia leaf extract, Natural color (turmeric + beta-carotene)'
, 'Yerba mate extract delivers smooth, long-lasting caffeine with less crash, providing balanced energy to keep you going strong. Green tea extract
 adds an antioxidant-rich boost, supporting both energy and overall wellness. Vitamin C helps strengthen immunity while promoting healthy skin, 
 making it a vital part of daily vitality. Sea salt replenishes essential electrolytes, keeping you hydrated and balanced during active lifestyles.
  Rounding out the formula, B-vitamins support energy metabolism, helping your body efficiently turn food into the fuel it needs.', 'Green tea 
  extract, Yerba mate extract', 110),


('Neon Apple', 'Crisp green apple flavor with a touch of kiwi — sharp and revitalizing.', 19.99, 'assets\\images\\green drink.jpg', 'Carbonated 
water, Natural apple and kiwi flavors, Organic cane sugar, Citric acid, Matcha green tea powder, Guarana seed extract, Sea salt (electrolytes), 
Vitamin C (ascorbic acid), B-vitamins (B3, B5, B6, B12), Stevia leaf extract, Natural color (chlorophyll + turmeric)', 'Matcha green tea delivers 
a steady stream of caffeine paired with L-theanine for calm, focused energy without the crash. Guarana seed extract extends this effect with a 
prolonged energy release, keeping you powered for longer. Vitamin C boosts the immune system, helping protect your health, while sea salt supports 
hydration balance by replenishing essential electrolytes. To round it out, B-vitamins aid in converting food into usable energy, ensuring your body
 runs at peak performance.', 'Matcha green tea, Guarana seed extract', 115),


('Electric Grape', 'Deep, juicy grape infused with light carbonation and vitamin boost.', 19.99, 'assets\\images\\purple drink.jpg', 'Carbonated 
water, Natural grape and acai flavors, Organic cane sugar, Citric acid, Green tea extract, Guarana seed extract, Sea salt (electrolytes), Vitamin C
 (ascorbic acid), B-vitamins (B3, B5, B6, B12), Stevia leaf extract, Natural color (anthocyanins from grape skin)', 'Green tea extract provides 
 clean, focused energy that keeps you alert without the jitters, while guarana seed extract enhances both alertness and endurance for sustained 
 performance. Acai offers powerful antioxidant support, promoting overall wellness and helping to combat oxidative stress. A touch of sea salt 
 aids in maintaining proper hydration and electrolyte balance, essential for staying at your best. Completing the blend, B-vitamins support 
 energy metabolism, ensuring your body efficiently converts nutrients into lasting fuel.', ' Green tea extract, Guarana seed extract', 125),

-- Insert 6 packs
('Solar Citrus 6 pack', 'A zesty mix of lemon, lime, and a hint of orange for an electrifying boost.', 99.99, 'assets\\images\\lime 6.jpg', 'Carbonated water,
Natural lemon, lime, and orange juices (from concentrate), Green tea extract, Organic cane sugar, Citric acid, Vitamin B6, B12, and C, Natural flavors,
Ginseng extract, Guarana seed extract, Electrolytes (Potassium, Magnesium)', 'Green tea extract provides a clean, steady caffeine boost along
with antioxidant support, while guarana seed extract enhances alertness and delivers longer-lasting energy. Vitamin C contributes to immune
system support, and sea salt promotes natural hydration and electrolyte balance. Additionally, B-vitamins help convert food into energy,
supporting overall vitality throughout the day.', 'Green tea extract, Guarana seed extract', 120),


('Blue Surge 6 pack', 'Refreshing blue raspberry with light cooling mint — crisp and energizing.', 99.99, 'assets\\images\\blue 6.jpg',
'Carbonated water, Blue raspberry juice (from concentrate), Organic cane sugar, Peppermint leaf extract, Green coffee bean extract,
Vitamin B-complex (B3, B6, B12), Natural flavors, Citric acid, L-theanine, Electrolytes', 'Yerba mate energizes the body without causing
jitters and is rich in antioxidants, making it a powerful natural stimulant. L-theanine provides a calming effect that helps smooth out the
caffeine spike, promoting focused energy without crashes. Natural mint adds a refreshing boost for the mind, helping to reduce fatigue and
enhance mental clarity. Combined with B-vitamins, which support sustained energy, this blend offers a balanced and effective way to stay alert
and refreshed.', 'Yerba mate extract, Natural caffeine from coffee beans', 140),


('Berry Voltage 6 pack', 'A powerful blend of acai, blackberry, and strawberry. Sweet, tart, and bold.', 99.99, 'assets\\images\\pink 6.jpg',
'Carbonated water, Acai puree, Blackberry and strawberry juices (from concentrate), Organic cane sugar, Yerba mate extract, Vitamin C, B6, B12,
Natural berry flavors, Citric acid, Panax ginseng, Electrolytes', 'Green coffee bean extract provides a smooth source of caffeine and is rich in
chlorogenic acids, offering both energy and antioxidant benefits. Ginseng supports overall vitality by enhancing energy, focus, and stamina, while
elderberry delivers powerful immune support and antioxidant protection. B-vitamins round out the blend by boosting energy levels and helping to
reduce fatigue, making this combination ideal for sustained mental and physical performance.', 'Green coffee bean extract, Ginseng', 130),


('Tropic Ignite 6 pack', 'Tropical fusion of mango, pineapple, and guava with a spicy ginger twist.', 99.99, 'assets\\images\\orange 6.jpg', 'Natural
 tropical fruit flavors (pineapple, mango, passionfruit), Carbonated water, Organic cane sugar, Citric acid, Green tea extract, Yerba mate extract,
  Sea salt (electrolytes), Vitamin C (ascorbic acid), B-vitamins (B3, B5, B6, B12), Stevia leaf extract, Natural color (turmeric + beta-carotene)'
, 'Yerba mate extract delivers smooth, long-lasting caffeine with less crash, providing balanced energy to keep you going strong. Green tea extract
 adds an antioxidant-rich boost, supporting both energy and overall wellness. Vitamin C helps strengthen immunity while promoting healthy skin, 
 making it a vital part of daily vitality. Sea salt replenishes essential electrolytes, keeping you hydrated and balanced during active lifestyles.
  Rounding out the formula, B-vitamins support energy metabolism, helping your body efficiently turn food into the fuel it needs.', 'Green tea 
  extract, Yerba mate extract', 110),


('Neon Apple 6 pack', 'Crisp green apple flavor with a touch of kiwi — sharp and revitalizing.', 99.99, 'assets\\images\\green 6.jpg', 'Carbonated 
water, Natural apple and kiwi flavors, Organic cane sugar, Citric acid, Matcha green tea powder, Guarana seed extract, Sea salt (electrolytes), 
Vitamin C (ascorbic acid), B-vitamins (B3, B5, B6, B12), Stevia leaf extract, Natural color (chlorophyll + turmeric)', 'Matcha green tea delivers 
a steady stream of caffeine paired with L-theanine for calm, focused energy without the crash. Guarana seed extract extends this effect with a 
prolonged energy release, keeping you powered for longer. Vitamin C boosts the immune system, helping protect your health, while sea salt supports 
hydration balance by replenishing essential electrolytes. To round it out, B-vitamins aid in converting food into usable energy, ensuring your body
 runs at peak performance.', 'Matcha green tea, Guarana seed extract', 115),


('Electric Grape 6 pack', 'Deep, juicy grape infused with light carbonation and vitamin boost.', 99.99, 'assets\\images\\purple 6.jpg', 'Carbonated 
water, Natural grape and acai flavors, Organic cane sugar, Citric acid, Green tea extract, Guarana seed extract, Sea salt (electrolytes), Vitamin C
 (ascorbic acid), B-vitamins (B3, B5, B6, B12), Stevia leaf extract, Natural color (anthocyanins from grape skin)', 'Green tea extract provides 
 clean, focused energy that keeps you alert without the jitters, while guarana seed extract enhances both alertness and endurance for sustained 
 performance. Acai offers powerful antioxidant support, promoting overall wellness and helping to combat oxidative stress. A touch of sea salt 
 aids in maintaining proper hydration and electrolyte balance, essential for staying at your best. Completing the blend, B-vitamins support 
 energy metabolism, ensuring your body efficiently converts nutrients into lasting fuel.', ' Green tea extract, Guarana seed extract', 125);
