<?php
define('VERSION', 0.1);
define('NL', PHP_EOL);
define('DRYRUN', FALSE); //@todo

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'embed') {
    trigger_error('Script can only run from CLI, not from: '.PHP_SAPI.'!', E_USER_ERROR);
    die;
}

require_once "Color.class.php";
require_once "CLI.class.php";
require_once "showUsage.function.php";
