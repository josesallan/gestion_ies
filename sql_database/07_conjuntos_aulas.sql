-- Estructura para agrupaciones de aulas (v2)
-- Adaptada a la etiqueta <conjuntoDeAulas> de Peñalara / GHC

-- 1. Tabla de conjuntos o categorías de aulas
CREATE TABLE IF NOT EXISTS conjuntos_aulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,       -- Ej: 'general', 'ed.física', 'laboratorios'
    es_general TINYINT(1) DEFAULT 0,           -- 1 para el conjunto general, 0 para especializados
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla pivote (relación M:N entre aulas y conjuntos_aulas)
CREATE TABLE IF NOT EXISTS aula_conjunto (
    aula_id INT NOT NULL,
    conjunto_id INT NOT NULL,
    PRIMARY KEY (aula_id, conjunto_id),
    CONSTRAINT fk_ac_aula FOREIGN KEY (aula_id) 
        REFERENCES aulas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ac_conjunto FOREIGN KEY (conjunto_id) 
        REFERENCES conjuntos_aulas(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;