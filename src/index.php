<?php
session_start();
ini_set('display_errors', 1);
ini_set('memory_limit', -1);
set_time_limit (0);
define('WEBROOT', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));
define('ROOT', str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']));

require(ROOT . 'Core/Controller.php');
require(ROOT . 'Core/Model.php');
require(ROOT . 'Core/Classes/PHPExcel/IOFactory.php');



$controller = isset($_GET['page']) ? $params = explode('-', $_GET['page']) : $params[0] = "crawler-ListerProduit";

$controller = ucfirst($params[0] . "Controller");

$action = isset($params[1]) ? $params[1] : 'index';
if(is_file("Controllers/" . $controller . ".php")) {
    require('Controllers/' . $controller . '.php');
    $controller = new $controller();
    if (method_exists($controller, $action)) {
        unset($params[0]);
        unset($params[1]);
        call_user_func_array(array($controller, $action), $params);
//$controller->$action();
    } else {
        echo 'erreur 404';
    }
}else{
    header("Location: crawler-ListerProduit");
}
?>