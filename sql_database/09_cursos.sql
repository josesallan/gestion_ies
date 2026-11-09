-- Estructura de cursos y su carga lectiva por materia (v2)
-- Adaptada a la etiqueta <cursos> de Peñalara / GHC

CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Mapeo con Peñalara / SIGAD
    codigo_sigad VARCHAR(50) NULL UNIQUE,       -- Extraído de <nombre> (ej: 1875)
    clave_penalara VARCHAR(50) NULL UNIQUE,     -- Extraído de <claveDeExportacion> (ej: 1875)
    
    -- Datos descriptivos
    nombre_completo VARCHAR(255) NOT NULL,      -- Extraído de <nombreCompleto> (ej: 1-ESO-FGC)
    
    -- Estado y auditoría
    es_activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación de materias asignadas a cada curso con su número de sesiones
CREATE TABLE IF NOT EXISTS curso_materia (
    curso_id INT NOT NULL,
    materia_clave_penalara VARCHAR(50) NOT NULL, -- Clave externa que enlaza con <materia>
    num_sesiones DECIMAL(4,2) DEFAULT 0.00,       -- Extraído de numSesiones (ej: 3.00)
    PRIMARY KEY (curso_id, materia_clave_penalara),
    CONSTRAINT fk_cm_curso FOREIGN KEY (curso_id) 
        REFERENCES cursos(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;