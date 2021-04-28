<?php

namespace app\models;

use app\Database;

define('MB', 1048576);

class Log
{
    public ?int $id = null;
    public ?string $name = null;
    public ?int $size = null;
    public ?string $logType = null;
    public ?string $logPath = null;
    public array $logFile;

    public function load($logData)
    {
        $this->name = $logData['name'];
        $this->size = $logData['size'];
        $this->logFile = $logData['logFile'];
        if ($this->logFile['type'] == "text/plain") {
            $this->logType = "txt";
        }
        if ($this->logFile['type'] == "application/gzip" || $this->logFile['type'] == "application/octet-stream") {
            $this->logType = "gz";
        }
    }

    public function save()
    {
        $errors = [
            "error_msg" => []
        ];

        if (!$this->name) {
            array_push($errors["error_msg"], "Missing 'name' POST argument");
        }
        if ($this->size > 100*MB) {
            array_push($errors["error_msg"], "File is > 100Mb");
        }

        if (!($this->logType != "txt" || $this->logType != "gz")) {
            array_push($errors["error_msg"], "The file is not gzipped or txt");
        }

        if (!is_dir(__DIR__ . '/../public/logs')) {
            mkdir(__DIR__ . '/../public/logs');
        }

        if (empty($errors["error_msg"])) {

            if ($this->logFile && $this->logFile['tmp_name']) {
                $this->logPath = 'logs/'. $this->name;

                move_uploaded_file($this->logFile['tmp_name'], __DIR__ . '/../public/' . $this->logPath.".".$this->logType);
            }

            $db = Database::$db;
            $db->createLog($this);
            
        }
        return $errors;
    }
}
