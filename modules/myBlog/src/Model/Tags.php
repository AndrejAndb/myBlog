<?php
namespace myBlog\Model;


class Tags extends \Zend\Db\Table\AbstractTable {
    
    protected $_name = 'tags';
    
    
    public function init() {
        
    }
    
    public function updateForId($id, \Zend\Tag\ItemList $list) {
        $adapter = $this->getAdapter();
        $tableName = $adapter->quoteIdentifier($this->_name);
        $this->deleteForId($id);
        foreach ($list as $tag) {
            
            $adapter->query('REPLACE INTO '.$tableName.' (id, tag) VALUES (?, ?)', array($id, $tag->getTitle()));
        }
    }
    
    public function getById($id) {
        $list = new \Zend\Tag\ItemList();
        
        $select = $this->select();
        $adapter = $this->getAdapter();
        $tableName = $adapter->quoteIdentifier($this->_name);
        $id = $adapter->quote($id);
        $select->from(array('tag'=>$this->_name))->columns(array(
            '(SELECT COUNT(*) FROM '.$tableName.' WHERE tag = tag.tag) AS weight'
        ))->where('id = '. $id);
        $rows = $this->fetchAll($select);
        foreach($rows as $row) {
            $list[] = new \Zend\Tag\Item(array(
                'Title' => $row->tag,
                'Weight' => $row->weight
            ));
        }
        return $list;
    }
    
    public function getByTags(array $tags) {
        $list = new \Zend\Tag\ItemList();
        
        $select = $this->select();
        $adapter = $this->getAdapter();
        $tableName = $adapter->quoteIdentifier($this->_name);
        $tags = array_map(array($adapter, 'quote'), $tags);
        $select->from(array('tag'=>$this->_name))
                ->columns(array(
                    'COUNT(tag.tag) as weight'
                    ))
                ->where('tag.tag in ('. implode(',',$tags).')')
                ->group('tag');
        $rows = $this->fetchAll($select);
        foreach($rows as $row) {
            $list[] = new \Zend\Tag\Item(array(
                'Title' => $row->tag,
                'Weight' => $row->weight
            ));
        }
        return $list;
    }
    
    public function getAllTags() {
        $list = new \Zend\Tag\ItemList();
        
        $select = $this->select();
        $adapter = $this->getAdapter();
        $tableName = $adapter->quoteIdentifier($this->_name);
        $select->from(array('tag'=>$this->_name))->columns(array(
            '(SELECT COUNT(*) FROM '.$tableName.' WHERE tag = tag.tag) AS weight'
        ))->group('tag');
        $rows = $this->fetchAll($select);
        foreach($rows as $row) {
            $list[] = new \Zend\Tag\Item(array(
                'Title' => $row->tag,
                'Weight' => $row->weight
            ));
        }
        return $list;
    }
    
    public function deleteForId($id) {
        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->delete($where);
    }
    
}