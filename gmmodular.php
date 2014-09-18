#!/usr/bin/env php
<?php
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR);

//@todo Implement a --clean function, to delete non-used asset files.
//@todo make the checkModuleSync into a nice function
//@todo general cleaning up of code
//@todo shader types
//@todo Fix bug when adding / removing overlapping datafile folders in GMX file
//@todo if a datafile folder already exists, then add the files into that folder
//@todo if a datafile folder is empty after an uninstall, then remove the folder (low prio)

//@todo UNITTEST constants
//@todo UNITTEST XML stuff
//@todo UNITTEST general stuff / everything

require("scripts" . DIRECTORY_SEPARATOR . "init.php");

//Check args
require "scripts" . DIRECTORY_SEPARATOR . "checkArguments.php";

showWelcome();

$GMModular = new GMModular();
$GMModular->setProjectRoot($projectRoot);

require "scripts" . DIRECTORY_SEPARATOR . "setupPaths.php";

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
    if (count($MDLIST_notInstalled) + count($MDLIST_removed) + count($MDLIST_notSynced) == 0) {
        CLI::line(Color::str('All modules are already up to date.', 'black', 'green'));
        die;
    }
    CLI::debug('Complete automated synchronisation starting');
    CLI::line(Color::str('Automatic sync!', 'black', 'green'));
    CLI::line('Installing: ' . Color::str(count($MDLIST_notInstalled), 'green'));
    CLI::line('Removing: ' . Color::str(count($MDLIST_removed), 'green'));
    CLI::line('Sync: ' . Color::str(count($MDLIST_notSynced), 'green'));

    if (count($MDLIST_removed) >= 1) {
        CLI::line('Uninstalling removed...');
        foreach ($MDLIST_removed as $remove) {
            $GMModular->uninstallModule($GMModularFile->getInstalledModule($remove), $GMModularFile);
        }
    }

    if (count($MDLIST_notSynced) >= 1) {
        CLI::line('Removing not synced...');
        foreach ($MDLIST_notSynced as $remove) {
            $GMModular->uninstallModule($GMModularFile->getInstalledModule($remove), $GMModularFile);
        }
    }

    $MDLIST_notInstalled = array();
    $MDLIST_installed = array();
    $MDLIST_removed = array();
    $MDLIST_notSynced = array();
    require "./scripts/checkModuleSync.php";
    $GMModular->loadConstants();

    if (count($MDLIST_notInstalled) >= 1) {
        CLI::line('Installing not installed...');
        foreach ($MDLIST_notInstalled as $install) {
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
                $GMModular->loadConstants();

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
                $GMModular->loadConstants();
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
                $GMModular->loadConstants();

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
                $GMModular->loadConstants();
                break;
            case 3: //Reindex
                CLI::line(Color::str('Rescanning all files and submodules...', 'black', 'blue'));
                CLI::debug('Reindexing...');
                $MDLIST_notInstalled = array();
                $MDLIST_installed = array();
                $MDLIST_removed = array();
                $MDLIST_notSynced = array();
                require "./scripts/checkModuleSync.php";
                $GMModular->loadConstants();
                break;
            case 4: //Quit
                CLI::verbose('Quitting');
                die;
                break;
        }
    }
}
