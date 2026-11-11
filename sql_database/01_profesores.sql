-- ========================================================
-- TABLA: profesores (Nivel 1 - Limpia y Atómica)
-- ========================================================
DROP TABLE IF EXISTS profesores;

CREATE TABLE profesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave_penalara VARCHAR(20) NOT NULL UNIQUE,
    dni VARCHAR(15) NULL,
    abreviatura VARCHAR(10) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    email VARCHAR(100) NULL,
    departamento_id_externo VARCHAR(20) NULL,
    es_activo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_prof_dept (departamento_id_externo),
    INDEX idx_prof_penalara (clave_penalara)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;