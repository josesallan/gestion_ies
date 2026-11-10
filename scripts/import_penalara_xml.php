<?php
// scripts/import_penalara_xml.php

// 1. Definir la ruta al archivo XML (Ajusta la ruta según dónde tengas el XML)
// Carga el XML desde la raíz del proyecto (un nivel por encima de /scripts/)
$ruta_xml = __DIR__ . '/../Horario.para.programa.gestion.xml'; 

echo "Iniciando proceso ETL - Fase 1: Lectura de XML\n";
echo "------------------------------------------------\n";

if (!file_exists($ruta_xml)) {
    die("❌ Error: No se encuentra el archivo XML en la ruta: $ruta_xml\n");
}

// 2. Cargar el XML en memoria
$xml = simplexml_load_file($ruta_xml);

if ($xml === false) {
    die("❌ Error: No se pudo parsear el archivo XML. Comprueba que el formato sea correcto.\n");
}

echo "✅ Archivo XML cargado correctamente en memoria.\n\n";

// 3. Crear los "Diccionarios" (Arrays en memoria)
$diccionario_profesores = [];
$diccionario_materias = [];
$diccionario_grupos = [];
$diccionario_aulas = [];

// --- A. Leer Profesores ---
if (isset($xml->profesores->profesor)) {
    foreach ($xml->profesores->profesor as $nodo) {
        $clave_exportacion = (string)$nodo->claveDeExportacion;
        $dni = (string)$nodo->nombre; // En el XML el DNI viene en la etiqueta <nombre>
        $nombre_completo = (string)$nodo->nombreCompleto;
        
        $diccionario_profesores[$clave_exportacion] = [
            'dni' => $dni,
            'nombre' => $nombre_completo
        ];
    }
}

// --- B. Leer Materias ---
if (isset($xml->materias->materia)) {
    foreach ($xml->materias->materia as $nodo) {
        $clave_exportacion = (string)$nodo->claveDeExportacion;
        $codigo_sigad = (string)$nodo->nombre; // En el XML el código viene en <nombre>
        
        $diccionario_materias[$clave_exportacion] = $codigo_sigad;
    }
}

// --- C. Leer Grupos ---
if (isset($xml->grupos->grupo)) {
    foreach ($xml->grupos->grupo as $nodo) {
        $clave_exportacion = (string)$nodo->claveDeExportacion;
        $abreviatura = (string)$nodo->abreviatura;
        
        $diccionario_grupos[$clave_exportacion] = $abreviatura;
    }
}

// --- D. Leer Aulas ---
if (isset($xml->aulas->aula)) {
    foreach ($xml->aulas->aula as $nodo) {
        $clave_exportacion = (string)$nodo->claveDeExportacion;
        $abreviatura = (string)$nodo->abreviatura;
        
        $diccionario_aulas[$clave_exportacion] = $abreviatura;
    }
}

// 4. Imprimir resultados por pantalla para verificar
echo "📊 RESUMEN DE DATOS EXTRAÍDOS (DICCIONARIOS):\n";
echo "- Profesores encontrados: " . count($diccionario_profesores) . "\n";
echo "- Materias encontradas: " . count($diccionario_materias) . "\n";
echo "- Grupos encontrados: " . count($diccionario_grupos) . "\n";
echo "- Aulas encontradas: " . count($diccionario_aulas) . "\n";
echo "------------------------------------------------\n";

// Si quieres ver cómo queda un diccionario por dentro, descomenta la siguiente línea:
// print_r(array_slice($diccionario_profesores, 0, 3, true)); // Muestra los 3 primeros profesores
?>