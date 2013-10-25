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
    private $file;
    private $parser;

    public function __construct($path)
    {
        $this->path = $path;
        $this->file = null;
        $this->parser = null;
    }

    public function setParser(Parser\RowParser $parser)
    {
        $this->parser = $parser;
    }

    public function open()
    {
        $this->file = new \SplFileObject($this->path);
    }

    public function getNext()
    {
        do {
            if ($this->file->eof()) {
                return false;
            }

            $row = trim($this->file->fgets());
        } while ($row == '');

        return $this->parser->parseRow($row);
    }

    public function close()
    {
        $this->file = null;
    }

    public function getProgress()
    {
        return $this->file->ftell() / $this->file->getSize();
    }
}
