<?php
namespace myBlog\Controller;
use Zend\Mvc\Controller\ActionController;

class BlogController extends ActionController {
    
    public function indexAction() {
        $this->setRouteParam('action','list');
        $data = $this->listAction();
        return $data;
    }
    
    public function listAction(\Zend\Tag\ItemList $tags = null) {
        $data = array();
        $data['posts'] = array();
        $feed = $this->getLocator()->get('myBlog\Model\Feed');
        
        $title= 'Последние записи блога';
        
        $list = $feed->getPosts($tags);
        foreach($list as $f) {
            $field = array(
                'id' => $f->id,
                'slug' => $f->slug,
                'title' => $f->title,
                'short' => $f->getShort(),
                'modifiedStamp' => $f->modified,
                'createdStamp' => $f->created,
                'tags' => $f->getTags()
            );
            $data['posts'][] = $field;
        }
        $data['title'] = $title;
        return $data;
    }
    
    public function tagsAction() {
        $tags = (string)$this->getRouteParam('tags', '');
        $tagsDB = $this->getLocator()->get('myBlog\Model\Tags');
        if (strlen(trim($tags)) != 0) {
            $tags = array_map('trim', explode(',', $tags));
            $tags = $tagsDB->getByTags($tags);
            if(count($tags)>0) {
                $data = $this->listAction($tags);
                $this->setRouteParam('action','list');
                
                $tagsArr = array();
                foreach($tags as $tag) {
                    $tagsArr[] = $tag->getTitle();
                }
                $data['title'] = 'Записи с меткой: ';
                $data['tags'] = new \Zend\Tag\Cloud();
                $data['tags']->setItemList($tags);
                return $data;
            }
        }
        
        $data['tags'] = new \Zend\Tag\Cloud();
        
        $tagsList = $tagsDB->getAllTags();
        $url = $this->plugin('url');
        foreach($tagsList as $tag) {
            $tag->setParam('url', $url->fromRoute('Home/tags', array('tags'=>$tag->getTitle())));
        }
        
        $data['tags']->setItemList($tagsList);
        return $data;
    }
    
    public function postAction() {
        $feed = $this->getLocator()->get('myBlog\Model\Feed');
        $slug = (string)$this->getRouteParam('slug', '');
        if(strlen($slug) == 0) {
            $this->getResponse()->setStatusCode(404);
            return null;
        }
        $row = $feed->getFromSlug($slug);
        if($row === null) {
            $this->getResponse()->setStatusCode(404);
            return null;
        }
        $data = array();
        $field = array(
            'id' => $row->id,
            'slug' => $row->slug,
            'title' => $row->title,
            'short' => $row->getShort(),
            'text' => $row->getText(),
            'keywords' => $row->keywords,
            'css' => $row->css,
            'js'  => $row->js,
            'description' => $row->description,
            'modifiedStamp' => $row->modified,
            'createdStamp' => $row->created,
            'tags' => $row->getTags()
        );
        $data['post'] = $field;
        /*$host = $this->getRequest()->server()->get('HTTP_HOST');
        $referer = $this->getRequest()->headers()->get('referer');//->getFieldValue();
        if ($referer != null) {
            $referer = ->getFieldValue();
        }
        if(strlen($host)>0 && strlen($referer)) {
            
        }
        var_dump($this->getRequest()->server()->get('HTTP_HOST'));
        $data['referer'] = $this->getRequest()->getBaseUrl();//->headers()->get('referer')->getFieldValue();*/
        return $data;
    }
    
    public function rssAction(){
        
        $url = $this->plugin('url');
        
        $feed = new \Zend\Feed\Writer\Feed();
        $feed->setTitle('Andrej Andb - Блог web-разработчика');
        $feed->setLink($url->fromRoute('Home/rss', array(), array('absolute'=>true)));
        $feed->addAuthor('Андрей Баранов', 'andrej.andb@gmail.com', 'http://www.example.com');
        $feed->setGenerator('andrej-andb.ru');
        $feed->setLanguage('ru');
        $feed->setDescription('Блог о Web-разработке. Тематика: PHP, Zend Framework, HTML и прочее');
        $feed->setDateModified(time());
        $feed->setCopyright('Copyright © 2011, andrej.andb@gmail.com');

        $feedDb = $this->getLocator()->get('myBlog\Model\Feed');
        $list = $feedDb->getPosts(null, 20);
        
        foreach($list as $post) {
            $entry = $feed->createEntry();
            $entry->setTitle($post->title);
            $entry->setLink($url->fromRoute('Home/post', array('slug' => $post->slug), array('absolute'=>true)));
            $entry->addAuthor('Андрей Баранов', 'andrej.andb@gmail.com', 'http://www.example.com');
            $entry->setDateModified($post->modified);
            $entry->setDateCreated($post->created);
            $entry->setDescription($post->getShortRss());
            $entry->setContent($post->getTextRss());
            $categories = array();
            foreach($post->getTags() as $tag) {
                $categories[] = array(
                    'term' => $tag->getTitle()
                    );
            }
            $entry->addCategories($categories);
            $entry->setCopyright('Copyright © 2011, andrej.andb@gmail.com');
            $feed->addEntry($entry);
        }

        
        $response = $this->getResponse();
        $response->setContent($feed->export('rss'));
        $response->headers()->addHeader(\Zend\Http\Header\ContentType::fromString('content-type: text/xml; charset=utf-8'));
        $response->headers()->addHeader(\Zend\Http\Header\CacheControl::fromString('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0'));
        return $response;
    }
    
    
    protected function setRouteParam($key, $value) {
        $this->getEvent()->getRouteMatch()->setParam($key, $value);
    }
    protected function getRouteParam($key, $default = null) {
        return $this->getEvent()->getRouteMatch()->getParam($key, $default);
    }
    
}
