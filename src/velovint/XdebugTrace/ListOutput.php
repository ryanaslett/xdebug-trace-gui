<?php
namespace velovint\XdebugTrace;

class ListOutput {
    public function __construct($warnTimeJump = null, $warnMemoryJump = null) {
        $this->previousLevel = 0;
        $this->memJump = (float) $warnMemoryJump;
        $this->timeJump = (float) $warnTimeJump;
    }

    public function printLine($data) {
        if (!$data[Frame::POINT]) {
            $executionTime = $memoryUsage = 0;
            $callInfo = "<li id=\"call{$data[Frame::ID]}\">" .
                "{$data[Frame::NAME]}() {$data[Frame::FILENAME]}:" .
                "{$data[Frame::LINE]}";
        } else {
            $executionTime = Reader::getExecutionTime($data);
            $memoryUsage = Reader::getMemoryUsage($data);
            $warning = $executionTime > $this->timeJump
                || $memoryUsage > $this->memJump;
            $callInfo = sprintf(
                " <span class=\"stat%s\">%.3fms / %+.4f MiB</span></li>\n",
                $warning ? " warning" : "",
                $executionTime * 1000,
                $memoryUsage / (1024 * 1024));
        }

        if ($data[Frame::LEVEL] > $this->previousLevel) {
            echo "<ul>\n"; 
        }
        elseif ($data[Frame::LEVEL] < $this->previousLevel) {
            echo "</ul>\n";
        }
        $this->previousLevel = $data[Frame::LEVEL];
        echo $callInfo;
    }
}