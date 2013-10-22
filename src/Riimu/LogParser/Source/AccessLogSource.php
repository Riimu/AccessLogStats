<?php

namespace Riimu\LogParser\Source;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class AccessLogSource implements DataSource
{
    private $path;
    private $fp;

    public function __construct($path)
    {
        $this->path = $path;
        $this->fp = null;
    }

    public function open()
    {
        $this->fp = fopen($this->path, 'r');
    }

    public function getNext()
    {
        $row = fgets($this->fp);

        if ($row === false) {
            return false;
        }

        return new AccessLogRow($row);
    }

    public function close()
    {
        fclose($this->fp);
    }
}

class AccessLogRow implements \Riimu\LogParser\LogRow
{
    private $row;
    private $ip;
    private $ident;
    private $time;
    private $request;
    private $code;
    private $size;
    private $referrer;
    private $agent;

    public function __construct($row)
    {
        $row = trim($row);
        $this->row = $row;

        preg_match('/^([^ ]+) ([^ ]+) ([^ ]+) \[([^\]]+)\] "([^"]+)" ([^ ]+) ([^ ]+) "([^"]+)" "([^"]+)"/', $row, $match);
        $this->ip = $match[1];
        $this->domain = $match[2];
        $this->ident = $match[3];
        $this->time = $match[4];
        $this->request = $match[5];
        $this->code = $match[6];
        $this->size = $match[7];
        $this->referrer = $match[8];
        $this->agent = $match[9];
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
}