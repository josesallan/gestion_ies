# Gestión IES — Versión 2 (v2)

Sistema de gestión escolar relacional para IES. Proyecto reescrito desde cero (Greenfield) enfocado en una arquitectura limpia, datos normalizados mediante IDs relacionales y soporte multicurso.

## 🚀 Características Principales (v2)

- **Base de datos normalizada (3FN):** MySQL / MariaDB con codificación `utf8mb4_unicode_ci`. Arquitectura atómica de 10 tablas relacionales desplegadas.
- **Asimilación ETL Avanzada:** Importación y parseo transaccional desde exportaciones XML de Peñalara (GHC) mediante PHP 8 y PDO (`beginTransaction` / `commit`). Integración completa y auditada de **Niveles 0, 1 y 2** (Departamentos, Aulas, Conjuntos, Profesores, Materias, Cursos, Grupos y Carga Lectiva).
- **Parser Atómico de Nombres:** Normalización automatizada de cadenas complejas a campos separados (`nombre`, `apellidos`, `nombre_completo`) con conversión UTF-8 limpia.
- **Trazabilidad y Auditoría:** Control de versiones en Git, documentación de relaciones/cardinalidades con diagrama ER en Mermaid y registro diario de desarrollo.

## 🛠️ Tecnologías

- **Base de datos:** MySQL / MariaDB (phpMyAdmin)
- **Entorno:** Linux (Ubuntu / Linux Mint) / VS Code
- **Backend & Scripting:** PHP 8.x (`SimpleXML` / `PDO` / `mbstring`)
- **Control de versiones:** Git / GitHub

## 📁 Estructura del Proyecto

```text
gestion_ies/
├── Horario.para.programa.gestion.xml   # Exportación XML de Peñalara GHC
├── database/                           # Esquemas SQL, migraciones y arquitectura relacional
│   ├── 01_profesores.sql               # Estructura atómica refactorizada
│   ├── 02_materias.sql
│   ├── 03_grupos.sql
│   ├── 04_departamentos.sql
│   ├── 05_aulas.sql
│   ├── 06_tramos_horarios.sql
│   ├── 07_conjuntos_aulas.sql
│   ├── 08_tareas.sql
│   ├── 09_cursos.sql                   # Incluye pivote curso_materia con FKs reales
│   ├── 10_horarios.sql
│   └── RELACIONES_Y_CARDINALIDADES.md  # Mapa de cardinalidades, diagrama ER y auditoría SQL
├── scripts/                            # Scripts de importación y procesamiento (ETL)
│   └── import_penalara_xml.php         # Motor ETL por niveles (v2.2)
├── DIARIO.md                           # Registro diario de avances del proyecto
└── README.md                           # Documentación general del repositorio