<?php

namespace velovint\XdebugTrace\Reader;

use velovint\XdebugTrace\Reader;

class SpecificCallReaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider getAllFrameIds()
     */
    function testNextReadsOnlyNestedCallsOnOneLevel($frameId) {
        $sut = $this->getReaderFor(
            "tests/velovint/XdebugTrace/sample-trace.xt", $frameId);

        $actual = $sut->next();

        $this->assertEquals($frameId, $actual[Reader::ID]);
    }

    function testNextReadsAllNestedCalls() {
        $sut = $this->getReaderFor(
            "tests/velovint/XdebugTrace/sample-trace.xt", 2);

        $actual = $this->readFullFile($sut);

        $this->assertCount(6, $actual);
    }

    public function getAllFrameIds() {
        return array_map(function($frameId) {
                    return array($frameId);
                }, range(0, 4));
    }

    private function getReaderFor($file, $callId) {
        $reader = new SpecificCallReader(new Reader($file), $callId);
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
