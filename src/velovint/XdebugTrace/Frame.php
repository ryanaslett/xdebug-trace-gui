<?php

namespace velovint\XdebugTrace;

/**
 * Represents structure of Xdebug's execution frame
 */
abstract class Frame {

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

    public static function getMemoryUsage($out) {
        return $out[Frame::EXIT_MEMORY] - $out[Frame::MEMORY];
    }

    public static function getExecutionTime($out) {
        return $out[Frame::EXIT_TIME] - $out[Frame::TIME];
    }

}
