<?php
class GMModularFile {
    /**
     * @todo I'd rather use a trait. But traits are only supported from PHP5.4+, so we'll stick to this solution for
     * compatibility reasons.
     */

    /**
     * The GMModular version this file was made with
     * @var float version
     */
    private $version = VERSION;

    /**
     * @var The name of the modules.gmm file
     */
    private $file;

    /**
     * @var string The last time the file was saved
     */
    private $lastEdited;

    /**
     * @var Array of all installed submodules (as modules.gmm tells us)
     */
    private $installedSubmodules = array();

    /**
     * Constructor. Optionally also sets the filename
     * @param string $filename
     */
    public function __construct($filename = null)
    {
        if (null != $filename) {
            $this->setFile($filename);
        }
    }

    /**
     * Load settings from the selected gmmodular file
     */
    public function load() {
        if (null == $this->file) {
            throw new Exception('No module file set!');
        }
        CLI::verbose('Loading and unserializing ' . $this->file);
        $newObject = unserialize(file_get_contents($this->file));
        if ($newObject->getVersion() != VERSION) {
            CLI::warning('This gmmodular file was made with another version; file: ' . $newObject->getVersion() . ' - Current: ' . VERSION);
        }
        $newObject->setFile($this->file);
        return $newObject;
    }

    /**
     * Save all settings to the selected gmmodular file
     * @return $this
     * @throws Exception
     */
    public function save() {
        if (null == $this->file) {
            throw new Exception('No module file set!');
        }

        $this->lastEdited = time();
        CLI::verbose('Saving serialized object to ' . $this->file . '');
        if (DRYRUN) {
            CLI::notice('DRY-RUN IN EFFECT. ' . $this->file . ' has not been written to.');
        } else {
            file_put_contents(realpath($this->file), serialize($this));
            CLI::debug('New data written to ' . $this->file . '');
        }

        return $this;
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
     * @return array
     */
    public function getInstalledSubmodules()
    {
        return $this->installedSubmodules;
    }

    /**
     * Return an array with just names of installed stuffs
     * @return array
     */
    public function getInstalledSubmodulesNames()
    {
        $arr = array();
        foreach ($this->installedSubmodules as $key => $value) {
            $arr[] = $key;
        }
        return $arr;
    }

    /**
     * Install a specific submodule into gmmodular.gmm and a list of the copied files (for uninstallation)
     * @param $submodule
     * @param $copied
     */
    public function installModule($submodule, $copied)
    {
        $this->installedSubmodules[$submodule->getName()]['files'] = $copied;
        $this->installedSubmodules[$submodule->getName()]['hash'] = $submodule->getHash();
        $this->installedSubmodules[$submodule->getName()]['date'] = time();
    }

    /**
     * Remove a specific submodule from the gmmodular file. Be sure to first remove the files!
     * @param $modulename
     * @return void
     */
    public function removeModule($modulename)
    {
        unset($this->installedModules[$modulename]);
    }

    /**
     * Get the settings of an installed module.
     * @param $modulename
     * @return null | array
     */
    public function getInstalledModule($modulename)
    {
        if (!isset($this->installedModules[$modulename])) {
            return null;
        }
        return $this->installedModules[$modulename];
    }

    /**
     * Get version of this object
     * @return float
     */
    public function getVersion()
    {
        return $this->version;
    }


}