<?php
class GMModular {

    /**
     * @var The projects rootfolder.
     */
    private $projectRoot;

    /**
     * @var The main projects GMX file
     */
    private $projectFile;

    /**
     * @var Absolute path to folder containing submodules
     */
    private $submoduleFolder;

    /**
     * @var Array of all available submodules (as the folders tell us)
     */
    private $availableSubmodules;

    /**
     * Add an available submodule to our array
     * @param string filename
     */
    public function addAvailableModule($filename)
    {
        if (isset($this->availableSubmodules[$filename])) {
            CLI::debug('Tried to add double available module ' . $filename . ' - skipping.');
            return;
        }
        $this->availableSubmodules[$filename] = new Submodule($filename);
        $this->availableSubmodules[$filename]->type = 'available';
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
     * @return array
     */
    public function getInstalledSubmodules()
    {
        return $this->installedSubmodules;
    }

    /**
     * @param string $projectFile
     */
    public function setProjectFile($projectFile)
    {
        $this->projectFile = $projectFile;
    }

    /**
     * @return string
     */
    public function getProjectFile()
    {
        return $this->projectFile;
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

}