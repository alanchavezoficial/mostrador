-- =====================================================
-- Migración: Agregar propiedad de productos (user_id)
-- Fecha: 2025-12-16
-- Descripción: Añade columna user_id a products con FK
--              para asignar productos a usuarios vendedores
-- =====================================================

-- 1. Agregar columna user_id (INT para coincidir con users.id)
ALTER TABLE products 
ADD COLUMN user_id INT NULL 
COMMENT 'FK al usuario vendedor dueño del producto';

-- 2. Asignar productos existentes al vendedor por defecto
-- (ajustar el ID según el vendedor que corresponda en producción)
UPDATE products 
SET user_id = (
    SELECT id FROM users 
    WHERE role = 'vendedor' 
    LIMIT 1
) 
WHERE user_id IS NULL;

-- 3. Agregar índice para mejorar rendimiento de consultas por vendedor
ALTER TABLE products 
ADD INDEX idx_products_user_id (user_id);

-- 4. Agregar FK con cascada en UPDATE, restringir DELETE
-- (impide borrar usuario si tiene productos; protege integridad)
ALTER TABLE products 
ADD CONSTRAINT fk_products_user 
FOREIGN KEY (user_id) REFERENCES users(id) 
ON UPDATE CASCADE 
ON DELETE RESTRICT;

-- 5. Hacer columna NOT NULL ahora que todos tienen valor asignado
ALTER TABLE products 
MODIFY COLUMN user_id INT NOT NULL;

-- =====================================================
-- Verificación
-- =====================================================
-- Ver estructura actualizada:
DESCRIBE products;

-- Confirmar asignación:
SELECT 
    u.nombre AS vendedor,
    COUNT(p.id) AS productos_asignados
FROM products p
INNER JOIN users u ON p.user_id = u.id
GROUP BY u.id, u.nombre;

-- Ver constraints:
SELECT 
    CONSTRAINT_NAME,
    CONSTRAINT_TYPE 
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
WHERE TABLE_NAME = 'products';
