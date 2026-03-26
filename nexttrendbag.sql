-- ============================================================
--  NextTrendBag Database
--  Import this into phpMyAdmin → create database nexttrendbag
--  then run this file.
-- ============================================================

CREATE DATABASE IF NOT EXISTS nexttrendbag CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexttrendbag;

-- -----------------------------------------------------------
-- USERS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(120) NOT NULL,
  email        VARCHAR(180) NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  phone        VARCHAR(20)  DEFAULT NULL,
  address      TEXT         DEFAULT NULL,
  city         VARCHAR(80)  DEFAULT NULL,
  state        VARCHAR(80)  DEFAULT NULL,
  pincode      VARCHAR(15)  DEFAULT NULL,
  is_admin     TINYINT(1)   NOT NULL DEFAULT 0,
  reset_token  VARCHAR(100) DEFAULT NULL,
  reset_expiry DATETIME     DEFAULT NULL,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin@nexttrendbag.com / admin123
INSERT INTO users (name, email, password, is_admin) VALUES
('Admin', 'admin@nexttrendbag.com', '$2y$10$OUaEeWwlehywp3kSlIqf8upkOe2XolW2bTWZL5Ii2lJdeYOWBvrWi', 1);
-- password above = "admin123" (bcrypt via password_hash)

-- -----------------------------------------------------------
-- PRODUCTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(200) NOT NULL,
  category       ENUM('backpacks','handbags','laptop','travel') NOT NULL,
  price          DECIMAL(10,2) NOT NULL,
  original_price DECIMAL(10,2) DEFAULT NULL,
  image          VARCHAR(255) NOT NULL DEFAULT 'images/bag4.jpeg',
  stock          INT NOT NULL DEFAULT 50,
  rating         DECIMAL(3,1) NOT NULL DEFAULT 4.5,
  description    TEXT         DEFAULT NULL,
  badge          VARCHAR(40)  DEFAULT NULL,
  is_featured    TINYINT(1)   NOT NULL DEFAULT 0,
  color          VARCHAR(30)  DEFAULT 'brown',
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (name, category, price, original_price, image, stock, rating, description, badge, is_featured, color) VALUES
-- Backpacks
('Urban Explorer Backpack',    'backpacks', 2999, 3999, 'images/Screenshot 2026-02-24 093022.png', 45, 4.8, 'The Urban Explorer Backpack is the perfect companion for your daily commute. Crafted from water-resistant polyester with reinforced zippers and a padded 15.6\" laptop compartment. Ergonomic straps ensure all-day comfort while multiple pockets keep you organised.', 'Best Seller', 1, 'brown'),
('Classic Campus Bag',         'backpacks', 1799, 2399, 'images/bag4.jpeg',   60, 4.5, 'A timeless campus-style backpack built for students. Spacious main compartment, front organiser pocket, and side bottle holder. Available in neutral tones that match any outfit.', 'New Arrival', 0, 'beige'),
('Adventure Pro Backpack',     'backpacks', 3499, 4999, 'images/bag5.jpeg',   30, 4.7, 'Designed for weekend adventures and day hikes. Features a roll-top closure, chest strap, hip belt, and reflective accents for safety. Durable 600D ripstop nylon construction.', 'Top Rated', 1, 'black'),
('Mini Daypack',               'backpacks', 1299, 1799, 'images/bagimg.jpg',  80, 4.3, 'Lightweight and packable mini daypack — ideal for day trips, walks, or as a personal item on flights. Folds into its own pocket when not in use.', NULL, 0, 'beige'),

-- Handbags
('Luxe Tote Handbag',          'handbags',  2499, 3299, 'images/bag5.jpeg',   40, 4.9, 'A structured tote in premium vegan leather. Fits everything from your laptop to your gym kit. Magnetic snap closure, interior zip pocket, and detachable pouch.', 'Best Seller', 1, 'tan'),
('Crossbody Sling Bag',        'handbags',  1599, 2199, 'images/bag4.jpeg',   55, 4.6, 'Hands-free style with this compact crossbody. Adjustable strap, RFID-blocking inner pocket, and scratch-resistant lining. Perfect for travel and everyday errands.', 'Trending', 1, 'brown'),
('Boxy Shoulder Bag',          'handbags',  2199, 2999, 'images/bagimg.jpg',  35, 4.4, 'Structured boxy silhouette with a suede effect finish. Gold-tone hardware, inside mirror, and chain shoulder strap. A statement bag for evenings out.', NULL, 0, 'beige'),
('Woven Basket Bag',           'handbags',  1899, 2599, 'images/bag5.jpeg',   25, 4.2, 'Hand-woven rattan basket bag with leather handles and a cotton lining. A sustainable fashion choice that effortlessly elevates your summer look.', 'Eco Pick', 0, 'tan'),

