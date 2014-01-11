<?php
require("./scripts/init.php");

//Check args
require "./scripts/checkArguments.php";

showWelcome();

$GMModular = new GMModular();
$GMModular->setProjectRoot($projectRoot);

require "./scripts/setupPaths.php";

$GMModular->setProjectFile($projectFile);
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

echo 'GMMODULAR:';
var_dump($GMModular);

echo 'GMMODULARFILE:';
var_dump($GMModularFile);