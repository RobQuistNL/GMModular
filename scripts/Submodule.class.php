<?php
class Submodule {

    /**
     * @var string
     */
    private $file;

    /**
     * @var DOMDocument
     */
    private $dom = null;

    /**
     * @var string Type of module - available or installed
     */
    public $type;

    /**
     * @var string The wole path of the project
     */
    private $filepath;

    /**
     * @var array All the assets of this submodule
     */
    private $assets;

    /**
     * @var string A unique hash made from all the files within this project to check on changes.
     */
    private $hash;

    public function __construct($filename) {
        CLI::verbose('New instance of Submodule spawned (File: ' . $filename . ')');
        $this->setFile($filename);
        $this->filepath = pathinfo($filename, PATHINFO_DIRNAME);
        $this->generateHash();
    }

    /**
     * Generate the unique hash of this submodule, based on project contents.
     */
    public function generateHash()
    {
        //@todo Check out what would be a good way to determine if something has changes, and save it as a comparable string.

        $hash = $this->getFile();

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
            $hash .= filemtime($name);
        }

        //SHA1 is very unlikely to collide.
        $this->setHash(sha1($hash));
    }


    /**
     * @param $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Get / lazy load the DOMDocument
     */
    public function getDom()
    {
        if (null == $this->dom) {
            $this->dom = $this->loadDocument();
        }

        return $this->dom;
    }

    /**
     * Set our DOMDocument
     * @param DOMDocument $dom
     */
    public function setDom(DOMDocument $dom)
    {
        $this->dom = $dom;
    }

    /**
     * Load $this->file as a DOMDocument
     */
    public function loadDocument()
    {
        if (!is_file($this->getFile())) {
            throw new Exception('"' . $this->getFile() . '" is not a file!');
        }
        $doc = new DOMDocument();
        try {
            $doc->load($this->getFile());
        } catch (Exception $e) {
            throw new Exception('Loading ' . $this->getFile() . ' was not possible. Is it corrupt, or no XML?');
        }
        return $doc;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * When used as string, return our name
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Return real name of module
     * @return string
     */
    public function getName()
    {
        return basename($this->getFile(), '.project.gmx');
    }

    /**
     * Get / lazy-load the assets from the DOMDocument
     * @return mixed
     */
    public function getAssets()
    {
        if (null == $this->assets) {
            $this->parseAssets();
        }
        return $this->assets;
    }

    /**
     * Get all child nodes with specific name (recursive)
     */
    private function getChildNodes($nodes, $name, $type)
    {
        for ($i = 0; $i < $nodes->length; $i++) {
            $item = $nodes->item($i);

            echo $item->childNodes->length;
            if ($item->childNodes->length <= 1) {
                $this->addAsset(new GMXAsset($item, $type));
            } else {
                $newDocStr = $item->ownerDocument->saveXML($item);
                $newDoc = new DOMDocument();
                $newDoc->loadXML($newDocStr);
                $xp = new DOMXPath($newDoc);
                var_dump($newDocStr);
                $this->getChildNodes($xp->query('/' . $name), $name, $type);
            }
        }
    }

    /**
     * Parse all the assets from my DOMDocument
     */
    private function parseAssets()
    {
        CLI::verbose('Started parsing assets of ' . $this->getName());
        $this->loadDocument(); //make sure the document is loaded in
        $xpath = new DOMXpath($this->getDom());

        //Sounds
        $this->getChildNodes($xpath->query('/assets/sounds/sound'), 'sound', GMXAsset::T_SOUND);
        $this->getChildNodes($xpath->query('/assets/sprites/sprite'), 'sprite', GMXAsset::T_SPRITE);
        $this->getChildNodes($xpath->query('/assets/paths/path'), 'path', GMXAsset::T_PATH);
        $this->getChildNodes($xpath->query('/assets/objects/object'), 'object', GMXAsset::T_OBJECT);

/*
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_SOUND);
            $this->addAsset($asset);
        }

        //Sprites
        $nodes = $xpath->query('/assets/sprites/sprite');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_SPRITE);
            $this->addAsset($asset);
        }

        //Backgrounds
        $nodes = $xpath->query('/assets/backgrounds');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_BACKGROUND);
            $this->addAsset($asset);
        }

        //paths
        $nodes = $xpath->query('/assets/paths');
        for ($i = 0; $i < $nodes->length; $i++) {

            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_PATH);
            $this->addAsset($asset);
        }

        //scripts
        $nodes = $xpath->query('/assets/scripts');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_SCRIPT);
            $this->addAsset($asset);
        }

        //shaders
        $nodes = $xpath->query('/assets/shaders');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_SHADER);
            $this->addAsset($asset);
        }

        //fonts
        $nodes = $xpath->query('/assets/fonts');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_FONT);
            $this->addAsset($asset);
        }

        //objects
        $nodes = $xpath->query('/assets/objects');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_OBJECT);
            $this->addAsset($asset);
        }

        //timelines
        $nodes = $xpath->query('/assets/timelines');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_TIMELINE);
            $this->addAsset($asset);
        }

        //rooms
        $nodes = $xpath->query('/assets/rooms');
        for ($i = 0; $i < $nodes->length; $i++) {
            $asset = new GMXAsset($nodes->item($i), GMXAsset::T_ROOM);
            $this->addAsset($asset);
        }
*/
        foreach ($this->getAssets() as $t) {
            CLI::verbose('ASSET: [TYPE:'.$t->getType().'] ' . $t->getLocation());
        }

        die;
    }

    /**
     * Add an asset to our submodule.
     * @param GMXAsset $asset
     */
    private function addAsset(GMXAsset $asset)
    {
        $this->assets[] = $asset;
    }
}