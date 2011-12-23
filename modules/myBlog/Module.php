<?php
namespace myBlog;

use InvalidArgumentException,
    Zend\Loader\AutoloaderFactory,
    Zend\Config\Config;

class Module implements \Zend\Module\Consumer\AutoloaderProvider
{ 
    protected $view = Null;
    protected $viewListener = Null;
    
    public function init() {
        $events = \Zend\EventManager\StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'initializeView'), 1000);
        $events->attach('bootstrap', 'bootstrap', array($this, 'initializeStructureRoute'), 1000);
    }
    
    public function getConfig(){
        $config = new Config(array( //TODO: Вынести в конфиг
            'di' => array(
                'instance' => array(
                    'alias' => array(
                        'Db_default'  => 'Zend\Db\Adapter\PdoMysql',
                        'myblog_administration' => 'myBlog\Controller\AdministrationController',
                        'ServiceLocator' => 'Zend\Di\ServiceLocator',
                        'view'  => 'Zend\View\PhpRenderer',
                    ),
                    
                    'Db_default' => array(
                        'parameters' => array(
                            'config'  => array(
                                'dbname' => 'acms_prototype',
                                'password' => 'acms_prototype',
                                'username' => 'acms_prototype',
                                'host' => 'localhost'
                            ),
                        ),
                    ),  
                    
                    'myBlog\Model\Feed' => array(
                        'parameters' => array(
                            'config' => 'Db_default',
                        ),
                    ),
                    'myBlog\Model\Tags' => array(
                        'parameters' => array(
                            'config' => 'Db_default',
                        ),
                    ),
                    
                    'Zend\View\HelperLoader' => array(
                        'parameters' => array(
                            'map' => array(
                            ),
                        ),
                    ),

                    'Zend\View\HelperBroker' => array(
                        'parameters' => array(
                            'loader' => 'Zend\View\HelperLoader',
                        ),
                    ),

                    'Zend\View\PhpRenderer' => array(
                        'parameters' => array(
                            'resolver' => 'Zend\View\TemplatePathStack',
                            'options'  => array(
                                'script_paths' => array(
                                    'cmsDefault' => realpath( __DIR__ . '/../../template'),
                                ),
                            ),
                            'broker' => 'Zend\View\HelperBroker',
                        ),
                    ),
                )
            ),
            'routes' => array(
                'Administration' => array(
                    'type' => 'segment',
                    'options' => array (
                        'route' =>'/administration[/:action][/:id]',
                        'defaults' => array(
                            'controller' => 'myblog_administration',
                            'action' => 'index',
                            'id' => null
                        )
                    ),
                    'may_terminate' => true,
                )
            )
        ));
        return $config;
    }
    
    public function getRoutes($locator){
        $structure = $locator->get('Cms_Service_Structure');
        return array($structure->getRouterArray());
    }
    
    public function initializeStructureRoute(){
        
    }
    public function initializeView($e){
        $app          = $e->getParam('application');
        $locator      = $app->getLocator();
        $config       = $e->getParam('config');
        $view         = $this->getView($app);
        $viewListener = $this->getViewListener($view, $config->view);
        $app->events()->attachAggregate($viewListener);
    }
    protected function getViewListener($view, $config)
    {
        if ($this->viewListener instanceof View\Listener) {
            return $this->viewListener;
        }

        $viewListener       = new View\Listener($view);
        //$viewListener->setDisplayExceptionsFlag($config->display_exceptions);

        $this->viewListener = $viewListener;
        return $viewListener;
    }

    protected function getView($app)
    {
        if ($this->view) {
            return $this->view;
        }

        $di     = $app->getLocator();
        $view   = $di->get('view');
        $url    = $view->plugin('url');
        $url->setRouter($app->getRouter());

        $view->plugin('headTitle')->setSeparator(' - ')
                                  ->setAutoEscape(false)
                                  ->append('Application');
        $this->view = $view;
        return $view;
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                  'myBlog'     => __DIR__.DIRECTORY_SEPARATOR.'src',
                ),
            )
        );
    }
}

