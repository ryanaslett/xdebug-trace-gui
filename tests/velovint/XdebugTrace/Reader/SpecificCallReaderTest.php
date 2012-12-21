<?php
namespace velovint\XdebugTrace\Reader;

use velovint\XdebugTrace\Reader;

class SpecificCallReaderTest extends \PHPUnit_Framework_TestCase {

    function testNextReadsOnlyNestedCallsOnOneLevel() {
        $reader = $this->getReaderFor("tests/velovint/XdebugTrace/sample-trace.xt");
        $sut = new SpecificCallReader($reader, 1);

        $actual = $this->readFullFile($sut);

        $this->assertCount(3, $actual);
    }

    function testNextReadsOnlyNestedCallsOnMultipleLevels() {
        $reader = $this->getReaderFor("tests/velovint/XdebugTrace/sample-trace.xt");
        $sut = new SpecificCallReader($reader, 0);

        $actual = $this->readFullFile($sut);

        $this->assertCount(5, $actual);
    }

    private function getReaderFor($file, $maxDepth = null) {
        $reader = new Reader($file, $maxDepth);
        $reader->init();
        return $reader;
    }

    public function readFullFile($sut) {
        $result = array();
        while ($result[] = $sut->next()) {
            
        }
        return $result;
    }

}
