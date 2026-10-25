```sql
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(150) NOT NULL,
    date_reported DATE NOT NULL,
    contact_info VARCHAR(150) NOT NULL,
    status ENUM('Lost', 'Found', 'Claimed') NOT NULL DEFAULT 'Lost',
    claim_code VARCHAR(12) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
