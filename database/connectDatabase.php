<?php

class connectDatabase
{
    private static $connection;

    private function __construct(){}
    private function __clone(){}
    public function __wakeup(){}

    public static function getConnection(String $host, String $db, String $username, String $password): PDO | String
    {
        try
        {
            if (empty(self::$connection)) {
                self::$connection = new PDO("mysql:$host=localhost;dbname=$db", $username, $password);
            }

            return self::$connection;
        }
        catch(PDOException $e)
        {
            return $e->getMessage();
        }
    }
}