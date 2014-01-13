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
        $moduleDocument = $submodule->getDom();
        $projectDocument = $this->getDom();

        var_dump($moduleDocument);
        var_dump($projectDocument);
        die;
        //$GMModular->installModule($MDLIST_notInstalled[$selected-1], $GMModularFile);
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
        try {
            $doc->load($this->getFile());
        } catch (Exception $e) {
            throw new Exception('Loading ' . $this->getFile() . ' was not possible. Is it corrupt, or no XML?');
        }
        return $doc;
    }

}