-- Estructura de la tabla grupos (v2)
-- Adaptada a la exportación XML de Peñalara / GHC y SIGAD

CREATE TABLE IF NOT EXISTS grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Mapeo con Peñalara / SIGAD
    codigo_sigad VARCHAR(50) NULL UNIQUE,       -- Extraído de <nombre> (ej: 0008500)
    clave_penalara VARCHAR(50) NOT NULL UNIQUE, -- Extraído de <claveDeExportacion> (ej: 85)
    abreviatura VARCHAR(50) NOT NULL,           -- Extraído de <abreviatura> (ej: 3A)
    curso_id_externo VARCHAR(50) NULL,          -- Extraído de <cursoPerteneciente> (ej: 1873)
    
    -- Datos descriptivos
    nombre_completo VARCHAR(255) NOT NULL,      -- Extraído de <nombreCompleto> (ej: 3A - 3º ESO)
    
    -- Estado y auditoría
    es_activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_grupo_curso (curso_id_externo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;