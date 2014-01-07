<?php
require(__DIR__ . "/scripts/init.php");

CLI::line('Welcome to the GMModular application.');
CLI::line('version ' . VERSION);
CLI::line('GMModular by Rob Quist - Licensed under Apache2 License');
CLI::line();
CLI::line('================================================================================');
CLI::line('================================================================================');
CLI::line();

CLI::line();
if (CLI::getLine('Testquestion. Quit? [y/n]', 'y') == 'y') {
    CLI::line('Phew. Then this will be the end of it.');
    die;
};
CLI::line('No quit!');
