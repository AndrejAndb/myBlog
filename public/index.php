<?php
define('APPLICATION_ENV', 'devel');
// Определяем Путь к Zend Framework и прочим библиотекам если нужно
defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', '../../zf2/library');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(LIBRARY_PATH),
    get_include_path(),
)));

// Инстанциируем наш загрузчик
require_once '../Bootstrap.php';
$bootstrap = new Application\Bootstrap();

// Инстанциируем прототип Zend MVC
$app = new Zend\Mvc\Application();

// Настраиваем Application в нашем загрузчике
$bootstrap->bootstrap($app);

// Исполняем Application
$response = $app->run();

// Отсылаем результат
$response->send();