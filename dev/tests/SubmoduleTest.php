<?php
//@todo put this in autoloader for tests
require_once __DIR__ . "/../../scripts/init.php";
define('VERBOSE', false);
define('DRYRUN', true);
define('QUIET', true);
define('DEBUG', true);

class SubmoduleTest extends PHPUnit_Framework_TestCase {

    public function testBasic()
    {
        $sub = $this->getMockSubmodule();
        $this->assertInstanceOf('DOMDocument', $sub->getDom());
    }

    public function testHash()
    {
        $sub = $this->getMockSubmodule();
        $this->assertEquals('8e933d1def5401a517252dddced5106b24bf31b8', $sub->generateHash());
    }

    public function testAssets()
    {
        $sub = $this->getMockSubmodule();
        $assets = $sub->getAssets();
        for ($i=0; $i<10; $i++) { //Test that all 10 first items are folders
            $this->assertInstanceOf('GMXAssetFolder', $assets[$i]);
        }
    }

    private function getMockSubmodule()
    {
        $submodule = new Submodule(__DIR__ . '/mocks/mockproject/project.gmx');
        return $submodule;
    }
}
 