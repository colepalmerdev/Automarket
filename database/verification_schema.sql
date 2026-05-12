-- Registration Verification Table
CREATE TABLE user_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    verification_token VARCHAR(255) NOT NULL,
    token_type ENUM('registration', 'password_reset', 'email_change') DEFAULT 'registration',
    is_used BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (verification_token),
    INDEX idx_email (email),
    INDEX idx_user_id (user_id)
);

-- Add verification_token column to users table for backward compatibility
ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL;
