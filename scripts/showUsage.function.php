<?php
/**
 *
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
    cli::line('     -d                   Debug');  //@todo
    cli::line('     -v                   Verbose');//@todo
    cli::line('     -q                   Quiet');  //@todo
    cli::line('     --dry-run            Do not touch files');  //@todo
    die;
}