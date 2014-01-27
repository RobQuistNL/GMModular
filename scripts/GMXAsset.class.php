<?php
class GMXAsset {

    const T_DATAFILE    = 0;
    const T_SOUND       = 1;
    const T_SPRITE      = 2;
    const T_BACKGROUND  = 3;
    const T_PATH        = 4;
    const T_SCRIPT      = 5;
    const T_SHADER      = 6;
    const T_FONT        = 7;
    const T_OBJECT      = 8;
    const T_TIMELINE    = 9;
    const T_ROOM        = 10;
    //We're skipping;
    // * Configs
    // * Helpfile
    // * TutorialState

    //@todo extra info for shader (type)
    //@todo included files
    //@todo constants

    /**
     * @var string
     */
    public $type;

    /**
     * @var DOMElement
     */
    private $node;

    public function __construct(DOMElement $node = null, $type = null)
    {
        if ($node != null) {
            $this->setNode($node);
        }
        if ($type != null) {
            $this->setType($type);
        }
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setNode(DOMElement $node)
    {
        $this->node = $node;
    }

    public function getLocation()
    {
        $this->checkNode();
        $return = trim($this->node->textContent); //if there are child nodes (like folders)
        $return = explode("\r", $return);
        $return = trim($return[0]);
        return $return;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getNode()
    {
        $this->checkNode();
        return $this->node;
    }

    public function checkNode()
    {
        if (null == $this->node) {
            throw new Exception('Node not set in GMXAsset!');
        }
    }

    /**
     * Get the file extension for this specific game asset
     * @return string
     */
    public function getFileExt()
    {
        switch ($this->getType()) {
            case GMXAsset::T_DATAFILE:
            case GMXAsset::T_SHADER:
            case GMXAsset::T_SCRIPT:
                return ''; //uses its own file extension. Thank god for consistency, YYG!
                break;
            case GMXAsset::T_SOUND:
                return '.sound.gmx';
                break;
            case GMXAsset::T_SPRITE:
                return '.sprite.gmx';
                break;
            case GMXAsset::T_BACKGROUND:
                return '.background.gmx';
                break;
            case GMXAsset::T_PATH:
                return '.path.gmx';
                break;
            case GMXAsset::T_FONT:
                return '.font.gmx';
                break;
            case GMXAsset::T_OBJECT:
                return '.object.gmx';
                break;
            case GMXAsset::T_TIMELINE:
                return '.timeline.gmx';
                break;
            case GMXAsset::T_ROOM:
                return '.room.gmx';
                break;
            default:
                throw new Exception('Unknown GMXAsset type ' . $this->getType() . '! File:' . $this->getLocation());
                break;
        }
    }

    /**
     * Copy all my asset files and related files to the directory.
     * @param string $projectRoot
     * @param string $submoduleFolder The name of the submodule folder
     */
    public function copyAsset($projectRoot, $submoduleLocation)
    {
        $files = $this->getFilesToCopy($submoduleLocation);
        foreach ($files as $file) {
            $source = str_replace('\\', DS, realpath($submoduleLocation) . DS . $file);
            $target = str_replace('\\', DS, realpath($projectRoot) . DS . $file);
            if (DRYRUN) {
                CLI::notice('DRYRUN: Copy ' . $source . ' -> ' . $target);
            }

        }

    }

    /**
     * Returns a multi-dimensional array filled with origin and location for files.
     */
    public function getFilesToCopy($submoduleLocation)
    {
        $files = array();
        $files[] = $this->getAssetFile(); //Always our own asset

        if ($this->getType() == GMXAsset::T_DATAFILE ||
            $this->getType() == GMXAsset::T_SHADER ||
            $this->getType() == GMXAsset::T_SCRIPT) {
            return $files;
        }

        // Check all the extra files we need to copy.
        $gmmod = new GMModular();
        $dom = $this->getAssetDom($submoduleLocation);
        $xpath = new DOMXPath($dom);
        switch ($this->getType()) {
            case GMXAsset::T_SOUND:
                $assetFile = $xpath->query('/sound/data')->item(0)->textContent;
                $files[] = 'sound' . DS . 'audio' . DS . trim(CLI::fixDS($assetFile));
                break;
            case GMXAsset::T_SPRITE:
                $assetFile = $xpath->query('/sprite/frames/frame');
                for ($i = 0; $i < $assetFile->length; $i++) {
                    $filename = $assetFile->item($i)->textContent;
                    $files[] = 'sprites' . DS . trim(CLI::fixDS($filename));
                }
                break;
            case GMXAsset::T_BACKGROUND:
                return '.background.gmx';
                break;
            case GMXAsset::T_PATH:
                return '.path.gmx';
                break;
            case GMXAsset::T_FONT:
                return '.font.gmx';
                break;
            case GMXAsset::T_OBJECT:
                return '.object.gmx';
                break;
            case GMXAsset::T_TIMELINE:
                return '.timeline.gmx';
                break;
            case GMXAsset::T_ROOM:
                return '.room.gmx';
                break;
        }

        return $files;
    }

    /**
     * Get the DOMDocument of the asset file
     * @return DOMDocument
     */
    public function getAssetDom($submoduleLocation)
    {
        $doc = new DOMDocument();
        $doc->loadXML(file_get_contents(realpath($submoduleLocation) . DS . $this->getAssetFile()));
        return $doc;
    }

    /**
     * Get the parents node name (e.g. sprites, datafiles, sounds etc.)
     * @return string
     */
    public function getParentNodeName()
    {
        $gmmod = new GMModular();
        return $gmmod->getParentNodeName($this->getType());
    }

    public function getAssetFile()
    {
        return CLI::fixDS($this->getLocation() . $this->getFileExt());
    }
}