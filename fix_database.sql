-- Run this in your database SQL tab to fix the "Unknown column" error
ALTER TABLE
    users
ADD
    COLUMN referrer_id INT DEFAULT NULL;

ALTER TABLE
    users
ADD
    COLUMN wallet_balance DECIMAL(10, 2) DEFAULT 0.00;

ALTER TABLE
    users
ADD
    COLUMN points INT DEFAULT 0;

ALTER TABLE
    users
ADD
    COLUMN city VARCHAR(100) DEFAULT NULL;

ALTER TABLE
    users
ADD
    COLUMN phone VARCHAR(20) DEFAULT NULL;