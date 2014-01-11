<?php

function searchForGMXFile($directory) {
    CLI::verbose('Opening "' . $directory . '". Contents:');
    if ($handle = opendir($directory)) {
        $projectFile = false;
        while (false !== ($entry = readdir($handle))) {
            CLI::verbose('    ' . $entry);
            if ($entry != '.' && $entry != '..') {
                $ext = pathinfo($entry, PATHINFO_EXTENSION);
                if ($ext == 'gmx' && is_file($directory . DS .$entry)) {
                    $projectFile = $directory . DS .$entry;
                    CLI::debug('Found project file: ' . $directory . DS . $entry);
                }
            }
        }
        closedir($handle);
    }
    return $projectFile;
}

CLI::debug('Searching for project file.');
CLI::verbose('Opening "' . $projectRoot . '". Contents:');

$projectFile = searchForGMXFile($projectRoot);

if (false === $projectFile) {
    CLI::fatal('No project file found in ' . $projectRoot . '. Make sure it is the project root!');
}

CLI::verbose('Checking if "' . $projectFile . '" is writeable.');
if (!is_writeable($projectFile)) {
    if (DRYRUN) {
        CLI::warning($projectFile . ' is not writable, but --dry-run flag was set so we will continue.');
    } else {
        CLI::fatal($projectFile . ' is not writeable.');
    }
}

$submoduleFolder = $projectRoot . DS . C_SUBMODULEFOLDER;
$submoduleFile = $projectRoot . DS . C_SUBMODULEFOLDER . DS . C_MODULEFILE;

CLI::debug('Checking for submodule folder in "' . $projectRoot . '".');
if (!is_dir($submoduleFolder)) {
    CLI::fatal('There was no submodules folder found. If you want to add a submodule, check the documentation.');
} else {
    CLI::debug('Submodule folder found');
    if (file_exists($submoduleFile)) {
        CLI::debug('GMModular file found');
        if (!is_writeable($submoduleFile)) {
            if (DRYRUN) {
                CLI::warning($submoduleFile . ' is not writable, but --dry-run flag was set so we will continue.');
            } else {
                CLI::fatal($submoduleFile . ' is not writeable.');
            }
        }
        $gmmfile = true;
    } else {
        $gmmfile = false;
        CLI::warning('There was a submodule folder found, but no modulefile! (' . C_MODULEFILE .
            '). If this is the first time running this script, you\'re okay, since it has not been made yet. ' .
            'If this isn\'t the first time... You\'d better find the file or clean up your main project manually!');
    }
}

CLI::debug('Checking for all submodules in folder');
CLI::verbose('Opening "' . $submoduleFolder . '". Contents:');
if ($handle = opendir($submoduleFolder)) {
    while (false !== ($entry = readdir($handle))) {
        CLI::verbose('    ' . $entry);
        if ($entry != '.' && $entry != '..') {
            if (is_dir($submoduleFolder . DS . $entry)) {
                //Open up this folder to see if there's a GMX file in there.
                $smDir = $submoduleFolder . DS .$entry;
                $smFile = searchForGMXFile($smDir);
                if (false !== $smFile) {
                    CLI::debug('Adding available submodule: ' . $smFile);
                    $GMModular->addAvailableModule($smFile);
                }
            }
        }
    }
    closedir($handle);
}



//$test = new DOMDocument();
//$test->load($projectFile);
//var_dump($test);

//echo $test->saveXML();
//echo file_get_contents($projectFile);
//$projectXml = simplexml_load_string($projectFile);
//$projectJson = json_encode($projectXml);
//$projectArray = json_decode($projectJson,TRUE);

//var_dump($projectArray);

//$_GM['submodules']['folders']
//$_GM['submodules']['used']


