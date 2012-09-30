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
        while ($data = fgetcsv($this->fh, 0, "\t")) {
            $isTraceRecord = count($data) >= 3;
            $isEntryPoint = $isTraceRecord && !$data[self::POINT];
            $entryPointExists = $isTraceRecord && $data[self::POINT]
                && isset($this->stack[$data[self::ID]]);
            if ($isTraceRecord && ($isEntryPoint || $entryPointExists)) {
                break;
            }
        }
        if (!$data[self::POINT]) {
            $result = $this->stack[$data[self::ID]] = $data;
        } else {
            $result = $this->stack[$data[self::ID]];
            $result[self::POINT] = 1;
            $result[self::EXIT_TIME] = $data[self::TIME];
            $result[self::EXIT_MEMORY] = $data[self::MEMORY];
            unset($this->stack[$data[self::ID]]);
        }
        return $result;
    }

}