-- Laptop Bags
('Pro Laptop Sleeve 15"',      'laptop',    1499, 1999, 'images/breifcase.jpeg', 70, 4.7, 'Slim, padded sleeve for 15\" laptops. Memory-foam lining protects against bumps. External pocket for charger and accessories. Fits in any backpack or carry-on.', 'New Arrival', 0, 'black'),
('Executive Briefcase',        'laptop',    3999, 5499, 'images/breifcase.jpeg', 20, 4.8, 'Premium full-grain leather briefcase with dedicated laptop (up to 16\") and tablet compartments. Retractable carry handle, combination lock, and detachable shoulder strap — the ultimate professional bag.', 'Premium', 1, 'brown'),
('Canvas Laptop Messenger',    'laptop',    2299, 3099, 'images/bag4.jpeg',   50, 4.5, 'Casual waxed-canvas messenger bag with a padded laptop sleeve, multiple small pockets, and quick-release buckle closure. Vintage aesthetic meets modern function.', 'Top Rated', 0, 'tan'),

-- Travel Bags
('Weekender Duffel',           'travel',    3299, 4499, 'images/Screenshot 2026-02-24 093311.png', 30, 4.8, 'Carry-on compliant duffel bag with a wet/dry compartment, shoe pocket, and trolley sleeve. Durable ripstop exterior with reinforced handles. Your ideal travel companion for short trips.', 'Best Seller', 1, 'navy'),
('Rolling Trolley Bag',        'travel',    5999, 7999, 'images/hiking.jpeg', 15, 4.6, '20\" cabin-size rolling trolley with spinner wheels, TSA-approved combination lock, and expandable gusset. Hard-shell ABS exterior keeps your belongings safe.', 'Premium', 0, 'black'),
('Hiking Day Pack 28L',        'travel',    2799, 3799, 'images/hiking.jpeg', 40, 4.7, 'Technical hiking daypack with 28L capacity, hydration reservoir sleeve, trekking pole attachment, and breathable back panel. Waterproof rain cover included.', 'Trending', 1, 'beige');


-- -----------------------------------------------------------
-- CART
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  product_id INT NOT NULL,
  quantity   INT NOT NULL DEFAULT 1,
  added_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_cart (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- WISHLIST
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlist (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  product_id INT NOT NULL,
  added_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_wishlist (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- ORDERS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  user_id          INT NOT NULL,
  subtotal         DECIMAL(10,2) NOT NULL DEFAULT 0,
  shipping         DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount         DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax              DECIMAL(10,2) NOT NULL DEFAULT 0,
  total            DECIMAL(10,2) NOT NULL DEFAULT 0,
  status           ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  payment_method   VARCHAR(50) DEFAULT 'COD',
  coupon_code      VARCHAR(50) DEFAULT NULL,
  -- Snapshot of shipping address
  ship_name        VARCHAR(150) DEFAULT NULL,
  ship_email       VARCHAR(180) DEFAULT NULL,
  ship_phone       VARCHAR(20)  DEFAULT NULL,
  ship_address     TEXT         DEFAULT NULL,
  ship_city        VARCHAR(80)  DEFAULT NULL,
  ship_state       VARCHAR(80)  DEFAULT NULL,
  ship_pincode     VARCHAR(15)  DEFAULT NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- ORDER ITEMS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  order_id   INT NOT NULL,
  product_id INT NOT NULL,
  quantity   INT NOT NULL DEFAULT 1,
  price      DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- COUPONS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  code             VARCHAR(50)  NOT NULL UNIQUE,
  discount_percent DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  max_uses         INT          NOT NULL DEFAULT 100,
  used             INT          NOT NULL DEFAULT 0,
  expires_at       DATE         DEFAULT NULL,
  is_active        TINYINT(1)   NOT NULL DEFAULT 1,
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO coupons (code, discount_percent, max_uses, expires_at) VALUES
('NEXT30',  30.00, 1000, '2026-12-31'),
('WELCOME10',10.00, 500,  '2026-12-31'),
('BAG20',    20.00, 200,  '2026-06-30');

-- -----------------------------------------------------------
-- NEWSLETTER SUBSCRIBERS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscribers (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(180) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
