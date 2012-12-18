<?php

class XdebugTraceReader {
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
    
    
    private $stack = array();
    private $fh;

    public function __construct($file) {
        $this->fh = fopen($file, 'r');
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
        $data = fgetcsv($this->fh, 0, "\t");
        if (!isset($data[self::ID])) { return null; }
        if (!$data[self::POINT]) {
            $result = $this->stack[] = $data;
        } else {
            $result = array_pop($this->stack);
            $result[self::POINT] = 1;
            $result[self::EXIT_TIME] = $data[self::TIME];
            $result[self::EXIT_MEMORY] = $data[self::MEMORY];
        }
        return $result;
    }
    
    public function init() {
        while ((strpos(fgets($this->fh), "TRACE START")) === FALSE) {};
    }

}
