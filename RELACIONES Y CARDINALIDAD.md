---

### 3. `database/RELACIONES_Y_CARDINALIDADES.md`

```markdown
# Modelo Relacional y Cardinalidades (ETL Peñalara XML → MariaDB v2)

Este documento registra la arquitectura de dependencias relacionales, las cardinalidades mapeadas y el flujo de importación en niveles utilizado en el proyecto **Gestión IES v2** para la migración desde el XML de Peñalara (GHC).

---

## 1. Mapa de Entidades y Cardinalidades

| Entidad Origen | Entidad Destino | Tipo Relación | Clave/Mapeo de Unión | Descripción |
| :--- | :--- | :---: | :--- | :--- |
| **Departamentos** | **Profesores** | `1 : N` | `departamentos.clave_penalara = profesores.departamento_id_externo` | Un departamento agrupa a múltiples profesores. Cada profesor pertenece a un único departamento. |
| **Departamentos** | **Materias** | `1 : N` | `departamentos.clave_penalara = materias.departamento_id_externo` | Un departamento coordina múltiples materias. Cada materia tiene un único departamento asignado. |
| **Aulas** | **Conjuntos de Aulas** | `N : M` | Tabla pivote `aula_conjunto` (`aula_id`, `conjunto_id`) | Un conjunto de aulas contiene múltiples aulas físicas, y un aula física puede formar parte de varios conjuntos. |
| **Cursos** | **Grupos** | `1 : N` | `cursos.clave_penalara = grupos.curso_id_externo` | Un curso académico engloba a varios grupos (ej: 1º ESO A, 1º ESO B). |
| **Cursos** | **Materias** | `N : M` | Tabla pivote `curso_materia` (`curso_id`, `materia_id`, `num_sesiones`) | Un curso abarca distintas materias y cada materia puede impartirse en distintos cursos con su respectiva carga lectiva. |

---

## 2. Jerarquía de Importación ETL por Niveles

Para garantizar la integridad referencial y evitar dependencias huérfanas, el proceso ETL (`scripts/import_penalara_xml.php`) sigue una ordenación estricta de ejecución en una única transacción SQL:

```text
NIVEL 0 (Entidades Independientes Totales)
 ├── 1. Departamentos (`departamentos`)
 └── 2. Conjuntos de Aulas (`conjuntos_aulas`)

NIVEL 1 (Entidades con Dependencia Única de Nivel 0 y Parseo Atómico)
 ├── 3. Profesores (`profesores`)  ──> Depende de Departamentos (Parseo Atómico APELLIDOS, NOMBRE)
 ├── 4. Materias (`materias`)      ──> Depende de Departamentos
 └── 5. Aulas (`aulas`)           ──> Entidad base

NIVEL 2 (Tablas Pivote N:M y Estructura Académica)
 ├── 6. Aula_Conjunto (`aula_conjunto`) ──> Relaciona Aulas con Conjuntos
 ├── 7. Cursos (`cursos`)
 ├── 8. Grupos (`grupos`)
 └── 9. Curso_Materia (`curso_materia`)

NIVEL 3 (Horarios y Sesiones Lectivas)
 └── 10. Sesiones / Horarios (`horarios`)
---

## 3. Consultas SQL de Auditoría e Integridad
#3.1. Agrupación de Profesores por Departamento
SQL

SELECT 
    d.nombre_completo AS departamento,
    COUNT(p.id) AS total_profesores,
    GROUP_CONCAT(p.nombre_completo ORDER BY p.nombre_completo SEPARATOR ', ') AS profesores
FROM departamentos d
LEFT JOIN profesores p ON d.clave_penalara = p.departamento_id_externo
GROUP BY d.id, d.nombre_completo
ORDER BY d.nombre_completo;

#3.2. Agrupación de Materias por Departamento
SQL

SELECT 
    d.nombre_completo AS departamento,
    COUNT(m.id) AS total_materias,
    GROUP_CONCAT(m.abreviatura ORDER BY m.abreviatura SEPARATOR ', ') AS materias
FROM departamentos d
LEFT JOIN materias m ON d.clave_penalara = m.departamento_id_externo
GROUP BY d.id, d.nombre_completo
ORDER BY total_materias DESC, d.nombre_completo ASC;

#3.3. Mapeo de Aulas en Conjuntos (Tabla Pivote aula_conjunto)
SQL

SELECT 
    ca.nombre AS conjunto,
    ca.es_general,
    COUNT(ac.aula_id) AS total_aulas,
    GROUP_CONCAT(a.abreviatura ORDER BY a.abreviatura SEPARATOR ', ') AS aulas_incluidas
FROM conjuntos_aulas ca
JOIN aula_conjunto ac ON ca.id = ac.conjunto_id
JOIN aulas a ON a.id = ac.aula_id
GROUP BY ca.id, ca.nombre, ca.es_general
ORDER BY ca.es_general DESC, ca.nombre ASC;

#3.4. Detección de Registros Huérfanos
SQL

-- Profesores o Materias sin departamento válido
SELECT 'Profesor sin depto' AS tipo, id, nombre_completo, departamento_id_externo 
FROM profesores 
WHERE departamento_id_externo IS NULL 
   OR departamento_id_externo NOT IN (SELECT clave_penalara FROM departamentos)

UNION ALL

SELECT 'Materia sin depto' AS tipo, id, nombre_completo, departamento_id_externo 
FROM materias 
WHERE departamento_id_externo IS NULL 
   OR departamento_id_externo NOT IN (SELECT clave_penalara FROM departamentos);