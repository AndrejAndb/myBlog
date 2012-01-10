<?php
namespace Application;

use Zend\EventManager\GlobalEventManager;

class Application
{
    protected $application;
    protected $config;
    protected $moduleManager;

    public function __construct(\Zend\Module\Manager $manager)
    {
        $this->moduleManager   = $manager;
    }
    
    public function setupApplication(\Zend\EventManager\Event $e) {
        $this->application = $e->getParam('application');
        var_dump($this->application);
        $this->config = $e->getParam('config');
        GlobalEventManager::attach('getApplicationConfig', array($this, 'getApplicationConfig'));
        GlobalEventManager::attach('getLoadedModules', array($this, 'getLoadedModules'));
        GlobalEventManager::attach('getApplicationRequest', array($this, 'getApplicationRequest'));
        GlobalEventManager::attach('getApplicationLocator', array($this, 'getApplicationLocator'));
    }
    
    public function getApplicationConfig() {
        return $this->config;
    }
    
    public function getApplicationLocator() {
        if ($this->application == null) 
            return null;
        return $this->application->getLocator();
    }
    
    public function getLoadedModules() {
        if ($this->moduleManager == null) 
            return null;
        return $this->moduleManager->getLoadedModules();
    }
    
    public function getApplicationRequest() {
        if ($this->application == null) 
            return null;
        return $this->application->getRequest();
    }
}