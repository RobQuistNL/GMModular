<?php
class GMModular {
    /**
     * @todo I'd rather use a trait for the File / DOMDocument stuff...
     * But traits are only supported from PHP5.4+, so we'll stick to this double-coding solution for
     * compatibility reasons.
     */

    /**
     * @var string
     */
    private $file;

    /**
     * @var DOMDocument
     */
    private $dom = null;

    /**
     * @var The projects rootfolder.
     */
    private $projectRoot;

    /**
     * @var Absolute path to folder containing submodules
     */
    private $submoduleFolder;

    /**
     * @var Array of all available submodules (as the folders tell us)
     */
    private $availableSubmodules;

    /**
     * Merge the given submodule into our loaded main project.
     * We will also add the submodule to the GMModularFile, and save it.
     * @param Submodule $submodule
     * @param GMModularFile $GMModularFile
     */
    public function installModule(Submodule $submodule, GMModularFile $GMModularFile)
    {
        CLI::verbose('Starting installation of module ' . $submodule);
        //$projectDocument = $this->getDom();

        $submoduleAssets = $submodule->getAssets();

        if (DEBUG) {
            CLI::debug('Assets found in module ' . $submodule . ':');
            $this->dumpAssets($submoduleAssets);
        }

        $parentNode = $this->getDom()->getElementsByTagName('assets')->item(0);
        foreach ($submoduleAssets as $asset) {
            if ($asset instanceof GMXAssetFolder) { //We have to create our <MODULE> folder first

                //Check if we even have stuff in there
                if (count($asset->children) >= 1) {
                    //Check if the main folder already exists (e.g. there are no fonts in main project)
                    $instanceType = $parentNode->getElementsByTagName($this->getParentNodeName($asset->type))->item(0);
                    if ($instanceType == null) {
                        $instanceType = $this->getDom()->createElement($this->getParentNodeName($asset->type));
                        $instanceType->setAttribute('name', $this->getParentNodeName($asset->type));
                        $parentNode->appendChild($instanceType);
                    }

                    //Create our submodule folder in there and add the assets
                    $newAsset = $this->getDom()->createElement($this->getParentNodeName($asset->type));
                    $newAsset->setAttribute('name', $submodule->getName());
                    $this->appendAssets($asset->children, $newAsset);

                    $instanceType->appendChild($newAsset);
                }
            } else {
                //$newAsset = new DOMElement($this->getAssetNodeName($asset['type']), $asset->getLocation());
                throw new Exception('FOUND A GENERAL ASSET ('.$asset['node']->getLocation().') ON 0-LEVEL! WTF!');
            }
        }
        $xml = $this->getDom()->saveXML();
        echo 'new doc:' . PHP_EOL . $xml;

        //$this->writeAssets($submoduleAssets, $projectDocument);
        var_dump($submoduleAssets);
        //var_dump($projectDocument);
        die;
        //$GMModularFile->installModule($submodule);

    }

    public function appendAssets(array $submoduleAssets, DOMNode $parentNode) {
        foreach ($submoduleAssets as $asset) {
            if ($asset instanceof GMXAssetFolder) {
                $newAsset = new DOMElement($this->getParentNodeName($asset->type));
                $parentNode->appendChild($newAsset);
                $newAsset->setAttribute('name', $asset->name);
                $this->appendAssets($asset->children, $newAsset);
            } else {
                $newAsset = new DOMElement($this->getAssetNodeName($asset->getType()), $asset->getLocation());
                $parentNode->appendChild($newAsset);
            }
        }
    }

