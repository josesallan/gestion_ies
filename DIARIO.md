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

### [Paso 9] — Creación del Esquema de Horarios (v2)

- Analizados los bloques del XML de Peñalara: `<sesionesLectivas>`, `<reuniones>`, `<guardias>` y el nodo maestro temporal `<horario>`.
- Diseñada la tabla principal `horarios` como contenedor universal de sesiones lectivas y horas complementarias (aprovechando campos nulos `NULL`).
- Diseñada la tabla pivote `horario_grupo` (relación M:N) con clave primaria compuesta (`horario_id`, `grupo_id`) para gestionar desdobles y sesiones con múltiples grupos de forma robusta.
- Resolución de conflictos de restricciones de claves foráneas en MySQL e implementación de timestamps de auditoría (`created_at`, `updated_at`).
- Creado el archivo de migración `database/10_horarios.sql`. Esquema relacional de la base de datos 100% completado (10 tablas en total).

### [Paso 10] — Consolidación de Niveles 0, 1 y 2 en el ETL y Refactorización Atómica

- **Corrección de Colisiones en Importación:** Identificado y resuelto el fallo de sobreescritura `ON DUPLICATE KEY UPDATE` en profesores, garantizando la inserción correcta de los 88 profesores.
- **Refactorización Atómica de `profesores` (3FN):** Rediseñada la tabla para separar de forma limpia `nombre`, `apellidos`, `nombre_completo` e incorporar la clave foránea `departamento_id_externo`.
- **Parser Inteligente de Nombres (PHP):** Implementado el helper `parsearNombreProfesor()` para desestructurar y limpiar automáticamente "APELLIDOS, NOMBRE" con manejo correcto de codificación UTF-8.
- **Auditoría e Integridad Confirmada:** 
  - **Departamentos:** 17 registros importados y vinculados.
  - **Conjuntos de Aulas:** 9 conjuntos creados.
  - **Profesores:** 88 docentes parseados atómicamente y asociados a sus departamentos (1:N).
  - **Materias:** 214 materias asignadas a sus departamentos (1:N).
  - **Aulas:** 49 aulas importadas y mapeadas en la tabla pivote `aula_conjunto` (N:M).
- **Documentación de Arquitectura:** Creado `database/RELACIONES_Y_CARDINALIDADES.md` con las consultas SQL de auditoría y jerarquía por niveles.

---

## 🔮 Próximos Pasos (Para la Siguiente Sesión)

1. **Importación de Nivel 2 (Cursos y Grupos):**
   - Revisar la estructura de la tabla `cursos` y `grupos`.
   - Implementar el procesamiento de `<cursos>` y `<grupos>` en el script `scripts/import_penalara_xml.php`.
   - Poblar la tabla pivote `curso_materia` (asignación de materias y número de sesiones por curso).
2. **Importación de Nivel 3 (Sesiones Lectivas y Horarios):**
   - Parsear el bloque de `<sesionesLectivas>`, `<reuniones>` y `<guardias>`.
   - Mapear las asignaciones horarias universales a la tabla `horarios` y su pivote `horario_grupo`.