<?php

declare ( strict_types = 1 );

namespace WebSocket\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class StdOutLogger extends AbstractLogger
{
    private const OUTMAP = [
        LogLevel::EMERGENCY => STDERR,
        LogLevel::ALERT     => STDERR,
        LogLevel::CRITICAL  => STDERR,
        LogLevel::ERROR     => STDERR,

        LogLevel::WARNING => STDOUT,
        LogLevel::NOTICE  => STDOUT,
        LogLevel::INFO    => STDOUT,
        LogLevel::DEBUG   => STDOUT,
    ];

    /**
     * Logs a message to stdout/stderr.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log( $level, $message, array $context = [] )
    {
        $level  = $level ?? LogLevel::ERROR;
        $output = isset( self::OUTMAP[$level] ) ? self::OUTMAP[$level] : STDERR;
        fwrite( $output, date( 'Y-m-d H:i:s' ) . ' [' . $level . '] ' . $message . PHP_EOL );
    }
}
