<?php
//Some options
$options['dryrun'] = false;
$options['verbose'] = false;
$options['debug'] = false;
$options['nocolor'] = false;

foreach ($argv as $nr => $argument) {
    if ($nr >= 2) {
        switch ($argument) {
            case '-d':
                $options['debug'] = true;
                break;
            case '-v':
                $options['verbose'] = true;
                break;
            case '--dry-run':
                $options['dryrun'] = true;
                break;
            case '--no-color':
                $options['nocolor'] = true;
                break;
        }
    }
}

define('DRYRUN', $options['dryrun']);
define('VERBOSE', $options['verbose']);
define('DEBUG', $options['debug']);
define('COLOR', !$options['nocolor']);

if (!isset($argv[1])) {
    showWelcome();
    showUsage();
}
if ($argv[1] == '--help') {
    showWelcome();
    showUsage(false);
}
$projectRoot = $argv[1];