<?php

namespace app;

use PDO;
use app\models\Log;





class Database
{



    public \PDO $pdo;
    public static Database $db;

    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "performcb_task";

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=$this->servername;port=3306", $this->username, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $databaseSql = "CREATE DATABASE IF NOT EXISTS $this->database";
        $this->pdo->exec($databaseSql);

        $this->pdo = new PDO("mysql:host=$this->servername;port=3306;dbname=$this->database", $this->username, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tableSql = "CREATE TABLE IF NOT EXISTS Logs (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(512) NOT NULL,
            logPath VARCHAR(2048),
            logType VARCHAR(2048) NOT NULL,
            size INT(11) NOT NULL,
            upload_time DATETIME NOT NULL
        )";
        $this->pdo->exec($tableSql);

        self::$db = $this;
    }

    public function getLogs()
    {

        $statement = $this->pdo->prepare('SELECT name, size, upload_time FROM Logs ORDER BY upload_time DESC');

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogByName($name)
    {
        $statement = $this->pdo->prepare('SELECT name, upload_time, logPath, logType FROM Logs WHERE name = :name');
        $statement->bindValue(':name', $name);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function createLog(Log $log)
    {
        $statement = $this->pdo->prepare("INSERT INTO Logs (name, logPath, logType, size, upload_time)
        VALUES (:name, :logPath, :logType, :size, :date)");
        $statement->bindValue(':name', $log->name);
        $statement->bindValue(':logPath', $log->logPath);
        $statement->bindValue(':logType', $log->logType);
        $statement->bindValue(':size', $log->size);
        $statement->bindValue(':date', date('Y-m-d H:i:s'));
        $statement->execute();
    }

    public function deleteLog($name)
    {
        $statement = $this->pdo->prepare('DELETE FROM Logs WHERE name = :name');
        $statement->bindValue(':name', $name);
        $statement->execute();
    }
}
