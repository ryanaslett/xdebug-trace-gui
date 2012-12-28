<?php

namespace velovint\XdebugTrace;

class SummaryTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        $this->sut = new Summary();
    }

    public function testNextReturnsAddedCalls() {
        $sampleTrace = $this->getSampleTrace();
        $this->sut->add($sampleTrace);
        $actual = array();

        while ($item = $this->sut->next()) {
            $actual[] = $item;
        }

        $this->assertCount(1, $actual);
    }

    public function testNextReturnsCallSummary() {
        $sampleTrace = $this->getSampleTrace();
        $expected = array(
            Summary::NAME => "myFunction",
            Summary::TIMES => 1,
            Summary::TOTAL_TIME => 0.01,
            Summary::TOTAL_MEMORY => 500,
            Summary::AVG_TIME => 0.01,
            Summary::AVG_MEMORY => 500
        );
        $this->sut->add($sampleTrace);

        $actual = $this->sut->next();

        $this->assertEquals($expected, $actual);
    }

    public function testNextAddsCallSummary() {
        $sampleTrace = $this->getSampleTrace();
        $this->sut->add($sampleTrace);
        $sampleTrace[Frame::EXIT_TIME] = "0.13";
        $sampleTrace[Frame::EXIT_MEMORY] = "1600";
        $this->sut->add($sampleTrace);
        $expected = array(
            Summary::NAME => "myFunction",
            Summary::TIMES => 2,
            Summary::TOTAL_TIME => 0.04,
            Summary::TOTAL_MEMORY => 1100,
            Summary::AVG_TIME => 0.02,
            Summary::AVG_MEMORY => 550
        );

        $actual = $this->sut->next();

        $this->assertEquals($expected, $actual);
    }

    private function getSampleTrace() {
        return array(
            Frame::NAME => "myFunction",
            Frame::TIME => "0.1",
            Frame::EXIT_TIME => "0.11",
            Frame::MEMORY => "1000",
            Frame::EXIT_MEMORY => "1500"
        );
    }

}
