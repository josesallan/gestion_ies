---

### 3. `database/RELACIONES_Y_CARDINALIDADES.md`

*(Asegúrate de guardar este contenido dentro de `database/RELACIONES_Y_CARDINALIDADES.md` y eliminar el archivo suelto `RELACIONES Y CARDINALIDAD.md` de la raíz si aún existe).*

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

## 2. Diagrama Entidad-Relación (Niveles 0, 1 y 2 Implementados)

```mermaid
erDiagram
    DEPARTAMENTOS {
        int id PK
        varchar codigo_sigad
        varchar clave_penalara UK
        varchar nombre_completo
    }

    PROFESORES {
        int id PK
        varchar clave_penalara UK
        varchar dni
        varchar abreviatura
        varchar nombre "Atómico (1FN)"
        varchar apellidos "Atómico (1FN)"
        varchar nombre_completo
        varchar email
        varchar departamento_id_externo FK
        boolean es_activo
    }

    MATERIAS {
        int id PK
        varchar codigo_sigad
        varchar clave_penalara UK
        varchar abreviatura
        varchar nombre_completo
        varchar departamento_id_externo FK
    }

    AULAS {
        int id PK
        varchar codigo_sigad
        varchar clave_penalara UK
        varchar abreviatura
        varchar descripcion
    }

    CONJUNTOS_AULAS {
        int id PK
        varchar nombre UK
        boolean es_general
    }

    AULA_CONJUNTO {
        int aula_id PK, FK
        int conjunto_id PK, FK
    }

    CURSOS {
        int id PK
        varchar codigo_sigad
        varchar clave_penalara UK
        varchar nombre_completo
        boolean es_activo
    }

    GRUPOS {
        int id PK
        varchar codigo_sigad
        varchar clave_penalara UK
        varchar abreviatura
        varchar curso_id_externo FK
        varchar nombre_completo
        boolean es_activo
    }

    CURSO_MATERIA {
        int curso_id PK, FK
        int materia_id PK, FK
        decimal num_sesiones
    }

    %% Relaciones 1:N (Nivel 0 -> Nivel 1)
    DEPARTAMENTOS ||--o{ PROFESORES : "adscribe (1:N)"
    DEPARTAMENTOS ||--o{ MATERIAS : "coordina (1:N)"

    %% Relación N:M (Nivel 1/0 -> Nivel 2 Pivote)
    AULAS ||--o{ AULA_CONJUNTO : "pertenece a"
    CONJUNTOS_AULAS ||--o{ AULA_CONJUNTO : "agrupa"

    %% Relaciones Nivel 2 (Estructura Académica)
    CURSOS ||--o{ GRUPOS : "engloba (1:N)"
    CURSOS ||--o{ CURSO_MATERIA : "define carga"
    MATERIAS ||--o{ CURSO_MATERIA : "impartida en"

    3. Jerarquía de Importación ETL por Niveles

Para garantizar la integridad referencial y evitar dependencias huérfanas, el proceso ETL (scripts/import_penalara_xml.php) sigue una ordenación estricta de ejecución en una única transacción SQL:
Plaintext

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
 ├── 8. Curso_Materia (`curso_materia`) ──> Carga lectiva N:M entre Cursos y Materias
 └── 9. Grupos (`grupos`)               ──> Depende de Cursos (1:N)

NIVEL 3 (Horarios y Sesiones Lectivas)
 └── 10. Tramos Horarios y Sesiones (`tramos_horarios`, `tareas`, `horarios`, `horario_grupo`)

4. Consultas SQL de Auditoría e Integridad
4.1. Agrupación de Profesores por Departamento
SQL

SELECT 
    d.nombre_completo AS departamento,
    COUNT(p.id) AS total_profesores,
    GROUP_CONCAT(p.nombre_completo ORDER BY p.nombre_completo SEPARATOR ', ') AS profesores
FROM departamentos d
LEFT JOIN profesores p ON d.clave_penalara = p.departamento_id_externo
GROUP BY d.id, d.nombre_completo
ORDER BY d.nombre_completo;

4.2. Agrupación de Materias por Departamento
SQL

SELECT 
    d.nombre_completo AS departamento,
    COUNT(m.id) AS total_materias,
    GROUP_CONCAT(m.abreviatura ORDER BY m.abreviatura SEPARATOR ', ') AS materias
FROM departamentos d
LEFT JOIN materias m ON d.clave_penalara = m.departamento_id_externo
GROUP BY d.id, d.nombre_completo
ORDER BY total_materias DESC, d.nombre_completo ASC;

4.3. Mapeo de Aulas en Conjuntos (Tabla Pivote aula_conjunto)
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

4.4. Grupos por Curso (Relación 1:N)
SQL

SELECT 
    c.nombre_completo AS curso,
    COUNT(g.id) AS total_grupos,
    GROUP_CONCAT(g.abreviatura ORDER BY g.abreviatura SEPARATOR ', ') AS grupos
FROM cursos c
LEFT JOIN grupos g ON c.clave_penalara = g.curso_id_externo
GROUP BY c.id, c.nombre_completo
ORDER BY c.nombre_completo;

4.5. Carga Lectiva y Materias por Curso (Tabla Pivote curso_materia)
SQL

SELECT 
    c.nombre_completo AS curso,
    COUNT(cm.materia_id) AS total_materias,
    SUM(cm.num_sesiones) AS suma_sesiones_asignadas,
    GROUP_CONCAT(CONCAT(m.abreviatura, ' (', CAST(cm.num_sesiones AS UNSIGNED), 'h)') ORDER BY m.abreviatura SEPARATOR ', ') AS materias
FROM cursos c
JOIN curso_materia cm ON c.id = cm.curso_id
JOIN materias m ON m.id = cm.materia_id
GROUP BY c.id, c.nombre_completo
ORDER BY c.nombre_completo;

4.6. Detección de Registros Huérfanos (Niveles 1 y 2)
SQL

SELECT 'Profesor sin depto' AS tipo, id, nombre_completo, departamento_id_externo AS ref_externa
FROM profesores 
WHERE departamento_id_externo IS NULL 
   OR departamento_id_externo NOT IN (SELECT clave_penalara FROM departamentos)

UNION ALL

SELECT 'Materia sin depto' AS tipo, id, nombre_completo, departamento_id_externo AS ref_externa
FROM materias 
WHERE departamento_id_externo IS NULL 
   OR departamento_id_externo NOT IN (SELECT clave_penalara FROM departamentos)

UNION ALL

SELECT 'Grupo sin curso' AS tipo, id, nombre_completo, curso_id_externo AS ref_externa
FROM grupos
WHERE curso_id_externo IS NULL 
   OR curso_id_externo NOT IN (SELECT clave_penalara FROM cursos);