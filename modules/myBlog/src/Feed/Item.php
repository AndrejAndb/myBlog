<?php
namespace myBlog\Feed;

class Item {
    
    protected $title;
    protected $slug;
    protected $tags;
    protected $category;
    protected $short;
    protected $text;
    protected $css;
    protected $js;
    
    public function getTitle() {
        return $this->title;
    }
    public function getSlug() {
        return $this->slug;
    }
    public function getTags() {
        return $this->tags;
    }
    public function getCategory() {
        return $this->category;
    }
    public function getShort() {
        return $this->short;
    }
    public function getContent() {
        return $this->text;
    }
    public function getCss() {
        return $this->css;
    }
    public function getJs() {
        return $this->js;
    }
}
