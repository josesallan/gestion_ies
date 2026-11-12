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
        varchar clave_penalara UK "Clave enlace Peñalara"
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

    %% Relaciones 1:N (Nivel 0 -> Nivel 1)
    DEPARTAMENTOS ||--o{ PROFESORES : "adscribe (1:N)"
    DEPARTAMENTOS ||--o{ MATERIAS : "coordina (1:N)"

    %% Relación N:M (Nivel 1/0 -> Nivel 2 Pivote)
    AULAS ||--o{ AULA_CONJUNTO : "pertenece a"
    CONJUNTOS_AULAS ||--o{ AULA_CONJUNTO : "agrupa"