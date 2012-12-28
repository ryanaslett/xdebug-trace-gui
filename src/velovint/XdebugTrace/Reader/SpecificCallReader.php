<?php

namespace velovint\XdebugTrace\Reader;

use \velovint\XdebugTrace\Reader;
use \velovint\XdebugTrace\Frame;

class SpecificCallReader {

    private $callId;
    private $isInTargetCall;
    private $leftTargetCall;

    /** @var Reader */
    private $reader;

    public function __construct(Reader $reader, $callId) {
        $this->reader = $reader;
        $this->callId = $callId;
        $this->isInTargetCall = false;
        $this->leftTargetCall = false;
    }

    public function init() {
        $this->reader->init();
        /**
         * call ID is at least number of strings = target ID - current ID - 1
         * @todo cover this part with tests
         */
        $target = $this->callId;
        $fp = $this->reader->getFileHandler();
        do {
            $data = explode("\t", $line = fgets($fp));
            $moveForwardLines = $target - $data[Frame::ID] - 1;
            for ($x = 0; $x < $moveForwardLines; $x++) {
                fgets($fp);
            }
        } while (!feof($fp) && $data[Frame::ID] != $target);
        fseek($fp, strlen($line) * -1, SEEK_CUR);
    }

    public function next() {
        if ($this->leftTargetCall) {
            return;
        }
        do {
            $data = $this->reader->next();
            if (is_null($data)) {
                return;
            }
        } while (!$this->isInTargetCall && $data[Frame::ID] != $this->callId);
        if ($data[Frame::ID] == $this->callId) {
            $this->isInTargetCall = !$this->isInTargetCall;
            if ($data[Frame::POINT] == "1") {
                $this->leftTargetCall = true;
            }
        }
        return $data;
    }

}