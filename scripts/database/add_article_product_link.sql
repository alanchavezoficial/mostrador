-- Agregar columna product_id a articles para enlazar artículos con productos
ALTER TABLE articles
  ADD COLUMN IF NOT EXISTS product_id VARCHAR(36);

-- Agregar foreign key
ALTER TABLE articles
  ADD CONSTRAINT fk_articles_product_id 
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;

-- Crear índice para búsquedas por product_id
CREATE INDEX IF NOT EXISTS idx_articles_product_id ON articles(product_id);
