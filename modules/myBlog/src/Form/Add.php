<?php
namespace myBlog\Form;
use Zend\Form\Form as ZendForm;

class Add extends ZendForm {
 
    public function init(){
        
        $title = $this->createElement('text', 'title');
        $title->setRequired(true)->setLabel('Заголовок');
        
        
        $slug = $this->createElement('text', 'slug');
        $slug->setRequired(true)->setLabel('Slug');
        
        $keywords = $this->createElement('text', 'keywords');
        $keywords->setRequired(false)->setLabel('Ключевые слова');
        
        $tags = $this->createElement('text', 'tags');
        $tags->setRequired(false)->setLabel('Тэги');
        
        $description = $this->createElement('text', 'description');
        $description->setRequired(true)->setLabel('Описание')->setAttrib('rows', '5');
        
        
        $short = $this->createElement('textarea', 'short');
        $short->setRequired(true)->setLabel('Короткое описание')->setAttrib('rows', '5');
        
        
        $text = $this->createElement('textarea', 'text');
        $text->setRequired(true)->setLabel('Содержимое');
        
        $css = $this->createElement('textarea', 'css');
        $css->setRequired(false)->setLabel('css')->setAttrib('rows', '5');
        
        $js = $this->createElement('textarea', 'js');
        $js->setRequired(false)->setLabel('JavaScript')->setAttrib('rows', '5');
        
        $status = $this->createElement('checkbox', 'status');
        $status->setRequired(false)->setLabel('Статус')->setCheckedValue(1)->setUncheckedValue(0);
        
        
        $this->addElement($title);
        $this->addElement($status);
        $this->addElement($slug);
        $this->addElement($tags);
        $this->addElement($keywords);
        $this->addElement($description);
        $this->addElement($short);
        $this->addElement($text);
        $this->addElement($css);
        $this->addElement($js);
        $this->addElement('submit', 'post', array('label' => 'Сохранить'));
    }
    
    public function setTitle($value) {
        $this->getElement('title')->setValue($value);
    }
    public function setStatus($value) {
        $this->getElement('status')->setChecked($value);
    }
    public function getStatus() {
        return $this->getElement('status')->isChecked();
    }
    public function setSlug($value) {
        $this->getElement('slug')->setValue($value);
    }
    public function setKeywords($value) {
        $this->getElement('keywords')->setValue($value);
    }
    public function setDescription($value) {
        $this->getElement('description')->setValue($value);
    }
    public function setShort($value) {
        $this->getElement('short')->setValue($value);
    }
    public function setText($value) {
        $this->getElement('text')->setValue($value);
    }
    public function setCss($value) {
        $this->getElement('css')->setValue($value);
    }
    public function setJs($value) {
        $this->getElement('js')->setValue($value);
    }
    public function setTags(\Zend\Tag\ItemList $list) {
        $val =array();
        foreach($list as $tag) {
            $val[] = $tag->getTitle();
        }
        $this->getElement('tags')->setValue(implode(',', $val));
    }
    public function getTags() {
        $value = $this->getElement('tags')->getValue();
        $tags = explode(',', (string)$value);
        $tags = array_map('trim', $tags);
        
        $tagList = new \Zend\Tag\ItemList();
        
        foreach ($tags as $value) {
            if(strlen($value) == 0) {
                continue;
            }
            $tagList[] = new \Zend\Tag\Item(array(
                'Title' => $value,
                'Weight' => 0
            ));
        }
        
        return $tagList;
        
    }
    
}
