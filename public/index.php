<?php
define('ROOT_PATH', dirname(__DIR__));
define('REQUEST_TIME', microtime());

session_start();

if (!array_key_exists('csrftoken', $_SESSION)) {
    $_SESSION["csrftoken"] = bin2hex(random_bytes(32));
}

require_once ROOT_PATH . "/models/db.php";
require_once ROOT_PATH . "/vendor/autoload.php";
require_once ROOT_PATH . "/models/usermodel.php";
require_once ROOT_PATH . "/models/helpers.php";
require_once ROOT_PATH . "/models/settings.php";

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$segments = explode('/', trim($path, '/'));
$segments = array_filter($segments);
$segments = array_values($segments);

$controller = array_shift($segments);
$controller = !empty($controller) ? $controller : 'home';
$controller = strtolower($controller);

$action = array_shift($segments);
$action = !empty($action) ? $action : 'Main';

if ($path === "/index.php") {
    die("Yeehaw!");
}

$controller_location = ROOT_PATH . "/controllers/$controller.controller.php";

if (!file_exists($controller_location)) {
    http_response_code(404);
    require_once "error/404.html";
}

require_once $controller_location;
$controller_class_name = ucwords($controller) . "Controller";
$controller_class_name = preg_replace('/[^a-zA-Z0-9_]/s', '_', $controller_class_name);
$action = preg_replace('/[^a-zA-Z0-9_ -]/s', '_', $action);

if (!class_exists($controller_class_name)) {
    http_response_code(404);
    require_once "error/404.html";
    exit;
}

$controller_instance = new $controller_class_name;
call_user_func_array([$controller_instance, $action], $segments);
?>
