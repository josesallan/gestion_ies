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

### [Paso 5] — Creación de Esquemas para Departamentos y Aulas (v2)

- Mapeadas las etiquetas XML de Peñalara para departamentos (`<nombre>`, `<nombreCompleto>`, `<claveDeExportacion>`) y aulas (`<nombre>`, `<abreviatura>`, `<descripcion>`, `<claveDeExportacion>`).
- Creadas las tablas `departamentos` y `aulas` en MySQL (`grandeco_gestion_ies`).
- Creados los archivos de migración `database/04_departamentos.sql` y `database/05_aulas.sql`.

### [Paso 6] — Creación del Esquema de Tramos Horarios (v2)

- Mapeada la estructura `<marcosDeHorario>` de Peñalara (`<submarco>`, `<dia>`, `<indice>`, `<horaEntrada>`, `<horaSalida>`, `<Tipo>`, `<clavX>`).
- Creada la tabla `tramos_horarios` en MySQL (`grandeco_gestion_ies`).
- Creado el archivo de migración `database/06_tramos_horarios.sql`.

### [Paso 7] — Esquema de Conjuntos y Agrupaciones de Aulas (v2)

- Mapeada la estructura `<conjuntoDeAulas>` de Peñalara (`<general>` y `<otroConjunto nombre="...">`).
- Diseñadas las tablas `conjuntos_aulas` y la tabla pivote `aula_conjunto` (relación M:N) con claves foráneas.
- Creado el archivo de migración `database/07_conjuntos_aulas.sql`.

### [Paso 8] — Esquemas de Tareas y Cursos (v2)

- Mapeadas las tareas complementarias (`<tareas>`) y los cursos con asignación de materias y número de sesiones (`<cursos>`).
- Creadas las tablas `tareas`, `cursos` y la tabla pivote `curso_materia`.
- Creados los archivos de migración `database/08_tareas.sql` y `database/09_cursos.sql`.
