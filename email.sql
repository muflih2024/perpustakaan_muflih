-- Add email column if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) UNIQUE;

-- Add reset token columns
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64);
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires TIMESTAMP NULL;

-- Update dummy emails
UPDATE users SET email = 'admin@perpustakaan.com' WHERE username = 'admin' AND email IS NULL;
UPDATE users SET email = 'user1@perpustakaan.com' WHERE username = 'user1' AND email IS NULL;
