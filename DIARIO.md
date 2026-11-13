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
- Creada una nueva base de datos con el nombre `grandeco_gestion_ies` y usuario `grandeco_gestion`.

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
- Diseñadas las tablas `conjuntos_aulas` y la tabla pivote `aula_conjunto` (relación N:M) con claves foráneas.
- Creado el archivo de migración `database/07_conjuntos_aulas.sql`.

### [Paso 8] — Esquemas de Tareas y Cursos (v2)

- Mapeadas las tareas complementarias (`<tareas>`) y los cursos con asignación de materias y número de sesiones (`<cursos>`).
- Creadas las tablas `tareas`, `cursos` y la tabla pivote `curso_materia`.
- Creados los archivos de migración `database/08_tareas.sql` y `database/09_cursos.sql`.

### [Paso 9] — Creación del Esquema de Horarios (v2)

- Analizados los bloques del XML de Peñalara: `<sesionesLectivas>`, `<reuniones>`, `<guardias>` y el nodo maestro temporal `<horario>`.
- Diseñada la tabla principal `horarios` como contenedor universal de sesiones lectivas y horas complementarias (aprovechando campos nulos `NULL`).
- Diseñada la tabla pivote `horario_grupo` (relación N:M) con clave primaria compuesta (`horario_id`, `grupo_id`) para gestionar desdobles y sesiones con múltiples grupos de forma robusta.
- Resolución de conflictos de restricciones de claves foráneas en MySQL e implementación de timestamps de auditoría (`created_at`, `updated_at`).
- Creado el archivo de migración `database/10_horarios.sql`. Esquema relacional de la base de datos 100% completado (10 tablas en total).

### [Paso 10] — Consolidación de Niveles 0 y 1 en el ETL y Refactorización Atómica

- **Corrección de Colisiones en Importación:** Identificado y resuelto el fallo de sobreescritura `ON DUPLICATE KEY UPDATE` en profesores.
- **Refactorización Atómica de `profesores` (3FN):** Rediseñada la tabla para separar de forma limpia `nombre`, `apellidos`, `nombre_completo` e incorporar la clave foránea `departamento_id_externo`.
- **Parser Inteligente de Nombres (PHP):** Implementado el helper `parsearNombreProfesor()` para desestructurar y limpiar automáticamente `"APELLIDOS, NOMBRE"` con manejo correcto de codificación UTF-8.
- **Auditoría e Integridad Confirmada (Niveles 0 y 1):**
  - **Departamentos:** 15 registros importados y vinculados.
  - **Conjuntos de Aulas:** 9 conjuntos creados.
  - **Profesores:** 85 docentes parseados atómicamente y asociados a sus departamentos (`1:N`).
  - **Materias:** 214 materias asignadas a sus departamentos (`1:N`).
  - **Aulas:** 49 aulas importadas y mapeadas en la tabla pivote `aula_conjunto` (`N:M`, 99 vínculos).

### [Paso 11] — Completado Nivel 2 ETL (Cursos, Grupos y Carga Lectiva `curso_materia`)

- **Refinamiento de Esquema SQL (3FN):**
  - Actualizado `database/03_grupos.sql` añadiendo índice sobre `curso_id_externo` (`1:N` hacia `cursos`).
  - Actualizado `database/09_cursos.sql` estableciendo claves foráneas reales (`fk_cm_curso`, `fk_cm_materia`) en `curso_materia` hacia `cursos(id)` y `materias(id)`.
- **Ampliación del Motor ETL (`scripts/import_penalara_xml.php` v2.2):**
  - Importados y auditados **10 cursos**, **214 asignaciones de carga lectiva** en `curso_materia` (cuadre 100 % con las materias importadas) y **37 grupos**.
  - Validación mediante consultas SQL de auditoría confirmando 0 registros huérfanos.
- **Diseño Funcional de Importación en Producción:**
  - Definido el flujo para la futura interfaz web: asistente en 3 pasos (*Dry-Run* / simulación previa con alertas bloqueantes ➔ ejecución transaccional por bloques ➔ informe post-importación).
- **Documentación y Diagrama ER:** Actualizado `database/RELACIONES_Y_CARDINALIDADES.md` con el diagrama Entidad-Relación en Mermaid de las 9 tablas activas.

---

## 🔮 Próximos Pasos (Para la Siguiente Sesión)

1. **Importación de Nivel 3 (Tramos Horarios y Sesiones):**
   - Revisar `database/06_tramos_horarios.sql`, `database/08_tareas.sql` y `database/10_horarios.sql`.
   - Parsear `<marcosDeHorario>`, `<tareas>`, `<sesionesLectivas>`, `<reuniones>`, `<guardias>` y la parrilla `<horario>` en `scripts/import_penalara_xml.php`.
   - Poblar `tramos_horarios`, `tareas`, `horarios` y la tabla pivote `horario_grupo`.
2. **Capa de Aplicación Web:**
   - Encapsular la lógica ETL en clases de servicio PHP reutilizables para alimentar el asistente de importación paso a paso.