<?php
// src/Database/Database.php


/**
 * --------------------------------------------------------------------------
 * CONEXIÓN ÚNICA A LA BASE DE DATOS (PATRÓN SINGLETON)
 * --------------------------------------------------------------------------
 * 
 * ¿QUÉ HACE ESTE ARCHIVO?:
 * Es el "guardián" de la conexión con MySQL. Se encarga de abrir la puerta 
 * a la base de datos de forma segura usando las claves de `config/database.php`.
 * 
 * ¿POR QUÉ SE USA ESTA ESTRUCTURA?:
 * En lugar de abrir una conexión nueva cada vez que una parte del programa 
 * necesita consultar datos (lo que saturaría el servidor y lo volvería lento), 
 * este archivo crea UNA SOLA conexión al principio y la reutiliza todo el tiempo.
 * 
 * IDEA GENERAL:
 * Funciona como una ventanilla única: cualquier script que necesite hablar 
 * con la base de datos le pide la conexión a esta clase. Si ya está abierta, 
 * se la entrega al instante sin perder tiempo ni memoria.
 */

namespace GestionIES\Database;

use PDO;
use PDOException;
use Exception;

class Database 
{
    private static ?PDO $instance = null;

    /**
     * Constructor privado para impedir la instanciación directa con 'new'
     */
    private function __construct() {}

    /**
     * Clonar privado para evitar la clonación del objeto Singleton
     */
    private function __clone() {}

    /**
     * Obtiene la instancia única de PDO
     *
     * @param string|null $configPath Ruta opcional al archivo de configuración
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(?string $configPath = null): PDO 
    {
        if (self::$instance === null) {
            // Determinar la ruta por defecto si no se pasa explícitamente
            $path = $configPath ?? __DIR__ . '/../../config/database.php';

            if (!file_exists($path)) {
                throw new Exception("❌ Error Crítico: No se encontró el archivo de configuración en: {$path}");
            }

            $config = require $path;

            $dsn = sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // En producción nunca mostramos $e->getMessage() al usuario final por seguridad,
                // pero lo registramos en logs o lo lanzamos controlado en CLI.
                throw new Exception("❌ Error de conexión a la Base de Datos: " . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }
}