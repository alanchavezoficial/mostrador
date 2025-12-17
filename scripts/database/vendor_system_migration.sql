-- ================================================================
-- MIGRACIONES PARA SISTEMA DE ROL VENDEDOR
-- ================================================================
-- Archivo: scripts/database/vendor_system_migration.sql
-- Descripción: Agregaciones recomendadas para soportar vendedores
-- Fecha: Diciembre 16, 2025
-- ================================================================

-- ================================================================
-- 1. AGREGAR CAMPO user_id A TABLA products
-- ================================================================
-- Este campo identifica qué vendedor creó cada producto
ALTER TABLE products ADD COLUMN user_id INT AFTER id;

-- Crear índice para búsquedas rápidas
CREATE INDEX idx_products_user ON products(user_id);

-- Crear relación con tabla users (opcional pero recomendado)
-- ALTER TABLE products ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;

-- ================================================================
-- 2. AGREGAR CAMPOS ADICIONALES A TABLA users
-- ================================================================
-- Estos campos almacenan información adicional del vendedor

-- Dirección de envío
ALTER TABLE users ADD COLUMN direccion VARCHAR(255) AFTER email IF NOT EXISTS;

-- Teléfono de contacto
ALTER TABLE users ADD COLUMN telefono VARCHAR(20) AFTER direccion IF NOT EXISTS;

-- Descripción del negocio
ALTER TABLE users ADD COLUMN descripcion TEXT AFTER telefono IF NOT EXISTS;

-- Comisión del vendedor (porcentaje)
ALTER TABLE users ADD COLUMN comision DECIMAL(5,2) DEFAULT 0 AFTER descripcion IF NOT EXISTS;

-- URL de logotipo de tienda
ALTER TABLE users ADD COLUMN logo_url VARCHAR(255) AFTER comision IF NOT EXISTS;

-- ================================================================
-- 3. CREAR TABLA DE CATEGORIZACIONES POR VENDEDOR (Opcional)
-- ================================================================
-- Para cuando un vendedor quiera tener sus propias categorías

CREATE TABLE IF NOT EXISTS vendor_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    position INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_vendor_categories_user (user_id),
    UNIQUE KEY unique_vendor_category (user_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 4. CREAR TABLA DE COMISIONES (Opcional)
-- ================================================================
-- Para rastrear historial de comisiones y pagos

CREATE TABLE IF NOT EXISTS vendor_commissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_id INT,
    amount DECIMAL(10,2) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_commissions_user (user_id),
    INDEX idx_commissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 5. CREAR TABLA DE DOCUMENTOS DEL VENDEDOR
-- ================================================================
-- Para almacenar documentación, licencias, certificados

CREATE TABLE IF NOT EXISTS vendor_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    document_type ENUM('license', 'certificate', 'identification', 'other') DEFAULT 'other',
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    verified_by INT,
    verified_at TIMESTAMP NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_documents_user (user_id),
    INDEX idx_documents_verified (verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 6. AGREGAR CAMPO created_by A TABLA orders
-- ================================================================
-- Para auditoría: quién creó la orden (admin que registró, o sistema)

ALTER TABLE orders ADD COLUMN created_by INT AFTER user_id IF NOT EXISTS;

-- ================================================================
-- 7. CREAR VISTA PARA ESTADÍSTICAS POR VENDEDOR
-- ================================================================
-- View que facilita obtener estadísticas de cada vendedor

CREATE OR REPLACE VIEW vendor_statistics AS
SELECT 
    u.id,
    u.nombre,
    u.email,
    COUNT(DISTINCT p.id) as total_products,
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(oi.quantity), 0) as total_items_sold,
    COALESCE(SUM(o.total_amount), 0) as total_sales,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(DISTINCT r.id) as total_reviews,
    u.created_at,
    MAX(o.created_at) as last_order_date
FROM users u
LEFT JOIN products p ON u.id = p.user_id
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id
LEFT JOIN reviews r ON p.id = r.product_id
WHERE u.role = 'vendedor'
GROUP BY u.id, u.nombre, u.email, u.created_at;

-- ================================================================
-- 8. CREAR ÍNDICES ADICIONALES PARA RENDIMIENTO
-- ================================================================

-- Índice en orders para búsquedas por vendedor (si agregamos relación)
CREATE INDEX idx_orders_created_by ON orders(created_by);

-- Índice en products para búsquedas por fecha
CREATE INDEX idx_products_user_created ON products(user_id, created_at DESC);

-- Índice en order_items para búsquedas de ventas
CREATE INDEX idx_order_items_product ON order_items(product_id);

-- ================================================================
-- 9. CREAR TRIGGER PARA AUDITAR CAMBIOS EN PRODUCTOS DE VENDEDOR
-- ================================================================

DELIMITER //

CREATE TRIGGER audit_vendor_products_update
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values, user_id, created_at)
    VALUES (
        'products',
        NEW.id,
        'UPDATE',
        JSON_OBJECT(
            'name', OLD.name,
            'price', OLD.price,
            'stock', OLD.stock,
            'description', OLD.description
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'price', NEW.price,
            'stock', NEW.stock,
            'description', NEW.description
        ),
        NEW.user_id,
        NOW()
    );
END //

CREATE TRIGGER audit_vendor_products_delete
AFTER DELETE ON products
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, user_id, created_at)
    VALUES (
        'products',
        OLD.id,
        'DELETE',
        JSON_OBJECT(
            'name', OLD.name,
            'price', OLD.price,
            'stock', OLD.stock
        ),
        OLD.user_id,
        NOW()
    );
END //

DELIMITER ;

-- ================================================================
-- 10. DATOS DE EJEMPLO (OPCIONAL)
-- ================================================================
-- Descomentar para agregar datos de prueba

/*
-- Crear vendedor de ejemplo
INSERT INTO users (nombre, email, password, role, direccion, telefono, descripcion, created_at, updated_at)
VALUES (
    'Juan Vendedor Ejemplo',
    'juan@ejemplo.com',
    '$2y$10$examplehash',
    'vendedor',
    'Calle Principal 123, Oficina 5',
    '+54 9 1234-5678',
    'Vendedor de artículos de calidad con más de 5 años de experiencia.',
    NOW(),
    NOW()
);

-- Asignar producto a vendedor (reemplazar IDs según corresponda)
UPDATE products SET user_id = (SELECT id FROM users WHERE email = 'juan@ejemplo.com' LIMIT 1) LIMIT 1;
*/

-- ================================================================
-- 11. VERIFICACIONES Y VALIDACIÓN
-- ================================================================

-- Ver estructura actual de tabla users
-- DESCRIBE users;

-- Ver estructura actual de tabla products
-- DESCRIBE products;

-- Ver estadísticas de vendedores
-- SELECT * FROM vendor_statistics;

-- Ver últimos cambios en audit_log para productos
-- SELECT * FROM audit_log WHERE table_name = 'products' ORDER BY created_at DESC LIMIT 10;

-- ================================================================
-- FIN DE MIGRACIONES
-- ================================================================
-- Estado: Listo para ejecutar
-- Fecha de ejecución recomendada: Después de crear rol vendedor en base de datos
-- Backup recomendado: SÍ (antes de ejecutar estas migraciones)
-- ================================================================
