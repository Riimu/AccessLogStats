<?php

namespace Riimu\LogParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class LogParser
{
    private $source;
    private $reports;
    private $output;
    private $maxRows;

    public function __construct(Source\DataSource $source, $output)
    {
        $this->source = $source;
        $this->reports = [];
        $this->output = $output;
        $this->maxRows = 0;
    }

    public function addReport(Report $report)
    {
        $this->reports[] = $report;
    }

    public function setMaxRows($rows)
    {
        $this->maxRows = (int) $rows;
    }

    public function process()
    {
        $this->source->open();
        $startTime = time();
        $rowNumber = 0;

        while (($row = $this->source->getNext()) !== false) {
            foreach ($this->reports as $report) {
                $report->processRow($row);
            }

            $this->reportProgress(++$rowNumber, $startTime);

            if ($rowNumber == $this->maxRows) {
                break;
            }
        }

        $this->source->close();
    }

    private function reportProgress($rowNumber, $startTime)
    {
        if ($rowNumber % 10000) {
            return;
        }

        $seconds = time() - $startTime;
        $min = floor($seconds / 60);
        $sec = $seconds % 60;
        $mem = round(memory_get_usage() / 1024 / 1024, 2);

        echo "$rowNumber rows, $min min $sec sec, $mem mb\n";
    }

    public function saveJSON()
    {
        $base = $this->getOutputPath($this->output);
        $info = [];

        foreach ($this->reports as $report) {
            $reportInfo = [
                'name' => $report->getName(),
                'views' => [],
            ];
            $path = $this->getOutputPath($base . DIRECTORY_SEPARATOR .
                $this->sanitizePath($report->getName()));

            foreach ($report->getViews() as $view) {
                $fname = $path . DIRECTORY_SEPARATOR . $this->sanitizePath($view->getName()) . ".json";
                $reportInfo['views'][] = [
                    'name' => $view->getName(),
                    'file' => $fname,
                ];

                file_put_contents($fname, json_encode($view->getData(), JSON_PRETTY_PRINT));
            }

            $info[] = $reportInfo;
        }

        file_put_contents($base . DIRECTORY_SEPARATOR . '/reports.json',
        json_encode($info, JSON_PRETTY_PRINT));
    }

    private function sanitizePath($name)
    {
        $path = preg_replace('/[^\x20-\x7E]+|[\\\\\\/:*?"<>|]+/', '', $name);
        return str_replace(' ', '_', $path);
    }

    private function getOutputPath($path)
    {
        if (file_exists($path)) {
            if (!is_dir($path)) {
                throw new \RuntimeException('Output path is not a directory');
            }
        } else {
            mkdir($path);
        }

        return realpath($path);
    }
}
