-- Agregar columnas de seguimiento a la tabla orders
ALTER TABLE orders ADD COLUMN tracking_code VARCHAR(100) AFTER transaction_id;
ALTER TABLE orders ADD COLUMN shipping_status VARCHAR(100) AFTER tracking_code;
