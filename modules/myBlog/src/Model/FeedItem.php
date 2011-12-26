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
        return $this->getConverter()->getSite($this->short);
    }
    
    public function getText() {
        return $this->getConverter()->getSite($this->text);
    }
    
    public function getShortRss() {
        return $this->getConverter()->getRss($this->short);
    }
    
    public function getTextRss() {
        return $this->getConverter()->getRss($this->text);
    }
    
    protected function getConverter() {
        return $this->getTable()->getConverter();
    }
    
}