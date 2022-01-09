<?php
namespace Gt;

use Exception;
use PDO;

class DatabaseClass
{
    /**
     * @var Singleton
     * @access private
     * @static
     */
    private static $_instance = null;

    private $db = null;

    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    private function __construct() {
        require("private/databasePrivate.php");
        try {
                $this->db = new PDO($_dbPath, $_dbLogin, $_dbPasswd);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @param void
     * @return Singleton
     */
    public static function getInstance(): DatabaseClass {

        if(is_null(self::$_instance)) {
            self::$_instance = new DatabaseClass();
        }

        return self::$_instance;
    }

    public function getDb(): PDO {
        return $this->db;
    }
}