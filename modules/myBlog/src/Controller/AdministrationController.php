<?php
namespace myBlog\Controller;
use Zend\Mvc\Controller\ActionController;

class AdministrationController extends ActionController {
    
    public function indexAction() {
        $feed = $this->getLocator()->get('myBlog\Model\Feed');
        $list = $feed->getAll();
        $data = array();
        foreach($list as $f) {
            $field = array(
                'id' => $f->id,
                'slug' => $f->slug,
                'title' => $f->title,
                'category' => $f->category,
                'modifiedStamp' => $f->modified,
                'createdStamp' => $f->created,
                'published' => $f->getStatus()
            );
            $data[] = $field;
        }
        return array('feed' => $data);
    }
    
    public function addAction() {
        
        $form = new \myBlog\Form\Add();
        $Url = $this->plugin('url');
        $form->setAction($Url->fromRoute('Administration', array('action'=>'add')));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if($form->isValid($request->post()->toArray())) {
                
                $feed = $this->getLocator()->get('myBlog\Model\Feed');
                $data = $form->getValues();
                
                $feed->createItem(array(
                    'slug'  => $data['slug'],
                    'title' => $data['title'],
                    'keywords' => $data['keywords'],
                    'description' => $data['description'],
                    'short' => $data['short'],
                    'text'  => $data['text'],
                    'css' => $data['css'],
                    'js'  => $data['js'],
                    'status' => $form->getStatus(),
                    'tags' => $form->getTags()
                ));
                
                $Redirect = $this->plugin('Redirect');
                $Redirect->toRoute('Administration');
                
                return;
            } else {
                return array('form' => $form);
            }
        } else {
            return array('form' => $form);
        }
    }
    
    
    public function editAction() {
        
        $routeMatch = $this->getEvent()->getRouteMatch();
        $id = $routeMatch->getParam('id', null);
        $Redirect = $this->plugin('Redirect');
        
        if($id === null) {
            $Redirect->toRoute('Administration');
        }
        
        $form = new \myBlog\Form\Add();
        $Url = $this->plugin('url');
        $form->setAction($Url->fromRoute('Administration', array('action'=>'edit', 'id'=>$id)));
        
        $request = $this->getRequest();
        $feed = $this->getLocator()->get('myBlog\Model\Feed');
        
        if ($request->isPost() && $form->isValid($request->post()->toArray())) {
            
            $data = $form->getValues();
            

            $feed->saveItem(array(
                'slug'  => $data['slug'],
                'title' => $data['title'],
                'keywords' => $data['keywords'],
                'description' => $data['description'],
                'short' => $data['short'],
                'text'  => $data['text'],
                'css' => $data['css'],
                'js'  => $data['js'],
                'status' => $form->getStatus(),
                'tags' => $form->getTags()
            ), $id);

            $Redirect->toRoute('Administration');
            return;
                
        } else {
            $row = $feed->getById($id);
            if ($row === null) {
                $Redirect->toRoute('Administration');
                return;
            } else {
                $form->setTitle($row['title']);
                $form->setSlug($row['slug']);
                $form->setKeywords($row['keywords']);
                $form->setDescription($row['description']);
                $form->setShort($row['short']);
                $form->setText($row['text']);
                $form->setCss($row['css']);
                $form->setJs($row['js']);
                $form->setStatus($row->getStatus());
                $form->setTags($row->getTags());
                return array('form' => $form);
            }
        }
    }
    
    
    public function deleteAction() {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $id = $routeMatch->getParam('id', null);
        if($id !== null) {
            $feed = $this->getLocator()->get('myBlog\Model\Feed');
            $feed->deleteById($id);
        }
        $Redirect = $this->plugin('Redirect');
        $Redirect->toRoute('Administration');
    }
    
}
