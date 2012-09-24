<?php

class XdebugTraceReader {
    const LEVEL = 0;
    const ID = 1;
    const POINT = 2;
    const TIME = 3;
    const MEMORY = 4;
    
    private $stack = array();
    private $fh;
    private $lastCall = array();

    public function __construct($file) {
        $this->fh = fopen($file, 'r');
    }

    public function __destruct() {
        fclose($this->fh);
    }
    
    public function getMemoryUsage($out) {
        $id = $out[self::ID];
        if (!isset($this->stack[$id])) {
            return null;
        }
        $in = $this->stack[$id];
        return $out[self::MEMORY] - $in[self::MEMORY];
    }
    
    public function getExecutionTime($out) {
        $id = $out[self::ID];
        if (!isset($this->stack[$id])) {
            return null;
        }
        $in = $this->stack[$id];
        return $out[self::TIME] - $in[self::TIME];
    }

    /**
     * @return XdebugTraceCall
     */
    public function next() {
        if (isset($this->lastCall[self::POINT]) && $this->lastCall[self::POINT]) {
            unset($this->stack[$this->lastCall[self::ID]]);
        }
        while ($this->lastCall = $data = fgetcsv($this->fh, 0, "\t")) {
            if (count($data) >= 3) {
                break;
            }
        }
        if (!$data[self::POINT]) {
            $this->stack[$data[self::ID]] = $data;
        }
//        $this->test = array($data[1], $data[5]);
        return $data;
//        return XdebugTraceCall::newFromArray($data);
    }

}
