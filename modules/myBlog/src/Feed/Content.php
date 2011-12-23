<?php
namespace myBlog\Feed;

interface Content {
    public function getTitle();
    public function getTags();
    public function getCategory();
    public function getAuthor();
    public function getShortText();
    public function getContent();
}
