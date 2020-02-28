<?php
/**
 * class for handling database connection.
 */
class DB {
    private static $db=null;
    private $dsn = 'mysql:dbname=project_1;host=db';
    private $user = 'user';
    private $password = 'test';
    private $dbh = null;
    private function __construct() {
        try {
            $this->dbh = new PDO($this->dsn, $this->user, $this->password);
        } catch (PDOException $e) {
            // NOTE IKKE BRUK DETTE I PRODUKSJON
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public static function getDBConnection() {
        if (DB::$db==null) {
            DB::$db = new self();
        }
        return DB::$db->dbh;
    }
}
