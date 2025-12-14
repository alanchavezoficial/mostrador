-- ============================================
-- TABLAS PARA TESTIMONIOS Y CONTACTO
-- ============================================

-- Tabla para testimonios
CREATE TABLE IF NOT EXISTS testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author VARCHAR(100) NOT NULL,
    rating INT NOT NULL DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    content TEXT NOT NULL,
    is_visible BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_visible (is_visible),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para información de contacto
CREATE TABLE IF NOT EXISTS contact_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    field_key VARCHAR(50) UNIQUE NOT NULL,
    field_value TEXT NOT NULL,
    field_type VARCHAR(20) DEFAULT 'text',
    label VARCHAR(100),
    icon VARCHAR(50),
    is_visible BOOLEAN DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_key (field_key),
    INDEX idx_visible (is_visible),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Datos por defecto de contacto
INSERT INTO contact_info (field_key, field_value, field_type, label, icon, sort_order) VALUES
('phone', '+34911234567', 'phone', 'Teléfono', '📞', 1),
('email', 'info@propsfotograficos.com', 'email', 'Email', '📧', 2),
('address', 'Madrid, España', 'address', 'Ubicación', '📍', 3),
('hours', 'Lun-Vie: 9:00 - 18:00', 'hours', 'Horario', '🕒', 4)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Testimonios de ejemplo (opcional)
INSERT INTO testimonials (author, rating, content, is_visible) VALUES
('María García', 5, 'Excelente calidad en los productos. La compra fue rápida y el servicio al cliente muy atento.', 1),
('Juan Rodríguez', 5, 'Los precios son muy competitivos y los productos llegan en perfecto estado. Recomendado.', 1),
('Sofia Martínez', 5, 'Superó mis expectativas. Volveré a comprar sin dudarlo. Servicio profesional.', 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

