<?php

namespace Riimu\LogParser\Source;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */

class DailyDownloadLogSource implements DataSource
{
    private $url;
    private $startDate;
    private $curlOptions;
    private $cachePath;
    private $parser;

    private $currentDate;
    private $file;
    private $curl;

    public function __construct($url, \DateTime $startDate)
    {
        $this->url = $url;
        $this->startDate = $startDate;
        $this->curlOptions = [];
        $this->cachePath = false;
        $this->parser = null;
    }

    public function setCachePath($path)
    {
        $this->cachePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function setCurlOptions(array $options)
    {
        $this->curlOptions = $options + $this->curlOptions;
    }

    public function setParser(Parser\RowParser $parser)
    {
        $this->parser = $parser;
    }

    public function open()
    {
        $this->currentDate = clone $this->startDate;
        $this->file = false;
        $this->curl = curl_init();
    }

    public function getNext()
    {
        do {
            if ($this->file === false || $this->file->eof()) {
                if ($this->file !== false && $this->cachePath === false) {
                    $path = $this->file->getPathname();
                    $this->file = null;
                    unlink($path);
                }

                $this->file = $this->getNextFile();

                if ($this->file === false) {
                    return false;
                }
            }

            $row = trim($this->file->fgets());

            if ($row != '') {
                $return = $this->parser->parseRow($row);
            }
        } while ($row == '' || $return === false);

        return $return;
    }

    private function getNextFile()
    {
        $day = $this->currentDate->format('Y-m-d');
        $this->currentDate->modify('+1 day');

        if ($this->currentDate->getTimestamp() > time()) {
            return false;
        }

        if ($this->cachePath !== false) {
            $cacheFile = $this->cachePath . $day . ".log";

            if (file_exists($cacheFile)) {
                echo "Reading from $cacheFile" . PHP_EOL;
                return new \SplFileObject($cacheFile);
            }
        } else {
            $cacheFile = tempnam(sys_get_temp_dir(), 'logdl');
        }

        echo "Downloading " . $this->formatUrl($day) . PHP_EOL;

        $fp = fopen($cacheFile, 'w');
        curl_setopt_array($this->curl, [
            CURLOPT_FILE => $fp,
            CURLOPT_URL => $this->formatUrl($day),
        ] + $this->curlOptions);
        curl_exec($this->curl);
        fclose($fp);

        return new \SplFileObject($cacheFile);
    }

    private function formatUrl($day)
    {
        return str_replace(['%Y', '%M', '%D'], explode('-', $day), $this->url);
    }

    public function getProgress()
    {
        return ($this->currentDate->getTimestamp() - $this->startDate->getTimestamp()) /
            (time() - $this->startDate->getTimestamp());
    }

    public function close()
    {
        $this->currentDate = false;
        $this->curl = false;
        $this->file = false;
    }
}