-- SQLite Database Schema for StitchHub (Testing)
-- Use this for testing without MySQL

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    phone_verified INTEGER DEFAULT 0,
    is_verified INTEGER DEFAULT 0,
    address TEXT,
    bio TEXT,
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    is_active INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- OTP Verification Table
CREATE TABLE IF NOT EXISTS otp_verification (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    phone VARCHAR(20) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    otp_type VARCHAR(20) DEFAULT 'registration',
    expires_at DATETIME NOT NULL,
    is_verified INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password Reset Tokens Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_used INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tailor Profiles Table (Women tailors in Swabi)
CREATE TABLE IF NOT EXISTS tailor_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    cnic VARCHAR(20),
    experience VARCHAR(50),
    location VARCHAR(100),
    gender VARCHAR(20) DEFAULT 'female',
    bio TEXT,
    verification_status VARCHAR(20) DEFAULT 'pending',
    cnic_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Designs Table (Tailor's portfolio)
CREATE TABLE IF NOT EXISTS designs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tailor_id INTEGER NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    price DECIMAL(10, 2),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tailor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Measurements Table (Customer's saved measurements)
CREATE TABLE IF NOT EXISTS measurements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    title VARCHAR(100) NOT NULL,
    chest DECIMAL(5, 2),
    waist DECIMAL(5, 2),
    hips DECIMAL(5, 2),
    shoulder DECIMAL(5, 2),
    sleeve_length DECIMAL(5, 2),
    total_length DECIMAL(5, 2),
    neck DECIMAL(5, 2),
    arm_length DECIMAL(5, 2),
    pant_length DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    tailor_id INTEGER NOT NULL,
    design_id INTEGER,
    measurement_id INTEGER,
    status VARCHAR(20) DEFAULT 'pending',
    delivery_type VARCHAR(20) DEFAULT 'pickup',
    delivery_address TEXT,
    total_amount DECIMAL(10, 2) NOT NULL,
    commission_amount DECIMAL(10, 2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tailor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (design_id) REFERENCES designs(id) ON DELETE SET NULL,
    FOREIGN KEY (measurement_id) REFERENCES measurements(id) ON DELETE SET NULL
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(30) DEFAULT 'pending',
    transaction_id VARCHAR(100),
    method VARCHAR(50) DEFAULT 'Simulated Card',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL UNIQUE,
    customer_id INTEGER NOT NULL,
    tailor_id INTEGER NOT NULL,
    rating INTEGER NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tailor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages Table
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER NOT NULL,
    receiver_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Initial Admin (password: admin123)
INSERT INTO users (name, email, password, role, is_verified) VALUES ('Admin', 'admin@stitchhub.com', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lWz5m5f5vH2a', 'admin', 1);