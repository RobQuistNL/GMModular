<?php
class GMXAssetFolder {

    /**
     * @var DOMElement
     */
    public $node;

    public $name;

    public $children = array();

    public function __construct($name, $children)
    {
        $this->name = $name;
        $this->children = $children;
    }
}