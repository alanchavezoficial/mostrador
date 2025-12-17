-- Añade flag de carrusel y asegura created_at
ALTER TABLE articles
  ADD COLUMN IF NOT EXISTS is_carousel TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE articles
  ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Índice para selección ordenada
CREATE INDEX IF NOT EXISTS idx_articles_is_carousel_created ON articles (is_carousel, created_at);
