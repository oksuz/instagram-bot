<?php

namespace IApplication;

class ConsoleLogger {

    public static function info($msg, $detail = null) {
        self::msg('INFO', $msg, $detail);
    }

    public static function error($msg, $detail = null) {
        self::msg('ERR', $msg, $detail);
    }

    public static function log($msg, $detail = null) {
        self::msg('LOG', $msg, $detail);
    }

    private static function msg($type, $msg, $detail) {
        if (count($detail) > 0) {
            echo sprintf('[%s][%s] %s (%s)', $type, date('Y-m-d H:i:s'), $msg, print_r($detail, true)) . PHP_EOL;
        } else {
            echo sprintf('[%s][%s] %s', $type, date('Y-m-d H:i:s'), $msg) . PHP_EOL;
        }
    }

}
