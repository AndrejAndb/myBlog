<?php
namespace Application;
use Zend\Mvc\AppContext as AppContext,
    Zend\Mvc\Bootstrap as ZendBootstrap,
    Zend\Loader\AutoloaderFactory;

define('APPLICATION_PATH', '/home/andb/projects/acmf2');

require_once 'Zend/Mvc/Bootstrapper.php';
require_once 'Zend/Mvc/Bootstrap.php';

class Bootstrap extends ZendBootstrap
{

    /**
     *
     * @var \Zend\Module\Manager 
     */
    protected $moduleManager = null;

    public function __construct() {
	$this->initStandartAutoloader(); // Инициализируем стандартный автозагрузчик
	$this->initModuleAutoloader(); // Инициализируем автозагрузчик модулей (который загружает файлы вида /Module.php)
        $appConfig = $this->getAppConfig();
        $moduleManager = new \Zend\Module\Manager($appConfig['modules']);
        
        $listenerOptions  = new \Zend\Module\Listener\ListenerOptions($appConfig['module_listener_options']);
        $defaultListeners = new \Zend\Module\Listener\DefaultListenerAggregate($listenerOptions);

        //$defaultListeners->getConfigListener()->addConfigGlobPath('config/autoload/*.config.php');
        $moduleManager->events()->attachAggregate($defaultListeners);
        $moduleManager->loadModules();
        $this->unregisterModuleAutoloader();
        parent::__construct($defaultListeners->getConfigListener()->getMergedConfig());
    }
    
    protected function getAppConfig() {
        return array(
            'modules' => array(
		'myblog',
            ),
            'module_listener_options' => array( 
                'config_cache_enabled' => false,
                'cache_dir'            => realpath(__DIR__ . '/data/cache'),
                'module_paths' => array(
                    realpath(__DIR__ . '/modules'),
                ),
            ),
        );
    }

    protected function initStandartAutoloader() {
        require_once 'Zend/Loader/AutoloaderFactory.php';
        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array()
        ));
    }
    protected function initModuleAutoloader() {
        AutoloaderFactory::factory(array(
            'Zend\Loader\ModuleAutoloader' => array(
                'myblog' => realpath(__DIR__ . '/modules/myBlog'),
                )
        ));
    }
    protected function unregisterModuleAutoloader() {
        AutoloaderFactory::unregisterAutoloader('Zend\Loader\ModuleAutoloader');
    }

}