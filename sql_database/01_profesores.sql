-- Estructura de la tabla profesores (v2)
-- Adaptada a la exportación XML de Peñalara / GHC

CREATE TABLE IF NOT EXISTS profesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Mapeo con Peñalara / SIGAD
    dni VARCHAR(20) NULL UNIQUE,               -- Extraído de <nombre> (ej: 08888045V)
    clave_penalara VARCHAR(50) NULL UNIQUE,     -- Extraído de <claveDeExportacion> (ej: 28326)
    abreviatura_penalara VARCHAR(50) NULL,      -- Extraído de <abreviatura> (ej: SVC)
    departamento_id_externo VARCHAR(50) NULL,   -- Extraído de <departamento> (ej: 2249)
    
    -- Datos de Identidad
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    nombre_completo VARCHAR(255) NOT NULL,      -- Extraído de <nombreCompleto>
    email VARCHAR(150) NOT NULL UNIQUE,         -- Extraído de <email>
    
    -- Formato visual para cuadrantes y menús
    nombre_corto VARCHAR(100) NOT NULL,
    alias_cuadrante VARCHAR(50) NOT NULL,
    
    -- Estado y auditoría
    es_activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;