<?php
namespace velovint\XdebugTrace\Reader;

use velovint\XdebugTrace\Reader;

class SpecificCallReader {

    private $isInTargetCall;
    private $leftTargetCall;

    public function __construct(Reader $reader, $callId) {
        $this->reader = $reader;
        $this->callId = $callId;
        $this->isInTargetCall = false;
        $this->leftTargetCall = false;
    }

    public function init() {
        $this->reader->init();
    }

    public function next() {
        if ($this->leftTargetCall) { return; }
        do {
            $data = $this->reader->next();
            if (is_null($data)) { return; }
        } while (!$this->isInTargetCall && $data[Reader::ID] != $this->callId);
        if ($data[Reader::ID] == $this->callId) {
            $this->isInTargetCall = !$this->isInTargetCall;
            if ($data[Reader::POINT] == "1") {
                $this->leftTargetCall = true;
            }
        }
        return $data;
    }

}