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
        $config = $defaultListeners->getConfigListener();
        $config->addConfigGlobPaths($appConfig['globConfPath']);

        $moduleManager->events()->attachAggregate($defaultListeners);
        $moduleManager->loadModules();
        $this->unregisterModuleAutoloader();
        parent::__construct($config->getMergedConfig());
    }
    
    protected function getAppConfig() {
        return array(
            'modules' => array(
		'myblog',
                'EdpCommon',
                'EdpUser',
            ),
            'globConfPath' => array(
                realpath(__DIR__ . '/config').'/*.php',
            ),
            'module_listener_options' => array(
                'ConfigCacheEnabled' => false,
                'CacheDir'            => realpath(__DIR__ . '/data/cache'),
                'ConfigCacheKey' => 'cache',
                'module_paths' => array(
                    realpath(__DIR__ . '/modules'),
                    realpath(__DIR__ . '/../zfModules')
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