<?php
//@todo Implement a --clean function, to delete non-used asset files.
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
var_dump($GMModularFile);
die;
//This one returns us with the following variables:
$MDLIST_notInstalled = array();
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
                break;
            case 1: //Uninstall
                CLI::verbose('Entering uninstall menu');
                break;
            case 2: //Synchronize
                CLI::verbose('Entering sync menu');
                break;
            case 3: //Quit
                CLI::verbose('Quitting');
                die;
                break;
        }
    }
}