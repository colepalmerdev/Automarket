-- Car Marketplace Database Schema
-- MySQL Database

CREATE DATABASE IF NOT EXISTS car_marketplace;
USE car_marketplace;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    role ENUM('buyer', 'seller', 'rental_customer', 'admin') DEFAULT 'buyer',
    profile_image VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Car Brands Table
CREATE TABLE car_brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    logo VARCHAR(255),
    country VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Car Models Table
CREATE TABLE car_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    year_start INT,
    year_end INT,
    FOREIGN KEY (brand_id) REFERENCES car_brands(id) ON DELETE CASCADE
);

-- Cars Table (For Sale)
CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    brand_id INT NOT NULL,
    model_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    year INT NOT NULL,
    mileage INT,
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid', 'lpg') NOT NULL,
    transmission ENUM('manual', 'automatic', 'cvt') NOT NULL,
    body_type ENUM('sedan', 'suv', 'hatchback', 'coupe', 'convertible', 'pickup', 'van', 'wagon') NOT NULL,
    color VARCHAR(30),
    engine_capacity VARCHAR(20),
    power VARCHAR(20),
    location VARCHAR(100) NOT NULL,
    images JSON,
    is_featured BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    is_sold BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES car_brands(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES car_models(id) ON DELETE CASCADE
);

-- Rental Cars Table
CREATE TABLE rental_cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    brand_id INT NOT NULL,
    model_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    daily_rate DECIMAL(8,2) NOT NULL,
    weekly_rate DECIMAL(8,2),
    monthly_rate DECIMAL(8,2),
    year INT NOT NULL,
    mileage INT,
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid', 'lpg') NOT NULL,
    transmission ENUM('manual', 'automatic', 'cvt') NOT NULL,
    body_type ENUM('sedan', 'suv', 'hatchback', 'coupe', 'convertible', 'pickup', 'van', 'wagon') NOT NULL,
    color VARCHAR(30),
    engine_capacity VARCHAR(20),
    power VARCHAR(20),
    location VARCHAR(100) NOT NULL,
    images JSON,
    is_available BOOLEAN DEFAULT TRUE,
    is_approved BOOLEAN DEFAULT FALSE,
    min_rental_days INT DEFAULT 1,
    security_deposit DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES car_brands(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES car_models(id) ON DELETE CASCADE
);

-- Bookings Table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rental_car_id INT NOT NULL,
    customer_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    return_time TIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    security_deposit DECIMAL(8,2),
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method ENUM('cash', 'credit_card', 'mpesa') NOT NULL,
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rental_car_id) REFERENCES rental_cars(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wishlist Table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, car_id)
);

-- Reviews Table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT,
    rental_car_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (rental_car_id) REFERENCES rental_cars(id) ON DELETE CASCADE,
    CHECK ((car_id IS NOT NULL) XOR (rental_car_id IS NOT NULL))
);

-- Messages Table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    car_id INT,
    rental_car_id INT,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
    FOREIGN KEY (rental_car_id) REFERENCES rental_cars(id) ON DELETE SET NULL
);

-- Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT,
    car_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'mpesa') NOT NULL,
    transaction_id VARCHAR(255),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL
);

-- Recently Viewed Cars Table
CREATE TABLE recently_viewed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT,
    rental_car_id INT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (rental_car_id) REFERENCES rental_cars(id) ON DELETE CASCADE,
    CHECK ((car_id IS NOT NULL) XOR (rental_car_id IS NOT NULL))
);

-- Settings Table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default data
INSERT INTO car_brands (name, country) VALUES 
('Toyota', 'Japan'), ('Honda', 'Japan'), ('Ford', 'USA'), ('BMW', 'Germany'),
('Mercedes-Benz', 'Germany'), ('Audi', 'Germany'), ('Volkswagen', 'Germany'),
('Tesla', 'USA'), ('Nissan', 'Japan'), ('Hyundai', 'South Korea'),
('Kia', 'South Korea'), ('Mazda', 'Japan'), ('Subaru', 'Japan'),
('Lexus', 'Japan'), ('Infiniti', 'Japan'), ('Acura', 'USA');

INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'AutoMarket Pro', 'Website name'),
('site_email', 'info@automarketpro.com', 'Contact email'),
('currency', 'USD', 'Default currency'),
('approval_required', 'true', 'Require admin approval for listings'),
('min_rental_age', '21', 'Minimum age for car rental');

-- Create indexes for better performance
CREATE INDEX idx_cars_price ON cars(price);
CREATE INDEX idx_cars_year ON cars(year);
CREATE INDEX idx_cars_location ON cars(location);
CREATE INDEX idx_cars_brand_model ON cars(brand_id, model_id);
CREATE INDEX idx_rental_cars_location ON rental_cars(location);
CREATE INDEX idx_rental_cars_rates ON rental_cars(daily_rate);
CREATE INDEX idx_bookings_dates ON bookings(pickup_date, return_date);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_reviews_rating ON reviews(rating);
