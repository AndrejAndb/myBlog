<?php
namespace myBlog\Model;


class FeedItem extends \Zend\Db\Table\AbstractRow {
    
    protected $tags = null;
    protected $tagsIsModified = false;
    
    protected function _insert()
    {
        $this->created = time();
        if (!in_array($this->status, array('PUBLISHED', 'UNPUBLISHED'))) {
            $this->status = 'UNPUBLISHED';
        }
        $this->_update();
    }
    protected function _update()
    {
        $this->modified = time();
    }
    protected function _postUpdate()
    {
        $this->updateTags();
    }
    protected function _postInsert()
    {
        $this->updateTags();
    }
    protected function _postDelete()
    {
        $tagsDB = $this->getTable()->getLocator()->get('myBlog\Model\Tags');
        $tagsDB->deleteForId('feed-'.$this->id);
    }
    
    protected function updateTags() {
        if($this->tagsIsModified) {
            $tagsDB = $this->getTable()->getLocator()->get('myBlog\Model\Tags');
            $tagsDB->updateForId('feed-'.$this->id, $this->tags);
        }
    }
    
    public function setTags(\Zend\Tag\ItemList $list) {
        $this->tags = $list;
        $this->tagsIsModified = true;
    }
    
    public function getTags() {
        if ($this->tags == null) {
            $tagsDB = $this->getTable()->getLocator()->get('myBlog\Model\Tags');
            $this->tags = $tagsDB->getById('feed-'.$this->id);
            $this->tagsIsModified = false;
        }
        return $this->tags;
    }
    
    public function getStatus() {
        if ($this->status == 'PUBLISHED') {
            return true;
        }
        return false;
    }
    
    public function getShort() {
        $xml = $this->getXmlContent($this->short);
        if($xml == null) {
            return $this->short;
        }
        return $this->getConverter()->getSite($xml);
    }
    
    public function getText() {
        $xml = $this->getXmlContent($this->text);
        if($xml == null) {
            return $this->text;
        }
        return $this->getConverter()->getSite($xml);
    }
    
    public function getShortRss() {
        $xml = $this->getXmlContent($this->short);
        if($xml == null) {
            return $this->short;
        }
        return $this->getConverter()->getRss($xml);
    }
    
    public function getTextRss() {
        $xml = $this->getXmlContent($this->text);
        if($xml == null) {
            return $this->text;
        }
        return $this->getConverter()->getRss($xml);
    }
    
    protected function getXmlContent($text) {
        $xml = new \DOMDocument('1.0', 'utf-8');
        try {
            if(@$xml->loadXML($text) == false){
                throw new \Exception();
            }

        } catch (\Exception $e) {
            return null;
        }
        $xpath = new \DOMXpath($xml);
        $items = $xpath->query('/document');
        $url = $this->getTable()->getLocator()->get('view')->url('Home/post', array('slug' => $this->slug), array('absolute'=>true));
        foreach ($items as $item) {
            $item->setAttributeNode(new \DOMAttr('link', $url));
            
        }
        
        return $xml;
    }
    
    protected function getConverter() {
        return $this->getTable()->getConverter();
    }
    
}