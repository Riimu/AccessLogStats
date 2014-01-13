<?php

namespace Riimu\LogParser\Source\Parser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class PatternParser implements RowParser
{
    private $pattern;
    private $dataParser;

    public function __construct()
    {
        $this->dataParser = new \Riimu\LogParser\DataParser();
        $this->pattern =
            '/^(?<ip>[^ ]+) (?<host>[^ ]+) (?<ident>[^ ]+) ' .
            '\[(?<time>[^\]]+)\] "(?<method>[^" ]+) (?<path>[^"]+) ' .
            '(?<protocol>[^" ]+)" (?<code>[^ ]+) (?<size>[^ ]+) ' .
            '"(?<referrer>[^"]+)" "(?<agent>[^"]+)"$/';
    }

    public function parseRow($row)
    {
        if (!preg_match($this->pattern, $row, $match)) {
            echo "Could not parse: $row" . PHP_EOL;
            return false;
        };

        return new \Riimu\LogParser\LogRow($match, $this->dataParser);
    }
}
