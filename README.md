# Gestión IES — Versión 2 (v2)

Sistema de gestión escolar relacional para IES. Proyecto reescrito desde cero (Greenfield) enfocado en una arquitectura limpia, datos normalizados mediante IDs relacionales y soporte multicurso.

## 🚀 Características Principales (v2)

- **Base de datos normalizada (3FN):** MySQL / MariaDB con codificación `utf8mb4_unicode_ci`. Arquitectura atómica de 10 tablas relacionales totalmente desplegadas y refactorizadas.
- **Asimilación ETL Avanzada:** Importación y parseo atómico desde exportaciones XML de Peñalara (GHC) mediante PHP CLI y PDO. Integración completa de Niveles 0, 1 y 2 (Departamentos, Aulas, Conjuntos, Profesores y Materias).
- **Parser Atómico de Nombres:** Normalización automatizada de cadenas complejas a campos separados (`nombre`, `apellidos`, `nombre_completo`) con soporte multi-caracter.
- **Trazabilidad y Auditoría:** Control de versiones en Git, documentación de relaciones/cardinalidades y registro diario de desarrollo.

## 🛠️ Tecnologías

- **Base de datos:** MySQL / MariaDB (phpMyAdmin)
- **Entorno local:** Linux (Ubuntu/Debian) / VS Code
- **Backend & Scripting:** PHP 8.x CLI (Módulos `SimpleXML` / `PDO`)
- **Control de versiones:** Git / GitHub

## 📁 Estructura del Proyecto

```text
gestion_ies/
├── Horario.para.programa.geston.xml  # Exportación XML de Peñalara GHC
├── database/                         # Esquemas SQL, migraciones y arquitectura relacional
│   ├── 01_profesores.sql             # (Estructura atómica refactorizada)
│   ├── ...
│   ├── 10_horarios.sql
│   └── RELACIONES_Y_CARDINALIDADES.md # Mapa de cardinalidades y consultas SQL de auditoría
├── scripts/                          # Scripts de importación y procesamiento (ETL)
│   └── import_penalara_xml.php      # Engine ETL por niveles (beginTransaction/commit)
├── DIARIO.md                         # Registro diario de avances del proyecto
└── README.md                         # Documentación general del repositorio