<?php
require("./scripts/init.php");

CLI::line('Welcome to the ' . Color::str('GM','green') . Color::str('Modular', 'light_blue') . '  application.');
CLI::line(Color::str('version ' . VERSION, 'brown'));
CLI::line(Color::str('GMModular by Rob Quist - Licensed under Apache2 License', 'brown'));
CLI::line();
CLI::line('================================================================================');
CLI::line('================================================================================');
CLI::line();
if (!isset($argv[1])) {
    showUsage();
}
if ($argv[1] == '--help') {
    showUsage(false);
}
$projectRoot = $argv[1];

if (!is_dir($projectRoot)) {
    CLI::fatal($projectRoot . ' is not a directory.');
}

if (!is_writeable($projectRoot)) {
    if (DRYRUN) {
        CLI::warning($projectRoot . ' is not writable, but --dry-run flag was set so we will continue.');
    } else {
        CLI::fatal($projectRoot . ' is not writeable.');
    }
}


CLI::line();
if (CLI::getLine('Testquestion. Quit? [y/n]', 'y') == 'y') {
    CLI::line('Phew. Then this will be the end of it.');
    die;
};
CLI::line('No quit!');
