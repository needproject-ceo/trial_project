-- ============================================================
-- Gauri Collections - Database Schema
-- Antique Indian God Statues E-Commerce
-- ============================================================

CREATE DATABASE IF NOT EXISTS gauri_collections
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gauri_collections;

-- ============================================================
-- Settings Table
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Gauri Collections'),
('site_tagline', 'Exquisite Antique Statues of Indian Gods'),
('site_email', 'info@gauricollections.com'),
('site_phone', '+91 98765 43210'),
('site_address', '42 Heritage Lane, Jaipur, Rajasthan, India 302001'),
('currency_symbol', '₹'),
('shipping_charge', '500'),
('free_shipping_above', '5000'),
('tax_rate', '18'),
('footer_text', '© 2026 Gauri Collections. All rights reserved. Curating divine artistry since 1985.'),
('about_text', 'Gauri Collections is a premier destination for exquisite antique statues of Indian gods and goddesses. With over four decades of expertise, we curate and present the finest pieces of divine artistry, each carrying centuries of heritage and spiritual significance.'),
('meta_description', 'Gauri Collections - Premium antique statues of Indian gods and goddesses. Handcrafted divine sculptures with centuries of heritage.'),
('razorpay_key', ''),
('razorpay_secret', '');

-- ============================================================
-- Categories Table
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(500),
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO categories (name, slug, description, sort_order) VALUES
('Lord Ganesha', 'lord-ganesha', 'Antique statues of Lord Ganesha, the remover of obstacles and god of beginnings.', 1),
('Lord Shiva', 'lord-shiva', 'Magnificent antique Shiva statues including Nataraja and Lingam forms.', 2),
('Lord Vishnu', 'lord-vishnu', 'Exquisite antique statues of Lord Vishnu and his avatars.', 3),
('Goddess Lakshmi', 'goddess-lakshmi', 'Beautiful antique statues of Goddess Lakshmi, the deity of wealth and prosperity.', 4),
('Goddess Saraswati', 'goddess-saraswati', 'Elegant antique statues of Goddess Saraswati, the deity of knowledge and arts.', 5),
('Lord Krishna', 'lord-krishna', 'Enchanting antique statues of Lord Krishna in various divine poses.', 6),
('Lord Hanuman', 'lord-hanuman', 'Powerful antique statues of Lord Hanuman, the devoted servant of Lord Rama.', 7),
('Buddha', 'buddha', 'Serene antique Buddha statues embodying peace and enlightenment.', 8);

-- ============================================================
-- Products Table
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(500) NOT NULL,
    slug VARCHAR(500) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(1000),
    price DECIMAL(12,2) NOT NULL,
    sale_price DECIMAL(12,2) DEFAULT NULL,
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    material VARCHAR(255),
    height VARCHAR(100),
    weight VARCHAR(100),
    origin VARCHAR(255),
    era VARCHAR(255),
    condition_note VARCHAR(500),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    image VARCHAR(500),
    image_2 VARCHAR(500),
    image_3 VARCHAR(500),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, stock_quantity, category_id, material, height, weight, origin, era, condition_note, is_featured, image) VALUES
