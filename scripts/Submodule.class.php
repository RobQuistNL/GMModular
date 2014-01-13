<?php
class Submodule {

    public $type; //available or installed

    /**
     * @var string The name of the GMX file
     */
    private $filename;

    /**
     * @var string The wole path of the project
     */
    private $filepath;

    /**
     * @var string A unique hash made from all the files within this project to check on changes.
     */
    private $hash;

    public function __construct($filename) {
        CLI::verbose('New instance of Submodule spawned (File: ' . $filename . ')');
        $this->filename = $filename;
        $this->filepath = pathinfo($filename, PATHINFO_DIRNAME);
        $this->generateHash();
    }

    /**
     * Generate the unique hash of this submodule, based on project contents.
     */
    public function generateHash()
    {
        //@todo this isn't really efficient... But its precise!
        $hash = base64_encode($this->filename);

        $objects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->filepath),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach($objects as $name => $object){
            if (!is_file($name)) {
                continue; //Skip non-files
            }
            if (false !== strpos($name, '/Configs/')) {
                continue; //Skip Configs folder, since we're not including that in module management.
            }
            //$hash .= '-' . md5(file_get_contents($name)); //Very good, but very slow
            $hash .= filemtime($name) . '-';
        }
        $this->setHash($hash);
    }

    /**
     * @param $hash
     */
    public function setHash($hash)
    {
        //@todo find a good way to compress this data. gzip doesn't seem to work on windows by default
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    public function __toString()
    {
        return basename($this->filename, '.project.gmx');
    }
}