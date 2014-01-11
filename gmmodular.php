<?php
require("./scripts/init.php");

//Check args
require "./scripts/checkArguments.php";

showWelcome();

/** Directory checks */
CLI::verbose('Checking if "' . $projectRoot . '" is a directory.');
if (!is_dir($projectRoot)) {
    CLI::fatal($projectRoot . ' is not a directory.');
}

CLI::verbose('Checking if "' . $projectRoot . '" is writeable.');
if (!is_writeable($projectRoot)) {
    if (DRYRUN) {
        CLI::warning($projectRoot . ' is not writable, but --dry-run flag was set so we will continue.');
    } else {
        CLI::fatal($projectRoot . ' is not writeable.');
    }
}

CLI::debug('Opening "' . $projectRoot . '".');
CLI::verbose('Contents of "' . $projectRoot . '":');
if ($handle = opendir($projectRoot)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
        CLI::verbose('    ' . $entry);
    }
    closedir($handle);
}



/** Testing crap */
CLI::line();
if (CLI::getLine('Testquestion. Quit? [y/n]', 'y') == 'y') {
    CLI::line('Phew. Then this will be the end of it.');
    die;
};
CLI::line('No quit!');