    /**
     * get the root node name of a specific asset type
     * @param int $type
     * @return string
     */
    public function getParentNodeName($type)
    {
        switch ($type) {
            case GMXAsset::T_DATAFILE:
                return 'datafiles';
                break;
            case GMXAsset::T_SOUND:
                return 'sounds';
                break;
            case GMXAsset::T_SPRITE:
                return 'sprites';
                break;
            case GMXAsset::T_BACKGROUND:
                return 'backgrounds';
                break;
            case GMXAsset::T_PATH:
                return 'paths';
                break;
            case GMXAsset::T_SCRIPT:
                return 'scripts';
                break;
            case GMXAsset::T_SHADER:
                return 'shaders';
                break;
            case GMXAsset::T_FONT:
                return 'fonts';
                break;
            case GMXAsset::T_OBJECT:
                return 'objects';
                break;
            case GMXAsset::T_TIMELINE:
                return 'timelines';
                break;
            case GMXAsset::T_ROOM:
                return 'rooms';
                break;
            default:
                return 'assets'; //Just... general.
                break;
        }
    }


    /**
     * get the root node name of a specific asset type
     * @param int $type
     * @return string
     */
    public function getAssetNodeName($type)
    {
        switch ($type) {
            case GMXAsset::T_DATAFILE:
                return 'datafile';
                break;
            case GMXAsset::T_SOUND:
                return 'sound';
                break;
            case GMXAsset::T_SPRITE:
                return 'sprite';
                break;
            case GMXAsset::T_BACKGROUND:
                return 'background';
                break;
            case GMXAsset::T_PATH:
                return 'path';
                break;
            case GMXAsset::T_SCRIPT:
                return 'script';
                break;
            case GMXAsset::T_SHADER:
                return 'shader';
                break;
            case GMXAsset::T_FONT:
                return 'font';
                break;
            case GMXAsset::T_OBJECT:
                return 'object';
                break;
            case GMXAsset::T_TIMELINE:
                return 'timeline';
                break;
            case GMXAsset::T_ROOM:
                return 'room';
                break;
            default:
                return 'GMMODULAR'; //Just... general.
                break;
        }
    }

    /**
     * Debug function to see a tree(ish) overview of all assets
     * @param $array
     * @param int $depth
     */
    public function dumpAssets($array, $depth=0)
    {
        foreach ($array as $ass) {
            if ($ass instanceof GMXAssetFolder) {
                CLI::debug(str_repeat('   ', $depth+1) . 'L' . $ass->name);
                $this->dumpAssets($ass->children, $depth+1);
            } else {
                CLI::debug(str_repeat('   ', $depth+1) . 'L' . $ass->getLocation());
            }
        }
    }

    /**
     * Add an available submodule to our array
     * @param string filename
     */
    public function addAvailableModule($filename)
    {
        $pointer = basename($filename, '.project.gmx');
        if (isset($this->availableSubmodules[$pointer])) {
            CLI::debug('Tried to add double available module ' . $pointer . ' - skipping.');
            return;
        }
        $this->availableSubmodules[$pointer] = new Submodule($filename);
        $this->availableSubmodules[$pointer]->type = 'available';
    }

    /**
     * GETTERS AND SETTERS
     */

    /**
     * @return array
     */
    public function getAvailableSubmodules()
    {
        return $this->availableSubmodules;
    }

    /**
     * @param string $projectFile
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
     * @param string $projectRoot
     */
    public function setProjectRoot($projectRoot)
    {
        /** Directory checks */
        CLI::verbose('Converting "' . $projectRoot . '" to real path.');
        $projectRoot = realpath($projectRoot);
        CLI::verbose('Checking if "' . $projectRoot . '" is a directory.');
        if (!is_dir($projectRoot)) {
            CLI::fatal($projectRoot . ' is not a directory.');
        }
        $this->projectRoot = $projectRoot;
    }

    /**
     * @return string
     */
    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    /**
     * @param string $submoduleFolder
     */
    public function setSubmoduleFolder($submoduleFolder)
    {
        $this->submoduleFolder = $submoduleFolder;
    }

    /**
     * @return string
     */
    public function getSubmoduleFolder()
    {
        return $this->submoduleFolder;
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
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;
        try {
            $doc->load($this->getFile());
        } catch (Exception $e) {
            throw new Exception('Loading ' . $this->getFile() . ' was not possible. Is it corrupt, or no XML?');
        }

        return $doc;
    }

}