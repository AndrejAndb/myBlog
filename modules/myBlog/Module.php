<?php
namespace myBlog;

use InvalidArgumentException,
    Zend\Loader\AutoloaderFactory,
    Zend\Config\Config;

class Module implements \Zend\Module\Consumer\AutoloaderProvider
{ 
    protected $view = Null;
    protected $viewListener = Null;
    
    public function init($manager) {
        $events = \Zend\EventManager\StaticEventManager::getInstance();
        $events->attach('bootstrap', 'bootstrap', array($this, 'initializeView'), 1000);
        $events->attach('Zend\Mvc\Application', 'route', array($this, 'checkAccessHandler'), -100);
    }
    
    public function getConfig(){
        $config = new Config(array( //TODO: Вынести в конфиг
            'di' => array(
                'instance' => array(
                    'alias' => array(
                        'Db_default'  => 'Zend\Db\Adapter\DiPdoMysql',
                        'Db_PDO'  => 'PDO',
                        'ServiceLocator' => 'Zend\Di\ServiceLocator',
                        'view'  => 'Zend\View\PhpRenderer',
                        
                        
                        'myblog_administration' => 'myBlog\Controller\AdministrationController',
                        'access_deny' => 'myBlog\Controller\AccessDenyController',
                        'Blog' => 'myBlog\Controller\BlogController',
                        
                        
                    ),
                    
                    'Db_default' => array(
                        'parameters' => array(
                            'pdo'            => 'Db_PDO',
                            'config'       =>  array(),
                        ),
                    ),
                    'Db_PDO' => array(
                        'parameters' => array(
                            'dsn'            => 'mysql:dbname=acms_prototype;host=localhost',
                            'username'       => 'acms_prototype',
                            'passwd'         => 'acms_prototype',
                            'driver_options' => array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''),
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
                                'tagsmenu' => 'myBlog\View\Helper\TagsMenu'
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
                'Home' => array(
                    'type' => 'literal',
                    'options' => array (
                        'route' =>'/',
                        'defaults' => array(
                            'controller' => 'Blog',
                            'action' => 'index',
                        )
                    ),
                    'may_terminate' => true,
                    'child_routes' => array(
                        'tags' => array(
                            'type' => 'segment',
                            'options' => array(
                                'route' => 'tag[/:tags]',
                                'defaults' => array(
                                    'controller' => 'Blog',
                                    'action'     => 'tags',
                                ),
                            ),
                        ),
                        'post' => array(
                            'type' => 'segment',
                            'options' => array(
                                'route' => 'post[/:slug]',
                                'defaults' => array(
                                    'controller' => 'Blog',
                                    'action'     => 'post',
                                ),
                            ),
                        ),
                        'rss' => array(
                            'type' => 'literal',
                            'options' => array(
                                'route' => 'rss',
                                'defaults' => array(
                                    'controller' => 'Blog',
                                    'action'     => 'rss',
                                ),
                            ),
                        )
                    )
                ),
                'Administration' => array(
                    'type' => 'segment',
                    'options' => array (
                        'route' =>'/administration[/:action][/:id]',
                        'defaults' => array(
                            'access' => 'admin',
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
    
    public function checkAccessHandler(\Zend\Mvc\MvcEvent $e){
        $routeMatch = $e->getRouteMatch();
        if ($routeMatch !== null) {
            $access = $routeMatch->getParam('access', null);
            if ($access == 'admin') {
                $app  = $e->getTarget();
                $authService = $app->getLocator()->get('edpuser_user_service')->getAuthService();
                if (!($authService->hasIdentity() && $authService->getIdentity()->getUserId() == 1)) {
                    $e->setError(403);
                    $app->events()->trigger('dispatch.error', $e);
                    $e->stopPropagation();
                }
            }
        }
        return $e->getResponse();
    }
    public function initializeView($e){
        $app          = $e->getParam('application');
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

        $view->plugin('doctype')->setDoctype('HTML5');
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

