<?php
namespace myBlog\Filter;

class Tags extends \Zend\Filter\AbstractFilter {
    
    public function filter($value) {
        $tags = explode(',', (string)$value);
        $tags = array_map('trim', $tags);
        
        foreach ($tags as $key => $value) {
            if(strlen($value) == 0) {
                unset($tags[$key]);
            }
        }
        return $tags;
    }
}