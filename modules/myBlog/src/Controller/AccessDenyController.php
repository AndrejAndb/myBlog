<?php
namespace myBlog\Controller;
use Zend\Mvc\Controller\ActionController;

class AccessDenyController extends ActionController {
    
    public function indexAction() {
        $this->getResponse()->setStatusCode(403);
        return array('content' => 'stub');
    }
    
}
