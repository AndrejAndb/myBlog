<?php

namespace myBlog\View;

use ArrayAccess,
    Zend\Di\Locator,
    Zend\EventManager\EventCollection,
    Zend\EventManager\ListenerAggregate,
    Zend\EventManager\StaticEventCollection,
    Zend\EventManager\StaticEventManager,
    Zend\Http\Response,
    Zend\Mvc\Application,
    Zend\Mvc\MvcEvent,
    Zend\View\PhpRenderer as Renderer;

class Listener implements ListenerAggregate
{
    protected $listeners = array();
    protected $staticListeners = array();
    protected $view;
    protected $displayExceptions = true;

    public function __construct(Renderer $renderer)
    {
        $this->view   = $renderer;
    }

    public function setDisplayExceptionsFlag($flag)
    {
        $this->displayExceptions = (bool) $flag;
        return $this;
    }

    public function displayExceptions()
    {
        return $this->displayExceptions;
    }

    public function attach(EventCollection $events)
    {
        $this->listeners[] = $events->attach('dispatch.error', array($this, 'renderError'));
        $this->listeners[] = $events->attach('dispatch', array($this, 'render404'), -80);
        $this->listeners[] = $events->attach('dispatch', array($this, 'renderLayout'), -1000);
        $this->listeners[] = $events->attach('route', array($this, 'routeMatch'), -1000);
        $this->registerStaticListeners(StaticEventManager::getInstance());
    }

    public function detach(EventCollection $events)
    {
        foreach ($this->listeners as $key => $listener) {
            $events->detach($listener);
            unset($this->listeners[$key]);
            unset($listener);
        }
        $this->detachStaticListeners(StaticEventManager::getInstance());
    }

    protected function registerStaticListeners(StaticEventCollection $events)
    {
        $ident   = 'Zend\Mvc\Controller\ActionController';
        $handler = $events->attach($ident, 'dispatch', array($this, 'renderView'), -50);
        $this->staticListeners[] = array($ident, $handler);
        
        $ident   = 'Cms\Controller\AjaxController';
        $handler = $events->attach($ident, 'dispatch', array($this, 'renderAjax'), -50);
        $this->staticListeners[] = array($ident, $handler);
    }

    public function detachStaticListeners(StaticEventCollection $events)
    {
        foreach ($this->staticListeners as $i => $info) {
            list($id, $handler) = $info;
            $events->detach($id, $handler);
            unset($this->staticListeners[$i]);
        }
    }
    
    public function routeMatch(MvcEvent $e) {
        $routeMatch = $e->getRouteMatch();
        if($routeMatch instanceof \Zend\Mvc\Router\RouteMatch) {
            $urlPlugin = $this->view->plugin('url');
            $urlPlugin->setRouteMatch($routeMatch);
        }
    }
    
    public function renderAjax(MvcEvent $e) {
        $request = $e->getRequest();
        $method = $request->query()->get('format', 'json');
        
        switch ($method) {
            case 'html':
                $htmlContent = null;
                try {
                    $htmlContent = $this->renderHtmlAjax($e);
                } catch (Zend\View\Exception\RuntimeException $e) {
                    $htmlContent = null;
                }
                if(!is_null($htmlContent)) {
                    $e->setResult($htmlContent);
                    return $htmlContent;
                }
                break;
        }
    }

