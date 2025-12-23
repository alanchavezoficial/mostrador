-- Agrega columna api_key a users y tabla de logs de uso
ALTER TABLE users ADD COLUMN api_key VARCHAR(64) UNIQUE AFTER password;

CREATE TABLE IF NOT EXISTS api_key_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    used_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
