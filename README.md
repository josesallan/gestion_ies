# Gestión IES — Versión 2 (v2)

Sistema de gestión escolar relacional para IES. Proyecto reescrito desde cero (Greenfield) enfocado en una arquitectura limpia, datos normalizados mediante IDs relacionales y soporte multicurso.

## 🚀 Características Principales (v2)

- **Base de datos normalizada:** MySQL con codificación `utf8mb4_unicode_ci` para soporte completo de caracteres y tildes.
- **Asimilación ETL:** Importación directa desde exportaciones XML de Peñalara (GHC) y SIGAD.
- **Trazabilidad:** Control de versiones en Git y documentación progresiva de desarrollo.

## 📁 Estructura del Proyecto

```text
gestion_ies/
├── database/            # Esquemas SQL y migraciones
│   └── 01_profesores.sql
├── DIARIO.md            # Registro diario de avances del proyecto
└── README.md            # Documentación general
```
