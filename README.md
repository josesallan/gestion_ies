# Gestión IES — Versión 2 (v2)

Sistema de gestión escolar relacional para IES. Proyecto reescrito desde cero (Greenfield) enfocado en una arquitectura limpia, datos normalizados mediante IDs relacionales y soporte multicurso.

## 🚀 Características Principales (v2)

- **Base de datos normalizada:** MySQL con codificación `utf8mb4_unicode_ci` para soporte completo de caracteres y tildes. Arquitectura de 10 tablas relacionales totalmente desplegada.
- **Asimilación ETL:** Importación directa desde exportaciones XML de Peñalara (GHC) y SIGAD mediante scripts nativos en PHP CLI. Fase 1 de extracción y diccionarios validada con éxito.
- **Trazabilidad:** Control de versiones en Git y documentación progresiva de desarrollo en el diario de a bordo.

## 🛠️ Tecnologías

- **Base de datos:** MySQL / MariaDB (phpMyAdmin)
- **Entorno local:** Linux (Ubuntu/Debian) / VS Code
- **Backend & Scripting:** PHP 8.x CLI (Módulos `SimpleXML` / `PDO`)
- **Control de versiones:** Git / GitHub

## 📁 Estructura del Proyecto

```text
gestion_ies/
├── Horario.para.programa.geston.xml  # Exportación XML de Peñalara GHC
├── database/                         # Esquemas SQL y migraciones (10 tablas desplegadas)
│   ├── 01_profesores.sql
│   ├── ...
│   └── 10_horarios.sql
├── scripts/                          # Scripts de importación y procesamiento (ETL)
│   └── import_penalara_xml.php
├── DIARIO.md                         # Registro diario de avances del proyecto
└── README.md                         # Documentación general del repositorio
```
