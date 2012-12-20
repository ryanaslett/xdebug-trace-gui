<?php

require_once "../src/XdebugNestedCallsReader.php";

class XdebugNestedCallsReaderTest extends PHPUnit_Framework_TestCase {

    function testNextReadsOnlyNestedCallsOnOneLevel() {
        $reader = $this->getReaderFor("sample-trace.xt");
        $sut = new XdebugNestedCallsReader($reader, 1);

        $actual = $this->readFullFile($sut);

        $this->assertCount(3, $actual);
    }

    function testNextReadsOnlyNestedCallsOnMultipleLevels() {
        $reader = $this->getReaderFor("sample-trace.xt");
        $sut = new XdebugNestedCallsReader($reader, 0);

        $actual = $this->readFullFile($sut);

        $this->assertCount(5, $actual);
    }

    private function getReaderFor($file, $maxDepth = null) {
        $reader = new XdebugTraceReader($file, $maxDepth);
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
