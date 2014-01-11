<?php
class Submodule {

    public $type; //available or installed

    private $filename;
    private $filepath;
    private $hash;

    public function __construct($filename) {
        CLI::verbose('New instance of Submodule spawned (File: ' . $filename . ')');
        $this->filename = $filename;
        $this->filepath = pathinfo($filename, PATHINFO_DIRNAME);
    }

}