('Antique Brass Ganesha - Dancing Pose', 'antique-brass-ganesha-dancing', 'This magnificent antique brass statue depicts Lord Ganesha in his joyful dancing pose (Nritya Ganapati). The intricate detailing on the ornaments, the graceful movement captured in bronze, and the serene expression make this piece a true masterpiece of Indian craftsmanship.', 'Exquisite 19th-century brass Ganesha in dancing pose with intricate detailing.', 45000.00, 38500.00, 'GC-GAN-001', 3, 1, 'Brass', '18 inches', '4.5 kg', 'Tamil Nadu, India', '19th Century', 'Excellent - Minor patina consistent with age', 1, 'ganesha_dancing.jpg'),
('Nataraja Shiva - Cosmic Dance', 'nataraja-shiva-cosmic-dance', 'An extraordinary antique bronze Nataraja depicting Lord Shiva performing the cosmic dance of creation and destruction (Tandava). This museum-quality piece features exceptional detail in the ring of fire.', 'Museum-quality bronze Nataraja with exceptional cosmic dance detailing.', 125000.00, NULL, 'GC-SHI-001', 1, 2, 'Bronze', '24 inches', '8.2 kg', 'Chidambaram, Tamil Nadu', '18th Century', 'Excellent - Beautiful aged patina', 1, 'nataraja_shiva.jpg'),
('Lord Vishnu - Standing Pose', 'lord-vishnu-standing-pose', 'A magnificent antique stone statue of Lord Vishnu in his characteristic standing pose (Sthanaka). The four arms hold the Shankha (conch), Chakra (discus), Gada (mace), and Padma (lotus).', 'Chola dynasty stone Vishnu statue with four characteristic attributes.', 85000.00, 75000.00, 'GC-VIS-001', 2, 3, 'Granite Stone', '22 inches', '12 kg', 'Thanjavur, Tamil Nadu', '12th Century (Chola Period)', 'Good - Some weathering adds character', 1, 'vishnu_standing.jpg'),
('Goddess Lakshmi - Gajalakshmi', 'goddess-lakshmi-gajalakshmi', 'This stunning antique bronze depicts Goddess Lakshmi in the Gajalakshmi form, flanked by elephants showering her with water - symbolizing royal power and fertility.', 'Rare Vijayanagara period bronze Gajalakshmi with flanking elephants.', 95000.00, NULL, 'GC-LAK-001', 1, 4, 'Bronze', '16 inches', '5.8 kg', 'Hampi, Karnataka', '15th Century (Vijayanagara Period)', 'Very Good - Minor restoration to base', 1, 'lakshmi_gajalakshmi.jpg'),
('Goddess Saraswati - Veena Player', 'goddess-saraswati-veena-player', 'A delicate and finely crafted antique marble statue of Goddess Saraswati playing the Veena. The flowing robes, the gentle smile, and the intricate Veena demonstrate the pinnacle of Rajasthani marble carving tradition.', 'Rajasthani marble Saraswati playing Veena on lotus throne.', 65000.00, 58000.00, 'GC-SAR-001', 4, 5, 'White Marble', '20 inches', '9.5 kg', 'Jaipur, Rajasthan', '19th Century', 'Excellent - Pristine condition', 1, 'saraswati_veena.jpg'),
('Lord Krishna - Butter Thief', 'lord-krishna-butter-thief', 'An absolutely charming antique brass figurine of baby Krishna (Bal Gopal) in the beloved Makhan Chor or butter thief pose.', 'Charming brass Bal Gopal (baby Krishna) in butter thief pose.', 28000.00, 24000.00, 'GC-KRI-001', 6, 6, 'Brass', '8 inches', '1.2 kg', 'Mathura, Uttar Pradesh', 'Early 20th Century', 'Excellent - Beautiful golden patina', 1, 'krishna_butter_thief.jpg'),
('Lord Hanuman - Mighty Warrior', 'lord-hanuman-mighty-warrior', 'A powerful and dynamic antique bronze statue of Lord Hanuman in his warrior form, holding the Gada (mace) and the Sanjeevani mountain.', 'Dynamic bronze Hanuman warrior with Gada and Sanjeevani mountain.', 55000.00, NULL, 'GC-HAN-001', 2, 7, 'Bronze', '20 inches', '6.5 kg', 'Hampi, Karnataka', '17th Century', 'Good - Battle-worn patina adds authenticity', 0, 'hanuman_warrior.jpg'),
('Meditating Buddha - Dhyana Mudra', 'meditating-buddha-dhyana-mudra', 'A serene and contemplative antique stone Buddha statue in the Dhyana (meditation) mudra. The closed eyes, peaceful expression, and perfect proportions embody ultimate inner peace.', 'Sarnath sandstone Buddha in deep meditation pose.', 72000.00, 65000.00, 'GC-BUD-001', 3, 8, 'Sandstone', '18 inches', '15 kg', 'Sarnath, Uttar Pradesh', '10th Century', 'Good - Natural weathering, structurally sound', 1, 'buddha_meditation.jpg'),
('Ganesha - Seated on Throne', 'ganesha-seated-throne', 'A regal antique wooden Ganesha seated on an ornate throne (Simhasana). Carved from a single piece of rosewood, this statue showcases remarkable woodworking skills of Kerala artisans.', 'Kerala rosewood Ganesha on ornate throne with exquisite carving.', 38000.00, NULL, 'GC-GAN-002', 5, 1, 'Rosewood', '14 inches', '3.2 kg', 'Kerala, India', 'Late 19th Century', 'Very Good - Rich wood patina', 0, 'ganesha_throne.jpg'),
('Shiva Lingam - Brass Yoni Base', 'shiva-lingam-brass-yoni', 'A sacred antique brass Shiva Lingam mounted on a beautifully crafted Yoni base. Includes traditional Naga (serpent) hood.', 'Sacred brass Shiva Lingam with Yoni base and Naga hood.', 32000.00, 28000.00, 'GC-SHI-002', 4, 2, 'Brass', '10 inches', '3.8 kg', 'Varanasi, Uttar Pradesh', '18th Century', 'Good - Worship marks add sacred value', 0, 'shiva_lingam.jpg'),
('Vishnu on Garuda', 'vishnu-on-garuda', 'A spectacular antique bronze depicting Lord Vishnu riding his divine vehicle Garuda (the eagle). Exceptional piece of South Indian bronze craftsmanship from the Pallava period.', 'Pallava period bronze Vishnu riding divine eagle Garuda.', 150000.00, NULL, 'GC-VIS-002', 1, 3, 'Bronze', '26 inches', '11 kg', 'Kanchipuram, Tamil Nadu', '8th Century (Pallava Period)', 'Good - Museum quality, professionally restored', 1, 'vishnu_garuda.jpg'),
('Lakshmi-Narayana - Divine Couple', 'lakshmi-narayana-divine-couple', 'An exquisite antique bronze depicting the divine couple on Shesha Naga (the cosmic serpent). A masterpiece from the Hoysala period.', 'Rare Hoysala period bronze of divine couple on cosmic serpent.', 185000.00, 165000.00, 'GC-LAK-002', 1, 4, 'Bronze', '22 inches', '9.5 kg', 'Belur, Karnataka', '12th Century (Hoysala Period)', 'Excellent - Museum-quality preservation', 1, 'lakshmi_narayana.jpg');

-- ============================================================
-- Users Table
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin (password: Admin@123)
INSERT INTO users (first_name, last_name, email, password, phone, role) VALUES
('Admin', 'Gauri', 'admin@gauricollections.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91 98765 43210', 'admin');

-- ============================================================
-- Orders Table
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    subtotal DECIMAL(12,2) NOT NULL,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    shipping_amount DECIMAL(12,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    transaction_id VARCHAR(255),
    shipping_name VARCHAR(200),
    shipping_email VARCHAR(255),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_state VARCHAR(100),
    shipping_pincode VARCHAR(10),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Order Items Table
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(500),
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Cart Table
-- ============================================================
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    user_id INT DEFAULT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Wishlist Table
-- ============================================================
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
) ENGINE=InnoDB;

-- ============================================================
-- Reviews Table
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    comment TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Contact Messages Table
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(500),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Newsletter Subscribers
-- ============================================================
CREATE TABLE IF NOT EXISTS newsletter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Coupons Table
-- ============================================================
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(500),
    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(12,2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_uses) VALUES
('WELCOME10', 'Welcome discount - 10% off on first order', 'percentage', 10.00, 5000, 100),
('DIVINE20', '20% off on orders above 50000', 'percentage', 20.00, 50000, 50);
