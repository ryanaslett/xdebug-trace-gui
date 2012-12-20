<?php

require_once "XdebugTraceReader.php";

class XdebugNestedCallsReader {

    private $isInTargetCall;
    private $leftTargetCall;

    public function __construct(XdebugTraceReader $reader, $callId) {
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
        } while (!$this->isInTargetCall && $data[XdebugTraceReader::ID] != $this->callId);
        if ($data[XdebugTraceReader::ID] == $this->callId) {
            $this->isInTargetCall = !$this->isInTargetCall;
            if ($data[XdebugTraceReader::POINT] == "1") {
                $this->leftTargetCall = true;
            }
        }
        return $data;
    }

}