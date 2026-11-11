<?php
// scripts/import_penalara_xml.php

require_once __DIR__ . '/../src/Database/Database.php';

use GestionIES\Database\Database;

/**
 * Normaliza y separa la cadena nombreCompleto de Peñalara ("APELLIDOS, NOMBRE")
 * en partes atómicas y formateadas.
 */
function parsearNombreProfesor(string $cadenaCompleta): array {
    $cadena = mb_convert_encoding(trim($cadenaCompleta), 'UTF-8', 'UTF-8, ISO-8859-1');
    $nombre = '';
    $apellidos = '';

    if (str_contains($cadena, ',')) {
        $partes = explode(',', $cadena, 2);
        $apellidos = trim($partes[0]);
        $nombre = trim($partes[1]);
    } else {
        $partes = preg_split('/\s+/', $cadena);
        if (count($partes) > 1) {
            $nombre = array_pop($partes);
            $apellidos = implode(' ', $partes);
        } else {
            $nombre = $cadena;
            $apellidos = '';
        }
    }

    $nombre = mb_convert_case($nombre, MB_CASE_TITLE, "UTF-8");
    $apellidos = mb_convert_case($apellidos, MB_CASE_TITLE, "UTF-8");
    $nombreCompletoFormateado = $apellidos ? "{$apellidos}, {$nombre}" : $nombre;

    return [
        'nombre'          => $nombre,
        'apellidos'       => $apellidos,
        'nombre_completo' => $nombreCompletoFormateado
    ];
}

$ruta_xml = __DIR__ . '/../Horario.para.programa.gestion.xml';

echo "🚀 INICIANDO ETL: MIGRACIÓN DE XML A MARIADB (v2.1)\n";
echo "------------------------------------------------\n";

if (!file_exists($ruta_xml)) {
    die("❌ Error: No se encuentra el archivo XML en: {$ruta_xml}\n");
}

