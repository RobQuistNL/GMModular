<?php
//Find all not installed modules:
$MDLIST_notInstalled = array();
foreach ($GMModular->getAvailableSubmodules() as $available) {
    if (!in_array($available, $GMModularFile->getInstalledSubmodulesNames())) {
        CLI::debug('Module ' . $available . ' available but not in installed list.');
        $MDLIST_notInstalled[] = $available;
    }
}

if (count($MDLIST_notInstalled) > 0) {
    CLI::line('You have ' . count($MDLIST_notInstalled) . ' uninstalled module(s)!');
    $i = 0;
    foreach ($MDLIST_notInstalled as $tmpstr) {
        $i++;
        CLI::line('   #' . $i . ' - ' . $tmpstr);
    }
}

//Find all removed modules
$MDLIST_removed = array();
foreach ($GMModularFile->getInstalledSubmodulesNames() as $installed) {
    if (!in_array($installed, $GMModular->getAvailableSubmodules())) {
        CLI::debug('Module ' . $available . ' NOT available but is installed.');
        $MDLIST_removed[] = $available;
    }
}

if (count($MDLIST_removed) > 0) {
    CLI::line('You have ' . count($MDLIST_removed) . ' deleted module(s)!');
    $i = 0;
    foreach ($MDLIST_removed as $tmpstr) {
        $i++;
        CLI::line('   #' . $i . ' - ' . $tmpstr);
    }
}

//Find all out of sync modules
$MDLIST_notSynced = array();
foreach ($GMModularFile->getInstalledSubmodulesNames() as $installed => $installedValue) {
    $availableModules = $GMModular->getAvailableSubmodules();
    if (in_array($installed, $availableModules)) {
        $installedHash = $installedValue['hash'];
        $currentHash = $availableModules[(string) $installed]->getHash();
        if ($installedHash != $currentHash) {
            CLI::debug('Found out of sync module: ' . $installed);
            $MDLIST_notSynced[] = $installed;
        }
    }
}

if (count($MDLIST_notSynced) > 0) {
    CLI::line('You have ' . count($MDLIST_notSynced) . ' out-of-sync module(s)!');
    $i = 0;
    foreach ($MDLIST_notSynced as $tmpstr) {
        $i++;
        CLI::line('   #' . $i . ' - ' . $tmpstr);
    }
}