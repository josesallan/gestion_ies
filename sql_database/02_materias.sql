-- Estructura de la tabla materias (v2)
-- Adaptada a la exportación XML de Peñalara / GHC

CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Mapeo con Peñalara / SIGAD
    codigo_sigad VARCHAR(50) NULL UNIQUE,       -- Extraído de <nombre> (ej: 12911)
    clave_penalara VARCHAR(50) NULL UNIQUE,     -- Extraído de <claveDeExportacion> (ej: 12911)
    abreviatura VARCHAR(50) NOT NULL,           -- Extraído de <abreviatura> (ej: GH1ESOD)
    departamento_id_externo VARCHAR(50) NULL,   -- Extraído de <departamento> (ej: 2256)
    
    -- Datos descriptivos
    nombre_completo VARCHAR(255) NOT NULL,      -- Extraído de <nombreCompleto> (ej: Geografía e Historia ( 1º ) - ESO)
    
    -- Estado y auditoría
    es_activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;