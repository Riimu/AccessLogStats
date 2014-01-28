<?php

namespace Riimu\LogParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class LogRow
{
    /**
     * @var \Riimu\LogParser\DataParser
     */
    private $dataParser;
    private $fields;

    private static $fieldNames = [
        'ip', 'host', 'ident', 'time', 'method', 'path', 'protocol', 'code',
        'size', 'referrer', 'agent'
    ];

    public function __construct($row, DataParser $dataParser)
    {
        $this->dataParser = $dataParser;

        foreach (self::$fieldNames as $name) {
            $this->fields[$name] = isset($row[$name]) && $row[$name] !== '-'
                ? $row[$name] : null;
        }
    }

    public function getField($field)
    {
        if (!isset($this->fields[$field]) && !array_key_exists($field, $this->fields)) {
            throw new \InvalidArgumentException("Invalid field name: $field");
        }

        return $this->fields[$field];
    }

    public function getIp()
    {
        return $this->fields['ip'];
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->fields['time'] !== null
            ? new \DateTime($this->fields['time']) : null;
    }

    public function getCode()
    {
        return $this->fields['code'];
    }

    public function getPath()
    {
        return $this->fields['path'];
    }

    public function getReferrer()
    {
        return $this->fields['referrer'];
    }

    public function getUserAgent()
    {
        return $this->fields['agent'];
    }

    public function getDay($format = 'Y-m-d')
    {
        return $this->getDate()->format($format);
    }

    public function getBrowser()
    {
        if ($this->fields['agent'] === null) {
            return null;
        }

        $browser = $this->dataParser->getBrowser($this->fields['agent']);
        return $browser->Browser === 'Default Browser' ? null : $browser;
    }

    public function getUrlInfo()
    {
        return $this->fields['referrer'] !== null
            ? $this->dataParser->getUrlInfo($this->fields['referrer']) : null;
    }

    public function getReferrerInfo()
    {
        return $this->fields['referrer'] !== null
            ? $this->dataParser->getReferrerInfo($this->fields['referrer']) : null;
    }

    public function isCrawler()
    {
        $browser = $this->getBrowser();
        return $browser !== null && $browser->Crawler;
    }
}
