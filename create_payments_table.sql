-- Create Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Commissions Table (to track the rewards)
CREATE TABLE IF NOT EXISTS commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    -- The referrer receiving the commission
    from_user_id INT NOT NULL,
    -- The user who made the payment
    amount DECIMAL(10, 2) NOT NULL,
    points INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ensure Users table has necessary columns (Run these if columns don't exist)
-- ALTER TABLE users ADD COLUMN referrer_id INT DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00;
-- ALTER TABLE users ADD COLUMN points INT DEFAULT 0;