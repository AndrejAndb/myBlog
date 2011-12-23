<?php
namespace myBlog\Service;


class Feed {
    
    protected $adapter;
    
    public function setAdapter(\Zend\Db\Adapter\AbstractAdapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function getAdapter() {
        return $this->adapter;
    }
    
    public function create(array $data) {
        
        $contentData = array(
            'short' => null,
            'text' => null,
            'css' => null,
            'js' => null,
        );
        
        
        $shortData = array(
            'type' => 0,
            'text' => $data['short']
        );
        $textData = array(
            'type' => 0,
            'text' => $data['text']
        );
        $cssData = array(
            'type' => 1,
            'text' => $data['css']
        );
        $jsData = array(
            'type' => 1,
            'text' => $data['js']
        );
        
        $dataForInsert = array(
            'title' => $component->getComponentClassName(),
            'slug' => $component->getOrder(),
            'active' => $component->isActive(),
            'name' => $component->getName(),
            'valid' => $component->isValid(),
            'route' => $component->getRouteLiteral(),
        );
        
        if($component->getParent() === null) {
            $dataForInsert['parent_id'] = 0;
        } else {
            $dataForInsert['parent_id'] = $component->getParent()->getId();
        }
        
        $this->adapter->insert($this->getTableName(), $dataForInsert);
        $id = (int) $this->adapter->lastInsertId();
        
        $component->setId($id);
        $dataForInsert['id'] = $id;
        $component->setPersistData($dataForInsert);
        
        if($recursive) {
            foreach($component->_getUserComponents() as $node) {
                $this->insert($node, $recursive);
            }
        }
    }
    
}
