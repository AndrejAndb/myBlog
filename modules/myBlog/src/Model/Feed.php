<?php
namespace myBlog\Model;


class Feed extends \Zend\Db\Table\AbstractTable {
    
    protected $_name = 'feed';
    protected $locator;
    
    protected $_rowClass = '\myBlog\Model\FeedItem';
    
    protected $converter = null;
    
    public function init() {
        
    }
    
    public function setLocator(\Zend\Di\Locator $locator)
    {
        $this->locator = $locator;
    }

    public function getLocator()
    {
        return $this->locator;
    }
    
    public function getConverter() {
        return $this->getLocator()->get('myBlog\Xslt\Convert');
    }
    
    public function createItem($data) {
        $tags = null;
        if(isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }
        if(isset($data['status'])) {
            if ($data['status']) {
                $data['status'] = 'PUBLISHED';
            } else {
                $data['status'] = 'UNPUBLISHED';
            }
        }
        $item = $this->createRow($data);
        if ($tags instanceof \Zend\Tag\ItemList) {
            $item->setTags($tags);
        }
        $item->save();
        return $item;
    }
    
    public function getFromSlug($slug) {
        $select = $this->select();
        $adapter = $this->getAdapter();
        $tableName = $adapter->quoteIdentifier($this->_name);
        $select ->where('status = "PUBLISHED"')
                ->where('slug = ?', $slug);
        return $this->fetchRow($select);
    }
    
    public function saveItem($data, $id) {
        
        $row = $this->getById($id);
        
        if ($row === null) {
            return;
        }
        
        if(isset($data['tags'])) {
            $row->setTags($data['tags']);
            unset($data['tags']);
        }
        if(isset($data['status'])) {
            if ($data['status']) {
                $data['status'] = 'PUBLISHED';
            } else {
                $data['status'] = 'UNPUBLISHED';
            }
        }

        $row->setFromArray($data);
        $row->save();
    }
    
    public function getAll() {
        $select = $this->select();
        $select->order('created desc');
        return $this->fetchAll($select);
    }
    
    public function getPosts(\Zend\Tag\ItemList $tags = null, $count = 10, $offset = 0) {
        $select = $this->select();
        $adapter = $this->getAdapter();
        $select->where('status = "PUBLISHED"')
                ->order('created desc')
                ->limit($count, $offset);
        
        if($tags !== null) {
            $tagsArr = array();
            foreach($tags as $tag) {
                $tagsArr[] = $tag->getTitle();
            }
            $tagsArr = array_map(array($adapter, 'quote'), $tagsArr);
            
            $select->where("(SELECT COUNT(*) FROM tags WHERE tags.id = CONCAT('feed-',feed.id) AND tags.tag IN (".  implode(',', $tagsArr).") ) > 0");
        }
        
        return $this->fetchAll($select);
    }
    
    public function getById($id) {
        $select = $this->select();
        $select->where('id = ?', $id);
        $rowSet = $this->fetchAll($select);
        
        if (count($rowSet) !== 1) {
            return null;
        }
        return $rowSet->getRow(0);
    }
    
    public function deleteById($id) {
        $row = $this->getById($id);
        if($row !== null) {
            $row->delete();
        }
    }
    
}