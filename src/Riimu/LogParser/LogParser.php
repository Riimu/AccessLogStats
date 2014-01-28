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

        $this->reportProgress($rowNumber, $startTime, true);
        $this->source->close();
    }

    private function reportProgress($rowNumber, $startTime, $final = false)
    {
        if ($rowNumber % 10000 && !$final) {
            return;
        }

        $seconds = time() - $startTime;

        printf("%s rows (%d/s), %d%%, %d min %02d sec, %.2f mb (%db/row)" . PHP_EOL,
            number_format($rowNumber, 0, '.', ','),
            $rowNumber / $seconds,
            $final ? 100 : $this->source->getProgress() * 100,
            $seconds / 60,
            $seconds % 60,
            memory_get_usage() / 1024 / 1024,
            memory_get_usage() / $rowNumber);
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
                $fname = $path . DIRECTORY_SEPARATOR . $this->sanitizePath($view->getName());
                $reportInfo['views'][] = [
                    'name' => $view->getName(),
                    'type' => get_class($view),
                    'file' => "$fname.json",
                ];

                file_put_contents("$fname.json", json_encode($view->getViewData(), JSON_PRETTY_PRINT));
                file_put_contents("$fname.debug.json", json_encode($view->getDebugData(), JSON_PRETTY_PRINT));
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
