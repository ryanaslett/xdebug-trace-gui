<?php
namespace velovint\XdebugTrace;

class ReaderTest extends \PHPUnit_Framework_TestCase {
    
    public function testNextReadsEntireTraceFile() {
        $sut = $this->getReaderFor("tests/velovint/XdebugTrace/sample-trace.xt");
        
        $data = $this->readFullFile($sut);

        $this->assertCount(10, $data);
    }

    public function testNextParsesEntryPoint() {
        $sut = $this->getReaderFor("tests/velovint/XdebugTrace/sample-trace.xt");
        $expected = array('1', '0', '0', '0.000210', '321320', '{main}',
            '1', '', '/var/www/equest2_git_2/ui/bin/run_tests.sh', '0');

        $actual = $sut->next();

        $this->assertEquals($expected, $actual);
    }

    public function testNextAppendsStatsOnExitPoint() {
        $sut = $this->getReaderFor("tests/velovint/XdebugTrace/sample-trace.xt");

        $actual = $this->readFullFile($sut);

        $this->assertEquals("dirname", $actual[2][Frame::NAME]);
        $this->assertEquals("321852", $actual[2][Frame::EXIT_MEMORY]);
        $this->assertEquals("0.000365", $actual[2][Frame::EXIT_TIME]);
    }

    function testNextAppendsStatsOnExitForMain() {
        $sut = $this->getReaderFor("tests/velovint/XdebugTrace/sample-trace.xt");

        $actual = array_pop($this->readFullFile($sut));

        $this->assertEquals("11712", $actual[Frame::EXIT_MEMORY]);
        $this->assertEquals("4.013765", $actual[Frame::EXIT_TIME]);
    }

    function testNextSkipsElementsDeeperThanMaxDepth() {
        $sut = $this->getReaderFor("tests/velovint/XdebugTrace/sample-trace.xt", 1);

        $actual = $this->readFullFile($sut);

        $this->assertCount(2, $actual);
    }

    private function getReaderFor($file, $maxDepth = null) {
        $reader = new Reader($file, $maxDepth);
        $reader->init();
        return $reader;
    }

    public function readFullFile($sut) {
        $result = array();
        while ($record = $sut->next()) {
            $result[] = $record;
        }
        return $result;
    }

}