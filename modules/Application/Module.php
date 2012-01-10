<?php
namespace Application;
use Zend\EventManager\StaticEventManager,
    Zend\EventManager\GlobalEventManager;

class Module
{ 
    protected $moduleManager;
    protected $application;
    protected $config;
    
    public function init($moduleManager) {
        $this->moduleManager   = $moduleManager;
        StaticEventManager::getInstance()->attach('bootstrap', 'bootstrap', array($this, 'setupApplication'), -1000);
    }
    
    public function setupApplication(\Zend\EventManager\Event $e) {
        $this->application = $e->getParam('application');
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

