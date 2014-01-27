<?php
//@todo Implement a --clean function, to delete non-used asset files.
//@todo make the checkModuleSync into a nice function
//@todo general cleaning up
//@todo datafiles / settings
//@todo constants
//@todo add manual reindex while in menu
//@todo fix the --sync functionality to install && sync all modules

require("./scripts/init.php");

//Check args
require "./scripts/checkArguments.php";

showWelcome();

$GMModular = new GMModular();
$GMModular->setProjectRoot($projectRoot);

require "./scripts/setupPaths.php";

$GMModular->setFile($projectFile);
$GMModular->setSubmoduleFolder($submoduleFolder);

$GMModularFile = new GMModularFile($submoduleFile);
if (false == $gmmfile) {
    if (CLI::getLine('Since there was no module file found, would you like to create a new one? [y/n]', 'n') == 'n') {
        CLI::line('Not creating file. We can\'t continue!');
        die;
    }
    $GMModularFile->save();
} else {
    CLI::verbose('Loading module file in to gmmodular instance');
    $GMModularFile = $GMModularFile->load();
}

//This one returns us with the following variables:
$MDLIST_notInstalled = array();
$MDLIST_installed = array();
$MDLIST_removed = array();
$MDLIST_notSynced = array();
require "./scripts/checkModuleSync.php";

CLI::verbose('Modules checked. Checking user input...');
//Now check the user input.
if (SYNC) { //Synchronize all modules.
    CLI::debug('Complete automated synchronisation starting');
    //@todo
} else { //Prompt user what to do.
    while (true) { //Keep looping until user presses CTRL+C or quits.
        switch(showMenu()) {
            case 0: //Install
                CLI::verbose('Entering install menu');
                $selected = getMenuItem('install', $MDLIST_notInstalled);
                if ($selected == 0) { //Cancel item
                    continue;
                }
                $GMModular->installModule($MDLIST_notInstalled[$selected-1], $GMModularFile);
                CLI::debug('Reindexing...');
                $MDLIST_notInstalled = array();
                $MDLIST_installed = array();
                $MDLIST_removed = array();
                $MDLIST_notSynced = array();
                require "./scripts/checkModuleSync.php";

                break;
            case 1: //Uninstall
                CLI::verbose('Entering uninstall menu');
                $selected = getMenuItem('uninstall', $MDLIST_installed);
                if ($selected == 0) { //Cancel item
                    continue;
                }
                $GMModular->uninstallModule($GMModularFile->getInstalledModule($MDLIST_installed[$selected-1]), $GMModularFile);
                CLI::debug('Reindexing...');
                $MDLIST_notInstalled = array();
                $MDLIST_installed = array();
                $MDLIST_removed = array();
                $MDLIST_notSynced = array();
                require "./scripts/checkModuleSync.php";
                break;
            case 2: //Synchronize
                CLI::verbose('Entering sync menu');
                $selected = getMenuItem('synchronize', $MDLIST_notSynced);
                if ($selected == 0) { //Cancel item
                    continue;
                }
                $moduleName = $MDLIST_notSynced[$selected-1];
                ob_start();
                $GMModular->uninstallModule($GMModularFile->getInstalledModule($moduleName), $GMModularFile);
                CLI::debug('Reindexing...');
                $MDLIST_notInstalled = array();
                $MDLIST_installed = array();
                $MDLIST_removed = array();
                $MDLIST_notSynced = array();
                require "./scripts/checkModuleSync.php";

                foreach ($MDLIST_notInstalled as $test) {
                    if ($test->__toString() == $moduleName) {
                        $GMModular->installModule($test, $GMModularFile);
                    }
                }
                CLI::debug(ob_get_clean());
                CLI::line(Color::str('Submodule ' . $moduleName . ' successfully synchronized!', 'black', 'green'));
                CLI::debug('Reindexing...');
                $MDLIST_notInstalled = array();
                $MDLIST_installed = array();
                $MDLIST_removed = array();
                $MDLIST_notSynced = array();
                require "./scripts/checkModuleSync.php";
                break;
            case 3: //Quit
                CLI::verbose('Quitting');
                die;
                break;
        }
    }
}