    public function renderHtmlAjax(MvcEvent $e)
    {
        $response = $e->getResponse();
        if (!$response->isSuccess()) {
            return;
        }

        $routeMatch = $e->getRouteMatch();
        
        $template = $routeMatch->getParam('template', 'default');
        $component = $routeMatch->getParam('component', 'default');
        $controller = $routeMatch->getParam('controller', 'default');
        
        $request = $e->getRequest();
        
        if($_action  = $routeMatch->getParam('action', false)) {
            $action = $_action;
        } elseif($request->isPost() && ($_action = $request->post()->get('action', false))) {
            $action = $_action;
        } elseif($request->isGet() &&($_action = $request->query()->get('action', false)) ) {
            $action = $_action;
        }
        
        $templateVariation     = $routeMatch->getParam('templateVariation', 'default');
        
        $script = 'components' . DIRECTORY_SEPARATOR 
                . $component . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR 
                . $action . DIRECTORY_SEPARATOR . $templateVariation . '.phtml';

        $vars       = $e->getResult();
        if (is_scalar($vars)) {
            $vars = array('content' => $vars);
        } elseif (is_object($vars) && !$vars instanceof ArrayAccess) {
            $vars = (array) $vars;
        }

        $content    = $this->view->rendere($script, $vars);

        $e->setResult($content);
        return $content;
    }

    public function renderView(MvcEvent $e)
    {
        $response = $e->getResponse();
        if (!$response->isSuccess()) {
            return;
        }

        $routeMatch = $e->getRouteMatch();
        
        $controller = $routeMatch->getParam('controller', 'default');
        $action     = $routeMatch->getParam('action', 'index');
        $templateVariation     = $routeMatch->getParam('templateVariation', 'default');
        
        $script = $controller . DIRECTORY_SEPARATOR . $action. '.phtml';

        $vars       = $e->getResult();
        if (is_scalar($vars)) {
            $vars = array('content' => $vars);
        } elseif (is_object($vars) && !$vars instanceof ArrayAccess) {
            $vars = (array) $vars;
        }

        $content    = $this->view->render($script, $vars);

        $e->setResult($content);
        return $content;
    }

    public function renderLayout(MvcEvent $e)
    {
        $response = $e->getResponse();
        if (!$response) {
            $response = new Response();
            $e->setResponse($response);
        }
        if ($response->isRedirect()) {
            return $response;
        }
        
        $contentType = $response->headers()->get('contenttype');
        if ($contentType instanceof \Zend\Http\Header\HeaderDescription) {
            $contentType = $contentType->getFieldValue();
        } else {
            $contentType = 'text/html';
        }
        
        $content = '';
        switch ($contentType) {
            case 'application/json':
                $result = $e->getResult();
                $content = \Zend\Json\Json::encode($result);
                break;
            default:

                if (false !== ($contentParam = $e->getParam('content', false))) {
                    $vars['content'] = $contentParam;
                } else {
                    $vars['content'] = $e->getResult();
                }

                $script = 'layouts' . DIRECTORY_SEPARATOR  . 'layout.phtml';

                $content   = $this->view->render($script, $vars);
                break;
        }
        
        $response->setContent($content);
        return $response;
    }

    public function render404(MvcEvent $e)
    {
        $vars = $e->getResult();
        if ($vars instanceof Response) {
            return;
        }

        $response = $e->getResponse();
        if ($response->getStatusCode() != 404) {
            // Only handle 404's
            return;
        }

        $vars = array('message' => 'Page not found.');

        $content = $this->view->render('pages/404.phtml', $vars);

        $e->setResult($content);

        return $content;
    }

    public function renderError(MvcEvent $e)
    {
        $error    = $e->getError();
        $app      = $e->getTarget();
        $response = $e->getResponse();
        if (!$response) {
            $response = new Response();
            $e->setResponse($response);
        }

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
                $vars = array(
                    'message' => 'Page not found.',
                );
                $response->setStatusCode(404);
                break;

            case Application::ERROR_EXCEPTION:
            default:
                $exception = $e->getParam('exception');
                $vars = array(
                    'message'            => 'An error occurred during execution; please try again later.',
                    'exception'          => $e->getParam('exception'),
                    'display_exceptions' => $this->displayExceptions(),
                );
                $response->setStatusCode(500);
                break;
        }

        $content = $this->view->render('error/index.phtml', $vars);

        $e->setResult($content);

        return $this->renderLayout($e);
    }
}