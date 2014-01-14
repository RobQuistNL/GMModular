<?php
define('VERSION', 0.1);
define('NL', PHP_EOL);
define('C_SUBMODULEFOLDER', 'submodules');
define('C_MODULEFILE', 'modules.gmm');
define('DS', DIRECTORY_SEPARATOR);

if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'embed') {
    trigger_error('Script can only run from CLI, not from: '.PHP_SAPI.'!', E_USER_ERROR);
    die;
}

require_once "Color.class.php";
require_once "CLI.class.php";
require_once "GMModular.class.php";
require_once "GMXAsset.class.php";
require_once "GMXAssetFolder.class.php";
require_once "GMModularFile.class.php";
require_once "Submodule.class.php";

require_once "userFeedback.function.php";
