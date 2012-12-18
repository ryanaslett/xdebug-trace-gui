<?php

use \XdebugTraceReader as Reader;

class XdebugTraceOutputList {
    const BUFFER_LEVELS = 4;
    public function __construct($warnTimeJump = null, $warnMemoryJump = null) {
        $this->previousLevel = 0;
        $this->memJump = (float) $warnMemoryJump;
        $this->timeJump = (float) $warnTimeJump;
    }

    public function printLine($data) {
        if (!$data[Reader::POINT]) {
            $executionTime = $memoryUsage = 0;
            $callInfo = "<li id=\"call{$data[Reader::ID]}\">" .
                "{$data[Reader::NAME]}() {$data[Reader::FILENAME]}:" .
                "{$data[Reader::LINE]}";
        } else {
            $executionTime = Reader::getExecutionTime($data);
            $memoryUsage = Reader::getMemoryUsage($data);
            $warning = $executionTime > $this->timeJump
                || $memoryUsage > $this->memJump;
            $callInfo = sprintf(
                " <span class=\"stat%s\">%.3fms / %+.4f Mb</span></li>\n",
                $warning ? " warning" : "",
                $executionTime * 1000,
                $memoryUsage / (1024 * 1024));
        }

        if ($data[Reader::LEVEL] > $this->previousLevel) {
            if ($data[Reader::LEVEL] >= self::BUFFER_LEVELS) { ob_start(); }
            echo "<ul>\n"; 
        }
        elseif ($data[Reader::LEVEL] < $this->previousLevel) {
            echo "</ul>\n";
            $dropNestedCalls = $data[Reader::LEVEL] >= self::BUFFER_LEVELS;
            $flushNestedCalls = $data[Reader::LEVEL] >= self::BUFFER_LEVELS 
                && ($memoryUsage > $memJump
                    || $executionTime > $timeJump);
            if ($flushNestedCalls) {
                ob_end_flush();
            } elseif ($dropNestedCalls) {
                ob_end_clean();
            }
        }
        $this->previousLevel = $data[Reader::LEVEL];
        echo $callInfo;
    }
}