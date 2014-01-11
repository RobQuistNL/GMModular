<?php

class CLI {

    private $colors;

    /**
     * Get a line from the CLI
     * @param string $default
     * @return string
     */
    public static function getLine($question, $default = '')
    {
        self::line($question . ' [' . $default . ']: ', false);
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if (trim($line) == '') {
            return $default;
        }
        return trim($line);
    }

    /**
     * Echo a string
     * @param string $string
     * @param bool $nl
     */
    public static function line($string = '', $nl = true) {
        echo $string;
        if ($nl) {
            echo NL;
        }
    }

    /**
     * Throw a warning
     * @param string $string
     */
    public static function warning($string = '') {
        self::line(
            Color::str('WARNING: ' . $string, "yellow", "")
        );
    }

    /**
     * Throw fatal error. Also kills the script.
     * @param string $string
     */
    public static function fatal($string = '') {
        self::line(
            Color::str('UNRECOVERABLE FATAL ERROR: ' . $string, "light_cyan", "red")
        );
        self::line('For help, execute "gmmodular --help"');
        die;
    }

    /**
     * Verbose message
     * @param string $string
     */
    public static function verbose($string = '') {
        if (VERBOSE) {
            self::line(
                Color::str('V: ' . $string, "light_cyan", "")
            );
        }
    }

    /**
     * Notice message
     * @param string $string
     */
    public static function notice($string = '') {
        self::line(
            Color::str('NOTICE: ' . $string, "light_cyan", "")
        );
    }

    /**
     * Debug message
     * @param string $string
     */
    public static function debug($string = '') {
        if (DEBUG) {
            self::line(
                Color::str('DEBUG: ' . $string, "cyan", "")
            );
        }
    }
}

?>