<?php

class CLI {
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

    public static function line($string = '', $nl = true) {
        echo $string;
        if ($nl) {
            echo NL;
        }
    }

}

?>