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
    cli::line('     -v                   Verbose');
    cli::line('     --dry-run            Do not touch files');
    cli::line('     --no-color           Do not use colours in feedback');
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