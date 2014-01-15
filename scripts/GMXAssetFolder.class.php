<?php
class GMXAssetFolder {

    /**
     * @var DOMElement
     */
    public $node;

    public $name;

    public $type; //Asset type (sound/sprite/object etc.)

    public $children = array();

    public function __construct($name, $type, $children)
    {
        $this->name = $name;
        $this->children = $children;
        $this->type = $type;
    }
}