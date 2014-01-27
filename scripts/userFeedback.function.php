<?php
/**
 * Displays usage information
 */
function showUsage($wrongUsage = true) {
    if ($wrongUsage) {
        cli::line('Wrong usage!');
        cli::line('------------');
    }
    cli::line('How to use:');
    cli::line(' gmmodular.php [path-to-root-project] [options]');
    cli::line('   Example: php gmmodular.php G:/my/gm/folder.gmx -d -q');
    cli::line();
    cli::line(' Options:');
    cli::line('     -d                   Debug');
    cli::line('     -D                   PHP-Debug (don\'t suppress PHP errors)');
    cli::line('     -v                   Be verbose (includes -d and -D)');
    cli::line('     --dry-run            Do not touch files');
    cli::line('     --dryrun             Do not touch files');
    cli::line('     --no-color           Do not use colours in feedback');
    cli::line('     --sync               Synchronize all. This;');
    cli::line('          - Installs all new found modules');
    cli::line('          - Uninstalls all removed modules');
    cli::line('          - Synchronizes all installed modules');
    cli::line();
    cli::line('     -S                   Same as --sync');
    die;
}

/**
 * Displays welcome message
 */
function showWelcome() {
    CLI::line('Welcome to the ' . Color::str('GM','green') . Color::str('Modular', 'light_blue') . '  application.');
    CLI::line(Color::str('version ' . VERSION, 'brown'));
    CLI::line(Color::str('GMModular by Rob Quist - Licensed under Apache2 License', 'brown'));
    CLI::line();
    CLI::line('================================================================================');
    CLI::line('================================================================================');
    CLI::line();
}

/**
 * Show a menu for the user to choose from
 */
function showMenu() {
    CLI::verbose('Showing main menu');
    CLI::line();
    CLI::line('Please select an option:');
    CLI::line('    ' . Color::str('1', 'cyan') . '. Install a module');
    CLI::line('    ' . Color::str('2', 'cyan') . '. Uninstall a module');
    CLI::line('    ' . Color::str('3', 'cyan') . '. Synchronize a module');
    CLI::line('    ' . Color::str('4', 'cyan') . '. Quit');
    return (int) CLI::getLine('Option number: [1-4]', '0')-1;
}

function getMenuItem($name, $array) {
    CLI::line(Color::str(strtoupper($name) . ' MENU', 'light_green'));
    CLI::line('Available modules to ' . $name . ':');
    CLI::line('    ' . Color::str('0', 'cyan') . ' -CANCEL-');
    $i = 1;
    foreach ($array as $item) {
        CLI::line('    ' . Color::str($i, 'cyan') . ' [' . $item . ']');
        $i++;
    }
    return CLI::getLine('Select module to install [0-' . count($array) . ']', 0);
}