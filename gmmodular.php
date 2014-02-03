<?php
//@todo Implement a --clean function, to delete non-used asset files.
//@todo make the checkModuleSync into a nice function
//@todo general cleaning up of code
//@todo shader types
//@todo Fix bug when adding / removing overlapping datafile folders in GMX file
//@todo Fix bug where constants are not being reindexed after install / update
//@todo fix bug when after a few uninstall / installs i get fatal errors on the GMX Document;
// PHP Fatal error:  Call to a member function removeChild() on a non-object in /cygdrive/g/GMSTUDIO/GIT/GMModular/scripts/GMModular.class.php on line 148


//@todo UNITTEST constants
//@todo UNITTEST XML stuff
//@todo UNITTEST general stuff / everything

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

//$GMModular->dumpAssets($MDLIST_notInstalled[1]->getAssets());
//$GMModular->installModule($MDLIST_notInstalled[1], $GMModularFile);
//die;

CLI::verbose('Modules checked. Checking user input...');
//Now check the user input.
if (SYNC) { //Synchronize all modules.
    if (count($MDLIST_notInstalled) + count($MDLIST_removed) + count($MDLIST_notSynced) == 0) {
        CLI::line(Color::str('All modules are already up to date.', 'black', 'green'));
        die;
    }
    CLI::debug('Complete automated synchronisation starting');
    CLI::line(Color::str('Automatic sync!', 'black', 'green'));
    CLI::line('Installing: ' . Color::str(count($MDLIST_notInstalled), 'green'));
    CLI::line('Removing: ' . Color::str(count($MDLIST_removed), 'green'));
    CLI::line('Sync: ' . Color::str(count($MDLIST_notSynced), 'green'));

    if (count($MDLIST_notInstalled) >= 1) {
        CLI::line('Installing not installed...');
        foreach ($MDLIST_notInstalled as $install) {
            $GMModular->installModule($install, $GMModularFile);
        }
    }

    if (count($MDLIST_removed) >= 1) {
        CLI::line('Uninstalling removed...');
        foreach ($MDLIST_removed as $remove) {
            $GMModular->uninstallModule($GMModularFile->getInstalledModule($remove), $GMModularFile);
        }
    }

    if (count($MDLIST_notSynced) >= 1) {
        CLI::line('Syncing not synced...');
        foreach ($MDLIST_notSynced as $remove) {
            $GMModular->uninstallModule($GMModularFile->getInstalledModule($remove), $GMModularFile);
        }

        foreach ($MDLIST_notSynced as $install) {
            $GMModular->installModule($install, $GMModularFile);
        }
    }

    CLI::line(Color::str('All submodules successfully synchronized!', 'black', 'green'));
    CLI::verbose('Quitting');
    die;
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
            case 3: //Reindex
                CLI::line(Color::str('Rescanning all files and submodules...', 'black', 'blue'));
                CLI::debug('Reindexing...');
                $MDLIST_notInstalled = array();
                $MDLIST_installed = array();
                $MDLIST_removed = array();
                $MDLIST_notSynced = array();
                require "./scripts/checkModuleSync.php";
                break;
            case 4: //Quit
                CLI::verbose('Quitting');
                die;
                break;
        }
    }
}