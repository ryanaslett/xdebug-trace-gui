<?php
namespace velovint\XdebugTrace\Reader;

use velovint\XdebugTrace\Reader;

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
            echo "{$data[Reader::ID]} - {$data[Reader::POINT]} \n";
            $moveForwardLines = $target - $data[Reader::ID] - 1;
            for ($x = 0; $x < $moveForwardLines; $x++) {
                fgets($fp);
            }
        } while (!feof($fp) && $data[Reader::ID] != $target);
        fseek($fp, strlen($line) * -1, SEEK_CUR);
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