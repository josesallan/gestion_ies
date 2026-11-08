-- Estructura de la tabla aulas (v2)
-- Adaptada a la exportación XML de Peñalara / GHC y SIGAD

CREATE TABLE IF NOT EXISTS aulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Mapeo con Peñalara / SIGAD
    codigo_sigad VARCHAR(50) NULL UNIQUE,       -- Extraído de <nombre> (ej: 1)
    clave_penalara VARCHAR(50) NULL UNIQUE,     -- Extraído de <claveDeExportacion> (ej: 1)
    abreviatura VARCHAR(50) NOT NULL,           -- Extraído de <abreviatura> (ej: 302)
    
    -- Datos descriptivos
    descripcion VARCHAR(255) NULL,              -- Extraído de <descripcion> (ej: 302)
    
    -- Estado y auditoría
    es_activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;