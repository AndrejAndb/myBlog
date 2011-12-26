<?php
namespace myBlog\View\Helper;

class TagsMenu extends \Zend\View\Helper\AbstractHelper
{
    protected $locator;
    
    protected $cloud1Count = 4;
    
    public function setLocator(\Zend\Di\Locator $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    public function getLocator()
    {
        return $this->locator;
    }
    
    public function __invoke()
    {
        $tagsDB = $this->getLocator()->get('myBlog\Model\Tags');
        $ItemList = $tagsDB->getAllTags();
        $ItemList = iterator_to_array($ItemList);
        usort($ItemList, array($this, 'cmpWeight'));
        
        $tagsList = new \Zend\Tag\ItemList();
        
        $index = 0;
        while($index<$this->cloud1Count && count($ItemList)>0) {
            $index++;
            $tag = array_shift($ItemList);
            $tag->setParam('url', $this->getView()->url('Home/tags', array('tags'=>$tag->getTitle())));
            $tagsList[] = $tag;
        }
        
        $tagCloud1 = new \Zend\Tag\Cloud();
        $tagCloud1->setItemList($tagsList);
        $tagCloud1->getCloudDecorator()->setHTMLTags(array(
            'ul' => array('class' => 'cloud-menu layout')
        ));
        $tagCloud1->getTagDecorator()->setClassList(array(
            'tag1','tag2','tag3'
        ));
        
        
        $tagsList = new \Zend\Tag\ItemList();
        
        while(count($ItemList)>0) {
            $tag = array_shift($ItemList);
            $tag->setParam('url', $this->getView()->url('Home/tags', array('tags'=>$tag->getTitle())));
            $tagsList[] = $tag;
        }
        
        $tagCloud2 = new \Zend\Tag\Cloud();
        $tagCloud2->setItemList($tagsList);
        $tagCloud2->getCloudDecorator()->setHTMLTags(array(
            'ul' => array('class' => 'cloud-tags layout')
        ));
        $tagCloud2->getTagDecorator()->setFontSizeUnit('%')->setMaxFontSize(200)->setMinFontSize(70);
        
        return $tagCloud1->__toString().$tagCloud2->__toString();
    }
    
    public function cmpWeight($a, $b) {
        if($a->getWeight() == $b->getWeight()) {
            return 0;
        }
        return ($a->getWeight() < $b->getWeight()) ? 1 : -1;
    }
    
}