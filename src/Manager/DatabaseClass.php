<?php

namespace App\Manager;

use PDO;
use Exception;
use Laminas\Config\Config;

final class DatabaseClass
{
    private PDO $db;

    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    public function __construct(Config $config) {
        try {
            $db = $config->get('db');
            $this->db = new PDO($db['path'], $db['login'], $db['pass']);
        } catch (Exception $e) {
            die('[DatabaseClass] new PDO error: ' . $e->getMessage());
        }
    }

    public function getDb(): PDO {
        return $this->db;
    }
}