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
     * Check if the given asset file exists. A Assetfolder will always return false.
     * @param $assetFile
     * @return bool
     */
    public function checkDoubleAssets($assetFile)
    {
        $doubleAssets = array();
        if ($assetFile instanceof GMXAsset) {
            $doubleAssets = array_merge($doubleAssets, $this->checkAssetExists($assetFile));
        } else if ($assetFile instanceof GMXAssetFolder) {
            foreach ($assetFile->children as $asset) {
                $doubleAssets = array_merge($doubleAssets, $this->checkDoubleAssets($asset));
            }
        } else {
            if (is_array($assetFile)) {
                foreach ($assetFile as $asset) {
                    $doubleAssets = array_merge($doubleAssets, $this->checkDoubleAssets($asset));
                }
            } else {
                var_dump($assetFile);
                throw new Exception('Unhandled type!');
            }
        }
        return $doubleAssets;
    }

    /**
     * Check if the given asset file exists.
     * @param GMXAsset $assetFile
     * @return array Filled with double assets
     */
    public function checkAssetExists(GMXAsset $assetFile)
    {
        $file = $this->projectRoot . DS . $assetFile->getLocation();
        $file = str_replace('\\', DS, $file) . $assetFile->getFileExt();
        $exists = file_exists($file);
        CLI::verbose('Check if file [' . $file . '] exists: ' . (int) $exists);
        if ($exists) {
            CLI::debug('Double asset found: ' . $file);
        }
        if ($exists) {
            return array($file);
        } else {
            return array();
        }
    }

    /**
     * Merge the given submodule into our loaded main project.
     * We will also add the submodule to the GMModularFile, and save it.
     * @param array $submodule
     * @param GMModularFile $GMModularFile
     */
    public function uninstallModule($submoduleArray, GMModularFile $GMModularFile)
    {
        $submodule = $submoduleArray['class'];
        CLI::verbose('Starting uninstallation of module ' . $submodule);

        $submoduleAssets = $submodule->getAssets();

        if (DEBUG) {
            CLI::debug('Assets found in module ' . $submodule . ':');
            $this->dumpAssets($submoduleAssets);
        }

        /*
         * Loop through all the assets in this submodule and REMOVE them from the DOMDocument.
         */
        CLI::debug('Removing module project file out of root project file...');
        $parentNode = $this->getDom()->getElementsByTagName('assets')->item(0);
        foreach ($submoduleAssets as $asset) {
            if ($asset instanceof GMXAssetFolder) { //We have to create our <MODULE> folder first

                //Check if we even have stuff in there
                if (count($asset->children) >= 1) {
                    //Select the mainfolder
                    $instanceType = $parentNode->getElementsByTagName($this->getParentNodeName($asset->type))->item(0);

                    //Check our submodules' child folder
                    $newAsset = $this->getDom()->createElement($this->getParentNodeName($asset->type));
                    $newAsset->setAttribute('name', $submodule->getName());
                    $instanceType->removeChild($newAsset); //and delete it
                }
            } else {
                throw new Exception('FOUND A GENERAL ASSET ('.$asset['node']->getLocation().') ON 0-LEVEL! Can\'t be right!');
            }
        }
        $xml = $this->getDom()->saveXML();
        CLI::debug('New XML file generated.');

        CLI::debug('Removing game asset files.');
        foreach ($submoduleArray['files'] as $file) {
            if (DRYRUN) {
                CLI::notice('DRYRUN: delete ' . $file);
            } else {

                unlink(str_replace('\\', DS, realpath($submoduleLocation) . DS . $file));
            }
        }
        CLI::notice('Deleted ' . count($submoduleArray['files']) . ' files.');
        $GMModularFile->removeModule($submodule->getName());

        CLI::debug('Saving GMModular file.');
        if (DRYRUN) {
            CLI::notice('DRYRUN: Write new module file');
        } else {
            $GMModularFile->save();
        }
        CLI::debug('Backing up old project file.');
        if (DRYRUN) {
            CLI::notice('DRYRUN: Copy backup of main project');
        } else {
            copy(
                realpath($this->getFile()),
                pathinfo(realpath($this->getFile()), PATHINFO_DIRNAME) . DS  . time() . '.project.backup.gmx'
            );
        }
        CLI::debug('Saving new project file.');
        if (DRYRUN) {
            CLI::notice('DRYRUN: Overwrite main project file with new XML');
        } else {
            file_put_contents(realpath($this->getFile()), $xml);
        }

    }

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

        if ($this->runDoubleAssetCheck($submoduleAssets) == false) { //Check for double files / ask user what to do.
            //False means, don't continue.
            return false;
        }

        /*
         * Loop through all the assets in this submodule and add them to the DOMDocument.
         */
        CLI::debug('Combining module project file into root project file...');
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
                throw new Exception('FOUND A GENERAL ASSET ('.$asset['node']->getLocation().') ON 0-LEVEL! Can\'t be right!');
            }
        }
        $xml = $this->getDom()->saveXML();
        CLI::debug('New XML file generated.');

        CLI::debug('Copying game asset files.');
        $copied = $this->copyAssetFiles($submoduleAssets, $submodule->getFilepath());
        CLI::notice('Copied ' . count($copied) . ' files.');
        $GMModularFile->installModule($submodule, $copied);

        CLI::debug('Saving GMModular file.');
        if (DRYRUN) {
            CLI::notice('DRYRUN: Write new module file');
        } else {
            $GMModularFile->save();
        }
        CLI::debug('Backing up old project file.');
        if (DRYRUN) {
            CLI::notice('DRYRUN: Copy backup of main project');
        } else {
            copy(
                realpath($this->getFile()),
                pathinfo(realpath($this->getFile()), PATHINFO_DIRNAME) . DS  . time() . '.project.backup.gmx'
            );
        }
        CLI::debug('Saving new project file.');
        if (DRYRUN) {
            CLI::notice('DRYRUN: Overwrite main project file with new XML');
        } else {
            file_put_contents(realpath($this->getFile()), $xml);
        }

    }

    /**
     * Check for double asset names, and if they occur, ask the user what to do.
     * @return bool
     */
    public function copyAssetFiles($assetFile, $submoduleLocation)
    {
        $copied = array();
        if ($assetFile instanceof GMXAsset) {
            $copied = array_merge($assetFile->copyAsset($this->projectRoot, $submoduleLocation), $copied);
        } else if ($assetFile instanceof GMXAssetFolder) {
            foreach ($assetFile->children as $asset) {
                $copied = array_merge($this->copyAssetFiles($asset, $submoduleLocation), $copied);
            }
        } else {
            if (is_array($assetFile)) {
                foreach ($assetFile as $asset) {
                    $copied = array_merge($this->copyAssetFiles($asset, $submoduleLocation), $copied);
                }
            } else {
                CLI::warning('Unknown asset file found in submodule. We will try to continue.');
                CLI::warning('Asset file dump:');
                var_dump($assetFile);
            }
        }
        return $copied;
    }

    /**
     * Check for double asset names, and if they occur, ask the user what to do.
     * @return bool
     */
    public function runDoubleAssetCheck($submoduleAssets)
    {
        CLI::debug('Starting double asset check');
        $doubleAssets = $this->checkDoubleAssets($submoduleAssets);
        if (count($doubleAssets) >= 1) {
            CLI::warning('Double asset names have been found:');
            foreach ($doubleAssets as $doubleName) {
                CLI::warning('    ' . $doubleName);
            }
            CLI::warning('You can continue, but existing assets WILL BE OVERWRITTEN!');
            if (CLI::getYesNo('Overwrite ' . count($doubleAssets) . ' game assets?', 'n') == false) {
                CLI::notice('Not overwriting game assets.');
                if (DRYRUN) {
                    CLI::notice('Script will continue because of --dryrun tag!');
                    return true;
                } else {
                    return false;
                }
            } else {
                CLI::warning('Overwriting game assets!');
                return true;
            }
        }
        CLI::debug('No conflicting / double asset names have been found. Continuing script!');
        return true; //Default, no doubles have been found so we can continue.
    }

    /**
     * Appending assets to an existing (new) node. Allows for recursion.
     * @param array $submoduleAssets
     * @param DOMNode $parentNode
     */
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