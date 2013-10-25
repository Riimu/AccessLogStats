<?php

namespace Riimu\LogParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class AccessLogRow implements LogRow
{
    private $ip;
    private $ident;
    private $time;
    private $method;
    private $path;
    private $protocol;
    private $code;
    private $size;
    private $referrer;
    private $agent;

    public function __construct($row)
    {
        $this->ip = $this->getParam('ip', $row);
        $this->domain = $this->getParam('domain', $row);
        $this->ident = $this->getParam('ident', $row);
        $this->time = $this->getParam('time', $row);
        $this->method = $this->getParam('method', $row);
        $this->path = $this->getParam('path', $row);
        $this->protocol = $this->getParam('protocol', $row);
        $this->code = $this->getParam('code', $row);
        $this->size = $this->getParam('size', $row);
        $this->referrer = $this->getParam('referrer', $row);
        $this->agent = $this->getParam('agent', $row);
    }

    private function getParam($name, $row, $default = false)
    {
        return isset($row[$name]) ? $row[$name] : $default;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getReferrer()
    {
        return $this->referrer === '-' ? false : $this->referrer;
    }

    public function getDate()
    {
        return new \DateTime($this->time);
    }

    public function getPath()
    {
        return $this->path;
    }
}
