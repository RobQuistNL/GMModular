<?php
class GMModularFile {

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
     * @var Array of all installed submodules (as modules.gmm tells us)
     */
    private $installedSubmodules;

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
        CLI::verbose('Saving serialized object to ' . $this->file . '');
        if (DRYRUN) {
            CLI::notice('DRY-RUN IN EFFECT. ' . $this->file . ' has not been written to.');
        } else {
            file_put_contents($this->file, serialize($this));
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
     * Get version of this object
     * @return float
     */
    public function getVersion()
    {
        return $this->version;
    }


}