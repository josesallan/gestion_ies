# Diario de Desarrollo — Gestión v2

## Visión del Proyecto

- **Objetivo:** Construir una plataforma limpia desde cero (v2).
- **Arquitectura:** IDs numéricos únicos y soporte multicurso (`curso_id`).
- **Estrategia:** Desarrollo en paralelo sin riesgo para la versión actual.

---

## Registro de Avances

### [Paso 1] — Inicialización del Proyecto

- Creada la estructura base de carpetas para la versión 2.
- Diseñado el diario de a bordo para documentar los cambios.
- Creo una nueva base de datos con el nombre grandeco_gestion_ies y usuario grandeco_gestion

### [Paso 2] — Creación del Esquema de Profesores (v2)

- Analizada la estructura XML real de Peñalara (`<nombre>`, `<claveDeExportacion>`, `<abreviatura>`).
- Creada e inicializada la tabla relacional `profesores` en MySQL (`grandeco_gestion_ies`).
- Creado el archivo de migración `database/01_profesores.sql` para el control de versiones.

### [Paso 3] — Creación del Esquema de Materias (v2)

- Mapeados los campos del XML de Peñalara (`<nombre>`, `<abreviatura>`, `<nombreCompleto>`, `<departamento>`).
- Creada la tabla `materias` en MySQL (`grandeco_gestion_ies`).
- Creado el archivo de migración `database/02_materias.sql`.

### [Paso 4] — Creación del Esquema de Grupos (v2)

- Mapeados los campos del XML de Peñalara (`<nombre>`, `<abreviatura>`, `<nombreCompleto>`, `<cursoPerteneciente>`, `<claveDeExportacion>`).
- Creada la tabla `grupos` en MySQL (`grandeco_gestion_ies`).
- Creado el archivo de migración `database/03_grupos.sql`.
