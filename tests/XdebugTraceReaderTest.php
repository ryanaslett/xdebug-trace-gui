<?php

require_once "../src/XdebugTraceReader.php";
require_once "../src/XdebugTraceOutputList.php";

class XdebugTraceReaderTest extends PHPUnit_Framework_TestCase {
    
    public function testNextReadsEntiryTraceFile() {
        $sut = $this->getReaderFor("sample-trace.xt");
        
        $data = $this->readFullFile($sut);

        $this->assertCount(5, $data);
    }

    public function testNextParsesEntryPoint() {
        $sut = $this->getReaderFor("sample-trace.xt");
        $expected = array('1', '0', '0', '0.000210', '321320', '{main}',
            '1', '', '/var/www/equest2_git_2/ui/bin/run_tests.sh', '0');

        $actual = $sut->next();

        $this->assertEquals($expected, $actual);
    }

    public function testNextAppendsStatsOnExitPoint() {
        $sut = $this->getReaderFor("sample-trace.xt");

        $actual = $this->readFullFile($sut);

        $this->assertEquals("321852", $actual[2][XdebugTraceReader::EXIT_MEMORY]);
        $this->assertEquals("0.000365", $actual[2][XdebugTraceReader::EXIT_TIME]);
    }

    function testNextAppendsStatsOnExitForMain() {
        $sut = $this->getReaderFor("sample-trace.xt");

        $actual = $this->readFullFile($sut);

        $this->assertEquals("11712", $actual[3][XdebugTraceReader::EXIT_MEMORY]);
        $this->assertEquals("4.013765", $actual[3][XdebugTraceReader::EXIT_TIME]);
    }

    function testNextSkipsElementsDeeperThanMaxDepth() {
        $sut = $this->getReaderFor("sample-trace.xt", 1);

        $actual = $this->readFullFile($sut);

        $this->assertCount(3, $actual);
    }

    private function getReaderFor($file, $maxDepth = null) {
        $reader = new XdebugTraceReader($file, $maxDepth);
        $reader->init();
        return $reader;
    }

    public function readFullFile($sut) {
        $result = array();
        while ($result[] = $sut->next()) {}
        return $result;
    }

}