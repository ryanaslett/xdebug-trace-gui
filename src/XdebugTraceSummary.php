<?php
use XdebugTraceReader as Reader;

class XdebugTraceSummary {
    const NAME = 0;
    const TIMES = 1;
    const TOTAL_TIME = 2;
    const TOTAL_MEMORY = 3;
    const AVG_TIME = 4;
    const AVG_MEMORY = 5;
    
    private $summary = array();
    
    public function add($data) {
        if (!isset($data[Reader::NAME])) { return; }
        $index = $data[Reader::NAME];
        if (!isset($this->summary[$index])) {
            $this->summary[$index] = array(
                self::NAME => $data[Reader::NAME],
                self::TIMES => 0,
                self::TOTAL_TIME => 0,
                self::TOTAL_MEMORY => 0,
                self::AVG_TIME => 0,
                self::AVG_MEMORY => 0);
        }
        $s =& $this->summary[$index];
        /**see Incremental Average Algorithm http://jvminside.blogspot.com/2010/01/incremental-average-calculation.html */
        $executionTime = $data[Reader::EXIT_TIME] - $data[Reader::TIME];
        $s[self::AVG_TIME] = (($executionTime - $s[self::AVG_TIME]) / ($s[self::TIMES] + 1)) 
            + $s[self::AVG_TIME];
        $s[self::TOTAL_TIME] += $executionTime;
        $memoryUsage = $data[Reader::EXIT_MEMORY] - $data[Reader::MEMORY];
        $s[self::AVG_MEMORY] = (($memoryUsage - $s[self::AVG_MEMORY]) / ($s[self::TIMES] + 1))
            + $s[self::AVG_MEMORY];
        $s[self::TOTAL_MEMORY] += $memoryUsage;
        $s[self::TIMES]++;
        
    }
    public function next() {
        $result = current($this->summary);
        next($this->summary);
        return $result;
    }
}
