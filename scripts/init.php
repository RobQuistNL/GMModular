<?php
define('VERSION', 0.1);
define('NL', PHP_EOL);

if (PHP_SAPI !== 'cli') {
    trigger_error('Script can only run from CLI!', E_USER_ERROR);
    die;
}

require_once "CLI.class.php";
