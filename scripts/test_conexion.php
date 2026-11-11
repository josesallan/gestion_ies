<?php
// scripts/test_conexion.php

/**
 * --------------------------------------------------------------------------
 * SCRIPT DE PRUEBA DE CONEXIÓN (TEST DE INTEGRACIÓN)
 * --------------------------------------------------------------------------
 * 
 * OBJETIVO:
 * Verificar que el motor de PHP puede cargar la clase Singleton `Database`, 
 * leer el archivo de configuración `config/database.php` y realizar una 
 * consulta de prueba real contra el servidor MySQL.
 */

// 1. CARGA DE DEPENDENCIAS
// Incluimos la definición de la clase Database. Usamos __DIR__ para que la 
// ruta sea relativa al directorio de este script (scripts/), independientemente
// de desde qué carpeta ejecutemos el comando PHP en la terminal.
require_once __DIR__ . '/../src/Database/Database.php';

// Importamos la clase mediante su Namespace para usar 'Database' directamente
use GestionIES\Database\Database;

// 2. BLOQUE CONTROLADO DE EJECUCIÓN (try / catch)
// Todo el proceso de conexión y consulta se envuelve en un try/catch para 
// capturar cualquier error (como credenciales incorrectas, BD inexistente o fallo de red)
// de forma limpia sin romper la ejecución abruptamente.
try {
    echo "Probando conexión a MySQL mediante Singleton...\n";
    
    // 3. OBTENCIÓN DE LA CONEXIÓN (SINGLETON)
    // Solicitamos la instancia de PDO. Si es la primera vez, el Singleton lee 
    // `config/database.php`, abre el socket TCP contra MySQL y devuelve el objeto PDO.
    $pdo = Database::getConnection();
    
    // 4. CONSULTA SQL DE PRUEBA
    // Ejecutamos una consulta rápida del sistema para pedirle a MySQL dos cosas:
    //  - DATABASE(): El nombre de la base de datos a la que nos hemos conectado.
    //  - VERSION(): La versión exacta del motor MySQL / MariaDB.
    $stmt = $pdo->query("SELECT DATABASE() AS bd, VERSION() AS version");
    
    // 5. LECTURA DEL RESULTADO
    // `fetch()` recupera la primera fila de la consulta como un array asociativo 
    // gracias a la opción PDO::FETCH_ASSOC que configuramos en database.php.
    $resultado = $stmt->fetch();

    // 6. INFORME DE ÉXITO POR CONSOLA
    echo "--------------------------------------------------\n";
    echo "✅ CONEXIÓN EXITOSA (Matrícula de Honor)\n";
    echo "  - Base de Datos: " . $resultado['bd'] . "\n";
    echo "  - Versión MySQL: " . $resultado['version'] . "\n";
    echo "--------------------------------------------------\n";

} catch (Exception $e) {
    // Si algo falla en cualquier punto del bloque 'try', la excepción se captura aquí
    // y mostramos un mensaje descriptivo sin exponer datos sensibles.
    echo "❌ FALLO DE CONEXIÓN:\n";
    echo $e->getMessage() . "\n";
}