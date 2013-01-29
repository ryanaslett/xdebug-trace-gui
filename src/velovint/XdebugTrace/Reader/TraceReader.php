<?php
namespace velovint\XdebugTrace\Reader;

use \velovint\XdebugTrace\Reader;
use \velovint\XdebugTrace\Frame;

class TraceReader implements Reader {

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

    public function getFileHandler() {
        return $this->fh;
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
            if ($data[Frame::LEVEL] > $this->maxDepth) {
                /**
                 * we can move forward at least number of lines = the difference
                 * of current and max levels -1 if that's exit point
                 */
                $moveForwardLines = $data[Frame::LEVEL] - $this->maxDepth
                    - $data[Frame::POINT];
                for ($x = 0; $x < $moveForwardLines; $x++) {
                    fgets($this->fh);
                }
                continue;
            }

            if (isset($data[Frame::POINT]) && $data[Frame::POINT] == "0") {
                $result = $this->stack[] = $data;
            } else {
                $result = array_pop($this->stack);
                $result[Frame::POINT] = 1;
                $result[Frame::EXIT_TIME] = $data[Frame::TIME];
                $result[Frame::EXIT_MEMORY] = $data[Frame::MEMORY];
            }
        } while ($data[Frame::LEVEL] > $this->maxDepth);
        return $result;
    }
    
    public function init() {
        while ((strpos(fgets($this->fh), "TRACE START")) === FALSE) {};
    }

}