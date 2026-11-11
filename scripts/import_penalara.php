<?php
// scripts/import_penalara_xml.php

require_once __DIR__ . '/../src/Database/Database.php';

use GestionIES\Database\Database;

$ruta_xml = __DIR__ . '/../Horario.para.programa.gestion.xml';

echo "🚀 INICIANDO ETL: MIGRACIÓN DE XML A MARIADB (v2)\n";
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

    // --- A. PROFESORES ---
    echo "👥 Procesando Profesores...\n";
    $stmtProf = $pdo->prepare("
        INSERT INTO profesores (nombre, abreviatura, clave_exportacion)
        VALUES (:nombre, :abreviatura, :clave)
        ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre),
            abreviatura = VALUES(abreviatura)
    ");
    $countProf = 0;
    if (isset($xml->profesores->profesor)) {
        foreach ($xml->profesores->profesor as $nodo) {
            $stmtProf->execute([
                ':nombre'      => (string)$nodo->nombre,
                ':abreviatura' => (string)$nodo->abreviatura,
                ':clave'       => (string)$nodo->claveDeExportacion
            ]);
            $countProf++;
        }
    }
    echo "   ➔ {$countProf} profesores procesados.\n";

    // --- B. DEPARTAMENTOS ---
    echo "🏢 Procesando Departamentos...\n";
    $stmtDept = $pdo->prepare("
        INSERT INTO departamentos (nombre, nombre_completo, clave_exportacion)
        VALUES (:nombre, :nombre_completo, :clave)
        ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre),
            nombre_completo = VALUES(nombre_completo)
    ");
    $countDept = 0;
    if (isset($xml->departamentos->departamento)) {
        foreach ($xml->departamentos->departamento as $nodo) {
            $stmtDept->execute([
                ':nombre'          => (string)$nodo->nombre,
                ':nombre_completo' => (string)$nodo->nombreCompleto,
                ':clave'           => (string)$nodo->claveDeExportacion
            ]);
            $countDept++;
        }
    }
    echo "   ➔ {$countDept} departamentos procesados.\n";

    // --- C. MATERIAS ---
    echo "📚 Procesando Materias...\n";
    $stmtMat = $pdo->prepare("
        INSERT INTO materias (nombre, abreviatura, nombre_completo)
        VALUES (:nombre, :abreviatura, :nombre_completo)
        ON DUPLICATE KEY UPDATE 
            abreviatura = VALUES(abreviatura),
            nombre_completo = VALUES(nombre_completo)
    ");
    $countMat = 0;
    if (isset($xml->materias->materia)) {
        foreach ($xml->materias->materia as $nodo) {
            $stmtMat->execute([
                ':nombre'          => (string)$nodo->nombre,
                ':abreviatura'     => (string)$nodo->abreviatura,
                ':nombre_completo' => (string)$nodo->nombreCompleto
            ]);
            $countMat++;
        }
    }
    echo "   ➔ {$countMat} materias procesadas.\n";

    // --- D. AULAS ---
    echo "🏫 Procesando Aulas...\n";
    $stmtAula = $pdo->prepare("
        INSERT INTO aulas (nombre, abreviatura, descripcion, clave_exportacion)
        VALUES (:nombre, :abreviatura, :descripcion, :clave)
        ON DUPLICATE KEY UPDATE 
            abreviatura = VALUES(abreviatura),
            descripcion = VALUES(descripcion)
    ");
    $countAula = 0;
    if (isset($xml->aulas->aula)) {
        foreach ($xml->aulas->aula as $nodo) {
            $stmtAula->execute([
                ':nombre'      => (string)$nodo->nombre,
                ':abreviatura' => (string)$nodo->abreviatura,
                ':descripcion' => (string)$nodo->descripcion,
                ':clave'       => (string)$nodo->claveDeExportacion
            ]);
            $countAula++;
        }
    }
    echo "   ➔ {$countAula} aulas procesadas.\n";

    // 4. Confirmar la transacción
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