try {
    // 1. Obtener conexión PDO Singleton
    $pdo = Database::getConnection();
    echo "✅ Conexión PDO a MariaDB establecida.\n";

    // 2. Cargar XML en memoria
    $xml = simplexml_load_file($ruta_xml);
    if ($xml === false) {
        throw new Exception("No se pudo parsear el archivo XML.");
    }
    echo "✅ Archivo XML parseado correctamente.\n\n";

    // 3. Iniciar Transacción SQL
    $pdo->beginTransaction();
    echo "🔒 Transacción SQL iniciada (beginTransaction).\n\n";

    // =========================================================================
    // NIVEL 0: ENTIDADES INDEPENDIENTES (PADRES BASE)
    // =========================================================================

    // --- 1. DEPARTAMENTOS ---
    echo "🏢 [Nivel 0] Procesando Departamentos...\n";
    $stmtDept = $pdo->prepare("
        INSERT INTO departamentos (
            codigo_sigad, 
            clave_penalara, 
            nombre_completo
        ) VALUES (
            :codigo_sigad, 
            :clave_penalara, 
            :nombre_completo
        )
        ON DUPLICATE KEY UPDATE 
            codigo_sigad = VALUES(codigo_sigad),
            nombre_completo = VALUES(nombre_completo)
    ");
    $countDept = 0;
    if (isset($xml->departamentos->departamento)) {
        foreach ($xml->departamentos->departamento as $nodo) {
            $nombreCompleto = (string)$nodo->nombreCompleto;
            $codigoSigad = (string)$nodo->nombre;
            
            $stmtDept->execute([
                ':codigo_sigad'    => $codigoSigad,
                ':clave_penalara'  => (string)$nodo->claveDeExportacion,
                ':nombre_completo' => $nombreCompleto ?: $codigoSigad
            ]);
            $countDept++;
        }
    }
    echo "   ➔ {$countDept} departamentos procesados.\n";

    // --- 2. CONJUNTOS DE AULAS ---
    echo "🏛️ [Nivel 0] Procesando Conjuntos de Aulas...\n";
    $stmtConjunto = $pdo->prepare("
        INSERT INTO conjuntos_aulas (nombre, es_general)
        VALUES (:nombre, :es_general)
        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
    ");
    $countConjuntos = 0;
    if (isset($xml->conjuntoDeAulas)) {
        foreach ($xml->conjuntoDeAulas->children() as $nodoConjunto) {
            $nombreConjunto = (string)$nodoConjunto['nombre'];
            if (empty($nombreConjunto)) {
                $nombreConjunto = $nodoConjunto->getName();
            }

            $esGeneral = ($nodoConjunto->getName() === 'general') ? 1 : 0;

            $stmtConjunto->execute([
                ':nombre'     => $nombreConjunto,
                ':es_general' => $esGeneral
            ]);
            $countConjuntos++;
        }
    }
    echo "   ➔ {$countConjuntos} conjuntos de aulas procesados.\n";

    // =========================================================================
    // NIVEL 1: ENTIDADES CON DEPENDENCIA DE NIVEL 0
    // =========================================================================

    // --- 3. PROFESORES (REFACTORIZADO Y ATÓMICO) ---
    echo "👥 [Nivel 1] Procesando Profesores (Parseo Atómico de Nombres)...\n";
    $stmtProf = $pdo->prepare("
        INSERT INTO profesores (
            clave_penalara, 
            dni, 
            abreviatura, 
            nombre, 
            apellidos, 
            nombre_completo, 
            email, 
            departamento_id_externo
        ) VALUES (
            :clave_penalara, 
            :dni, 
            :abreviatura, 
            :nombre, 
            :apellidos, 
            :nombre_completo, 
            :email, 
            :dept_externo
        )
        ON DUPLICATE KEY UPDATE 
            dni = VALUES(dni),
            abreviatura = VALUES(abreviatura),
            nombre = VALUES(nombre),
            apellidos = VALUES(apellidos),
            nombre_completo = VALUES(nombre_completo),
            email = VALUES(email),
            departamento_id_externo = VALUES(departamento_id_externo)
    ");
    $countProf = 0;
    if (isset($xml->profesores->profesor)) {
        foreach ($xml->profesores->profesor as $nodo) {
            $claveExportacion = trim((string)$nodo->claveDeExportacion);
            if (empty($claveExportacion)) continue;

            $dniVal = trim((string)$nodo->nombre);
            $abrev = trim((string)$nodo->abreviatura);
            $rawNombreCompleto = (string)$nodo->nombreCompleto;
            $deptExterno = trim((string)$nodo->departamento);
            $email = isset($nodo->email) ? trim((string)$nodo->email) : null;

            $datosNombre = parsearNombreProfesor($rawNombreCompleto);

            $stmtProf->execute([
                ':clave_penalara'  => $claveExportacion,
                ':dni'             => $dniVal ?: null,
                ':abreviatura'     => $abrev ?: $claveExportacion,
                ':nombre'          => $datosNombre['nombre'],
                ':apellidos'       => $datosNombre['apellidos'],
                ':nombre_completo' => $datosNombre['nombre_completo'],
                ':email'           => $email ?: null,
                ':dept_externo'    => $deptExterno ?: null
            ]);
            $countProf++;
        }
    }
    echo "   ➔ {$countProf} profesores procesados con datos atómicos.\n";

    // --- 4. MATERIAS ---
    echo "📚 [Nivel 1] Procesando Materias...\n";
    $stmtMat = $pdo->prepare("
        INSERT INTO materias (
            codigo_sigad, 
            clave_penalara, 
            abreviatura, 
            departamento_id_externo, 
            nombre_completo
        ) VALUES (
            :codigo_sigad, 
            :clave_penalara, 
            :abreviatura, 
            :departamento_id_externo, 
            :nombre_completo
        )
        ON DUPLICATE KEY UPDATE 
            codigo_sigad = VALUES(codigo_sigad),
            abreviatura = VALUES(abreviatura),
            departamento_id_externo = VALUES(departamento_id_externo),
            nombre_completo = VALUES(nombre_completo)
    ");
    $countMat = 0;
    if (isset($xml->materias->materia)) {
        foreach ($xml->materias->materia as $nodo) {
            $codigoSigad = (string)$nodo->nombre;
            $abrev = (string)$nodo->abreviatura;
            $nombreCompleto = (string)$nodo->nombreCompleto;
            
            $stmtMat->execute([
                ':codigo_sigad'            => $codigoSigad,
                ':clave_penalara'          => (string)$nodo->claveDeExportacion,
                ':abreviatura'             => $abrev ?: $codigoSigad,
                ':departamento_id_externo' => (string)$nodo->departamento,
                ':nombre_completo'         => $nombreCompleto ?: $abrev ?: $codigoSigad
            ]);
            $countMat++;
        }
    }
    echo "   ➔ {$countMat} materias procesadas.\n";

    // --- 5. AULAS ---
    echo "🏫 [Nivel 1] Procesando Aulas...\n";
    $stmtAula = $pdo->prepare("
        INSERT INTO aulas (
            codigo_sigad, 
            clave_penalara, 
            abreviatura, 
            descripcion
        ) VALUES (
            :codigo_sigad, 
            :clave_penalara, 
            :abreviatura, 
            :descripcion
        )
        ON DUPLICATE KEY UPDATE 
            codigo_sigad = VALUES(codigo_sigad),
            abreviatura = VALUES(abreviatura),
            descripcion = VALUES(descripcion)
    ");
    $countAula = 0;
    if (isset($xml->aulas->aula)) {
        foreach ($xml->aulas->aula as $nodo) {
            $codigoSigad = (string)$nodo->nombre;
            $abrev = (string)$nodo->abreviatura;
            $desc = (string)$nodo->descripcion;
            
            $stmtAula->execute([
                ':codigo_sigad'   => $codigoSigad,
                ':clave_penalara' => (string)$nodo->claveDeExportacion,
                ':abreviatura'    => $abrev ?: $codigoSigad,
                ':descripcion'    => $desc ?: NULL
            ]);
            $countAula++;
        }
    }
    echo "   ➔ {$countAula} aulas procesadas.\n";

    // =========================================================================
    // NIVEL 2: RELACIONES PIVOTE (N:M)
    // =========================================================================

    // --- 6. RELACIÓN PIVOTE: AULA_CONJUNTO ---
    echo "🔗 [Nivel 2] Mapeando Relaciones Aula-Conjunto...\n";

    $stmtGetIdConjunto = $pdo->prepare("
        SELECT id FROM conjuntos_aulas 
        WHERE nombre = :nombre_search 
        LIMIT 1
    ");

    $stmtGetAulaId = $pdo->prepare("
        SELECT id FROM aulas 
        WHERE codigo_sigad = :cod1 OR clave_penalara = :cod2 
        LIMIT 1
    ");

    $stmtPivoteAula = $pdo->prepare("
        INSERT IGNORE INTO aula_conjunto (aula_id, conjunto_id)
        VALUES (:aula_id, :conjunto_id)
    ");

    $countRelaciones = 0;
    if (isset($xml->conjuntoDeAulas)) {
        foreach ($xml->conjuntoDeAulas->children() as $nodoConjunto) {
            $nombreConjunto = (string)$nodoConjunto['nombre'];
            if (empty($nombreConjunto)) {
                $nombreConjunto = $nodoConjunto->getName();
            }

            $stmtGetIdConjunto->execute([':nombre_search' => $nombreConjunto]);
            $conjuntoId = $stmtGetIdConjunto->fetchColumn();

            if ($conjuntoId && isset($nodoConjunto->aula)) {
                foreach ($nodoConjunto->aula as $codigoAulaXml) {
                    $codigoAula = (string)$codigoAulaXml;
                    
                    $stmtGetAulaId->execute([
                        ':cod1' => $codigoAula,
                        ':cod2' => $codigoAula
                    ]);
                    $aulaId = $stmtGetAulaId->fetchColumn();

                    if ($aulaId) {
                        $stmtPivoteAula->execute([
                            ':aula_id'     => $aulaId,
                            ':conjunto_id' => $conjuntoId
                        ]);
                        $countRelaciones++;
                    }
                }
            }
        }
    }
    echo "   ➔ {$countRelaciones} vínculos en aula_conjunto creados.\n";

    // Confirmar transacción
    $pdo->commit();
    echo "\n------------------------------------------------\n";
    echo "🎉 ¡MIGRACIÓN COMPLETADA Y CONFIRMADA (COMMIT) CON ÉXITO!\n";
    echo "------------------------------------------------\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        echo "\n⚠️ Transacción revertida (ROLLBACK).\n";
    }
    echo "\n❌ FALLO EN EL PROCESO ETL: " . $e->getMessage() . "\n";
}