<?php

use \XdebugTraceReader as Reader;

class XdebugTraceOutputList {
    public function __construct($warnTimeJump = null, $warnMemoryJump = null) {
        $this->priviousLevel = 0;
        $this->memJump = (float) $warnMemoryJump;
        $this->timeJump = (float) $warnTimeJump;
    }

    public function printLine($data) {
        if (!$data[Reader::POINT]) {
            $executionTime = $memoryUsage = 0;
            $callInfo = sprintf('<li id="call%d">%s() %s:%d',
                $data[Reader::ID], $data[Reader::NAME], 
                $data[Reader::FILENAME], $data[Reader::LINE]);
        } else {
            $executionTime = Reader::getExecutionTime($data);
            $memoryUsage = Reader::getMemoryUsage($data);
            $warning = $executionTime > $this->timeJump
                || $memoryUsage > $this->memJump;
            $callInfo = sprintf(
                ' <span class="stat%s">%.3fms / %+.4f Mb</span></li>%s',
                $warning ? " warning" : "",
                $executionTime * 1000,
                $memoryUsage / (1024 * 1024),
                PHP_EOL);
        }

        if ($data[Reader::LEVEL] > $this->previousLevel) {
            if ($data[Reader::LEVEL] >= 3) { ob_start(); }
            echo "<ul>\n"; 
        }
        elseif ($data[Reader::LEVEL] < $this->previousLevel) {
            echo "</ul>\n";
            $dropNestedCalls = $data[Reader::LEVEL] >= 3;
            $flushNestedCalls = $data[Reader::LEVEL] >= 3 
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