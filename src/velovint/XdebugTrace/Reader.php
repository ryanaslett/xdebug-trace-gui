<?php
namespace velovint\XdebugTrace;

/**
 * @todo need to parse line, then filter nested calls and then filter depth
 */
class Reader {

    const LEVEL = 0;
    const ID = 1;
    const POINT = 2;
    const TIME = 3;
    const MEMORY = 4;
    const NAME = 5;
    const FILENAME = 8;
    const LINE = 9;
    const EXIT_TIME = 11;
    const EXIT_MEMORY = 12;
    const DEFAULT_DEPTH = 4;

    private $stack = array();
    private $fh;
    private $maxDepth;

    public function __construct($file, $maxDepth = null) {
        $this->fh = fopen($file, 'r');
        $this->maxDepth = $maxDepth ?: self::DEFAULT_DEPTH;
    }

    public function __destruct() {
        fclose($this->fh);
    }
    
    public function getMemoryUsage($out) {
        return $out[self::EXIT_MEMORY] - $out[self::MEMORY];
    }
    
    public function getExecutionTime($out) {
        return $out[self::EXIT_TIME] - $out[self::TIME];
    }

    /**
     * @return array
     */
    public function next() {
        do {
            $data = explode("\t", rtrim(fgets($this->fh)));
            if (count($data) < 4) {
                return null;
            }
            if ($data[self::LEVEL] > $this->maxDepth) {
                /**
                 * we can move forward at least number of lines = the difference
                 * of current and max levels -1 if that's exit point
                 */
                $moveForwardLines = $data[self::LEVEL] - $this->maxDepth
                    - $data[self::POINT];
                for ($x = 0; $x < $moveForwardLines; $x++) {
                    fgets($this->fh);
                }
                continue;
            }

            if (isset($data[self::POINT]) && $data[self::POINT] == "0") {
                $result = $this->stack[] = $data;
            } else {
                $result = array_pop($this->stack);
                $result[self::POINT] = 1;
                $result[self::EXIT_TIME] = $data[self::TIME];
                $result[self::EXIT_MEMORY] = $data[self::MEMORY];
            }
        } while ($data[self::LEVEL] > $this->maxDepth);
        return $result;
    }
    
    public function init() {
        while ((strpos(fgets($this->fh), "TRACE START")) === FALSE) {};
    }

}