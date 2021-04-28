<?php

namespace app\controllers;

error_reporting(E_ERROR | E_PARSE);

use app\models\Log;
use app\Router;


class LogController
{

    public function index(Router $router)
    {
        $logs = $router->db->getLogs();
        echo json_encode($logs);
    }

    public function createLog(Router $router)
    {
        $errors = [
            "error_msg" => ""
        ];
        $logData = [
            'name' => '',
            'size' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $logData['name'] = $_POST['name'];
            $logData['logFile'] = $_FILES['file'] ?? null;
            $logData['size'] = (int)$_FILES['file']['size'];

            $log = $router->db->getLogByName($logData['name']);
            if ($log) {
                $errors["error_msg"] = "Log with '" . $log['name'] . "' already exists";
            } else {
                $log = new Log();
                $log->load($logData);
                $errors = $log->save();
            }
        }

        if (empty($errors["error_msg"])) {
            $log = $router->db->getLogByName($logData['name']);
            echo json_encode(array("name" => $log['name'], "upload_time" => $log['upload_time']));
        } else {
            echo json_encode($errors);
        }
    }

    public function getLog(Router $router)
    {
        $errors = [
            "error_msg" => ''
        ];
        $name = $_SERVER['REQUEST_URI'] ?? null;
        $nameArray = explode("/", $name);
        if (count($nameArray) == 3) {
            $name = $nameArray[2];
        }

        $log = $router->db->getLogByName($name);
        if ($log == false) {
            $errors["error_msg"] = "Log with specific name doesn't exist";
        } else {
            $logPath = $log['logPath'];
            $logType = $log['logType'];
        }

        if (empty($errors["error_msg"])) {
            if (!is_dir(__DIR__ . '/../public/temporaryLog')) {
                mkdir(__DIR__ . '/../public/temporaryLog');
            }
            $files = scandir("temporaryLog", 1);
            if (count($files) == 3) {
                unlink("temporaryLog/" . $files[0]);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://localhost/" . $logPath . "." . $logType);
            $fp = fopen("temporaryLog/" . $name . "." . $logType, 'w', true);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            echo json_encode(["success" => "Log is download to temporaryLog directory"]);
        } else {
            echo json_encode($errors);
        }
    }

    public function deleteLog(Router $router)
    {
        $errors = [
            "error_msg" => ''
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

            $name = $_SERVER['REQUEST_URI'] ?? null;
            $nameArray = explode("/", $name);
            $name = $nameArray[2];

            $log = $router->db->getLogByName($name);
            if ($log == false) {
                $errors["error_msg"] = "Log with specific name doesn't exist";
            } {
                $logType = $log['logType'];
            }

            if (empty($errors["error_msg"])) {
                $router->db->deleteLog($name);
                unlink("logs/" . $name . "." . $logType);
                $files = scandir("temporaryLog", 1);
                if (count($files) == 3) {
                    $filename = $files[0];
                }
                if ($filename == $name . "." . $logType) {
                    unlink("temporaryLog/" . $name . "." . $logType);
                }
                echo json_encode(["success" => "Log is deleted"]);
            } else {
                echo json_encode($errors);
            }
        }
    }

    public function aggregateByIp()
    {
        $contents = self::getContnets();

        $filteredContentsArray = self::getFilteredContents($contents);

        $ips = [];
        foreach ($filteredContentsArray as $filteredContent) {
            $helper = explode("-", $filteredContent);
            array_push($ips, trim($helper[0], " "));
        }
        $uniqueIps = array_unique($ips, SORT_STRING);

        $finalArray = [];
        $filteredContents = implode("\n", $filteredContentsArray);
        foreach ($uniqueIps as $uniqueIp) {
            if (!empty($uniqueIp)) {
                $supportArray = [
                    "ip" => '',
                    "cnt" => ''
                ];

                $supportArray["ip"] = $uniqueIp;
                $supportArray["cnt"] = substr_count($filteredContents, $uniqueIp);
                array_push($finalArray, $supportArray);
            }
        }
        echo json_encode($finalArray);
    }

    public static function aggregateByMethod()
    {
        $contents = self::getContnets();

        $filteredContentsArray = self::getFilteredContents($contents);
        $methods = [];
        foreach ($filteredContentsArray as $filteredContent) {
            $helper = explode("\"", $filteredContent);
            $method = explode(" ", $helper[1])[0];
            if (strpos($method, "x") || strpos($method, " ")) {
                $method = "Wierd method at " . substr(explode("[", $filteredContent)[1], 0, 20);
            }
            array_push($methods, $method);
        }
        $uniqueMethods = array_unique($methods, SORT_STRING);
        $finalArray = [];
        $filteredContents = implode("\n", $filteredContentsArray);

        foreach ($uniqueMethods as $uniqueMethod) {
            if (!empty($uniqueMethod)) {
                $supportArray = [
                    "method" => '',
                    "cnt" => ''
                ];

                $supportArray["method"] = $uniqueMethod;
                $supportArray["cnt"] = substr_count($filteredContents, $uniqueMethod);
                array_push($finalArray, $supportArray);
            }
        }

        echo json_encode($finalArray);
    }

    public static function getContnets()
    {
        $errors = [
            "error_msg" => []
        ];

        $files = scandir("temporaryLog", 1);
        if (count($files) == 3) {
            $filename = "temporaryLog/" . $files[0];
        } else {
            array_push($errors["error_msg"], "Download log from server: curl http://localhost/log/[logname]");
            echo json_encode($errors);
            exit;
        }

        if (strpos($filename, "gz") != false) {
            $gzFile = gzopen($filename, "r");
            $contents = gzread($gzFile, 1000000000);
            gzclose($gzFile);
        } elseif (strpos($filename, "txt") != false) {
            $txtFile = fopen($filename, "r");
            $contents = fread($txtFile, 1000000000);
            fclose($txtFile);
        } else {
            array_push($errors["error_msg"], "Log with specific name doesn't exit");
            echo json_encode($errors);
            exit;
        }

        return $contents;
    }

    public static function getFilteredContents($contents)
    {
        $errors = [
            "error_msg" => []
        ];

        if ($_GET["dt_start"]) {
            $dt_start = date_create($_GET['dt_start'])->format("Y-m-d H:i:s");
        }

        if ($_GET["dt_end"]) {
            $dt_end = date_create($_GET['dt_end'])->format("Y-m-d H:i:s");
        }

        $contentsArray = explode("\n", $contents);

        if ($_GET["dt_start"] || $_GET["dt_end"]) {
            $filteredContentsArray = [];
            foreach ($contentsArray as $content) {
                if (!empty($content)) {
                    if (substr(explode("[", $content)[1], 0, 20)) {
                        $stringDate = substr(explode("[", $content)[1], 0, 20);
                    } else {
                        array_push($errors["error_msg"], "Log content doesn't follow structure: IP -- [DATETIME] \"METHOD REQUEST_PATH");
                        echo json_encode($errors);
                        exit;
                    }
                    $date = date_create_from_format("d/M/Y:H:i:s", $stringDate)->format("Y-m-d H:i:s");
                    if ($_GET["dt_start"] && $_GET["dt_end"]) {
                        if ($dt_start <= $date && $dt_end >= $date) {
                            array_push($filteredContentsArray, $content);
                        }
                    } elseif ($_GET["dt_start"]) {
                        if ($dt_start <= $date) {
                            array_push($filteredContentsArray, $content);
                        }
                    } else {
                        if ($dt_end >= $date) {
                            array_push($filteredContentsArray, $content);
                        }
                    }
                }
            }
        } else {
            $filteredContentsArray = $contentsArray;
        }

        return $filteredContentsArray;
    }
}
