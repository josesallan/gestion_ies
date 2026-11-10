-- Script: database/10_horarios.sql
-- Descripción: Tabla principal de sesiones horarias y su relación con múltiples grupos

-- 1. TABLA PRINCIPAL: El evento temporal y físico
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `profesor_id` INT NOT NULL,
  `tramo_horario_id` INT NOT NULL,
  `aula_id` INT DEFAULT NULL,
  `materia_id` INT DEFAULT NULL,
  `tarea_id` INT DEFAULT NULL,
  `actividad_id_externo` VARCHAR(50) DEFAULT NULL COMMENT 'ID de la sesión lectiva en Peñalara GHC para la importación',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_horarios_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_horarios_tramo` FOREIGN KEY (`tramo_horario_id`) REFERENCES `tramos_horarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_horarios_aula` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_horarios_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_horarios_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,

  -- Índices para optimizar las vistas de horarios por profesor o aula
  INDEX `idx_horarios_profesor_tramo` (`profesor_id`, `tramo_horario_id`),
  INDEX `idx_horarios_aula_tramo` (`aula_id`, `tramo_horario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA PIVOTE: Relación Muchos a Muchos (Horario <-> Grupos)
CREATE TABLE IF NOT EXISTS `horario_grupo` (
  `horario_id` INT NOT NULL,
  `grupo_id` INT NOT NULL,
  
  PRIMARY KEY (`horario_id`, `grupo_id`),
  CONSTRAINT `fk_hg_horario` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hg_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  
  -- Índice para optimizar la vista del horario de un grupo concreto
  INDEX `idx_hg_grupo` (`grupo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;