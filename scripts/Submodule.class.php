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
    private $assets = array();

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
        $assets = array();
        for ($i = 0; $i < $nodes->length; $i++) {
            $item = $nodes->item($i);
            if (!$item instanceof DOMText && $item->tagName == $name) {
                CLI::verbose('  Found ' . $name . ' asset.');
                $assets[] = new GMXAsset($item, $type);
            }

            if ($item->hasChildNodes() && $item->getAttribute('name') != '') {
                $assets[] = new GMXAssetFolder($item->getAttribute('name'), $this->getchildNodes($item->childNodes, $name, $type));
            }
        }
        return $assets;
    }

    /**
     * Parse all the assets from my DOMDocument
     */
    private function parseAssets()
    {
        CLI::verbose('Started parsing assets of ' . $this->getName());
        $this->loadDocument(); //make sure the document is loaded in
        $xpath = new DOMXpath($this->getDom());


        $this->addAsset(new GMXAssetFolder('sounds', $this->getChildNodes($xpath->query('/assets/sounds/*'), 'sound', GMXAsset::T_SOUND)));
        $this->addAsset(new GMXAssetFolder('sprites', $this->getChildNodes($xpath->query('/assets/sprites/*'), 'sprite', GMXAsset::T_SPRITE)));
        $this->addAsset(new GMXAssetFolder('backgrounds', $this->getChildNodes($xpath->query('/assets/backgrounds/*'), 'background', GMXAsset::T_BACKGROUND)));
        $this->addAsset(new GMXAssetFolder('paths', $this->getChildNodes($xpath->query('/assets/paths/*'), 'path', GMXAsset::T_PATH)));
        $this->addAsset(new GMXAssetFolder('scripts', $this->getChildNodes($xpath->query('/assets/scripts/*'), 'script', GMXAsset::T_SCRIPT)));
        $this->addAsset(new GMXAssetFolder('shaders', $this->getChildNodes($xpath->query('/assets/shaders/*'), 'shader', GMXAsset::T_SHADER)));
        $this->addAsset(new GMXAssetFolder('fonts', $this->getChildNodes($xpath->query('/assets/fonts/*'), 'font', GMXAsset::T_FONT)));
        $this->addAsset(new GMXAssetFolder('objects', $this->getChildNodes($xpath->query('/assets/objects/*'), 'object', GMXAsset::T_OBJECT)));
        $this->addAsset(new GMXAssetFolder('timelines', $this->getChildNodes($xpath->query('/assets/timelines/*'), 'timeline', GMXAsset::T_TIMELINE)));
        $this->addAsset(new GMXAssetFolder('rooms', $this->getChildNodes($xpath->query('/assets/rooms/*'), 'room', GMXAsset::T_ROOM)));

        foreach ($this->getAssets() as $t) {
            //CLI::verbose('ASSET: [TYPE:'.$t->getType().'] ' . $t->getLocation());
        }
    }

    /**
     * Add an asset to our submodule.
     * @param GMXAsset $asset
     */
    private function addAsset($asset)
    {
        $this->assets[] = $asset;
    }
}