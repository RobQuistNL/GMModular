<?php
//Some options
$options['dryrun'] = false;
$options['verbose'] = false;
$options['debug'] = false;
$options['error_debug'] = false;
$options['nocolor'] = false;
$options['sync'] = false;

foreach ($argv as $nr => $argument) {
    if ($nr >= 2) {
        switch ($argument) {
            case '-d':
                $options['debug'] = true;
                break;
            case '-D':
                $options['error_debug'] = true;
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
            case '--sync':
                $options['sync'] = true;
                break;
            case '-S':
                $options['sync'] = true;
                break;
        }
    }
}
if ($options['error_debug']) {
    ini_set('display_errors', true);
    error_reporting(-1);
}
define('DRYRUN', $options['dryrun']);
define('VERBOSE', $options['verbose']);
define('DEBUG', $options['debug']);
define('COLOR', !$options['nocolor']);
define('SYNC', $options['sync']);

if (!isset($argv[1])) {
    showWelcome();
    showUsage();
}
if ($argv[1] == '--help') {
    showWelcome();
    showUsage(false);
}
$projectRoot = trim($argv[1], '/');