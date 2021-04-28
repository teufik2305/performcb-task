<?php

namespace app;

class Router
{
    public array $getRoutes = [];
    public array $postRoutes = [];
    public array $deleteRoutes = [];
    public Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function get($url, $fn)
    {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$url] = $fn;
    }

    public function delete($url, $fn)
    {
        $this->deleteRoutes[$url] = $fn;
    }

    public function resolve()
    {
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';

        if (strpos($currentUrl, "log") != false) {
            
            $currentUrlArray = explode("/", $currentUrl);
            $currentUrl = "/".$currentUrlArray[1];
        }

        if (strpos($currentUrl, '?') != false) {
            $currentUrl = substr($currentUrl, 0, strpos($currentUrl, '?'));
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? '/';

        if ($method == 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } elseif ($method == 'DELETE') {
            $fn = $this->deleteRoutes[$currentUrl] ?? null;

        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }

        if ($fn) {
            call_user_func($fn, $this);
        } else {
            echo json_encode("Route not found");
        }
    }
}
