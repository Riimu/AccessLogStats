<?php

namespace Riimu\LogParser\View;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class HitsView extends DataView
{
    private $lastSeen;
    private $patterns;
    private $paths;

    private $hits;
    private $ips;
    private $visitors;
    private $new;
    private $views;
    private $crawlers;
    private $unknown;

    public function __construct()
    {
        parent::__construct();
        $this->setName('HitsView');

        $this->lastSeen = [];
        $this->patterns = [];
        $this->paths = [];
        $this->hits = [];
        $this->ips = [];
        $this->visitors = [];
        $this->new = [];
        $this->views = [];
        $this->crawlers = [];
        $this->unknown = [];
    }

    public function getViewData()
    {
        return [
            'hits' => $this->hits,
            'ips' => $this->ips,
            'visitors' => $this->visitors,
            'new' => $this->new,
            'views' => $this->views,
            'crawlers' => $this->crawlers,
        ];
    }

    public function getDebugData()
    {
        $included = array_keys($this->paths, true);
        $excluded = array_keys($this->paths, false);

        sort($included);
        sort($excluded);

        return [
            'unknown' => $this->unknown,
            'paths' => [
                'included' => $included,
                'excluded' => $excluded,
            ]
        ];
    }

    public function addExcludePattern($pattern)
    {
        $this->patterns[] = $pattern;
        return $this;
    }

    public function processRow(\Riimu\LogParser\LogRow $row)
    {
        $day = $row->getDay();
        $agent = $row->getBrowser();
        $ip = $row->getIp();

        $this->increment($this->hits[$day]);

        if (!isset($this->lastSeen[$ip])) {
            $this->increment($this->ips[$day]);
        }

        if ($agent === null) {
            $this->increment($this->unknown[$day]);
        } elseif ($agent->Crawler) {
            $this->increment($this->crawlers[$day]);
        } else {
            if (!isset($this->lastSeen[$ip])) {
                $this->increment($this->visitors[$day]);
                $this->increment($this->new[$day]);
            } else {
                if ($this->lastSeen[$ip]->format('Y-m-d') !== $day) {
                    $this->increment($this->visitors[$day]);
                }
                if ($row->getDate()->modify('-7 days today')->getTimestamp() >
                    $this->lastSeen[$ip]->getTimestamp()) {
                    $this->increment($this->new[$day]);
                }

                if ($this->isPageView($row)) {
                    $this->increment($this->views[$day]);
                }
            }
        }

        $this->lastSeen[$ip] = $row->getDate();
        return true;
    }

    private function isPageView(\Riimu\LogParser\LogRow $row)
    {
        if ($row->getCode() != '200' && $row->getCode() != '304') {
            return false;
        }

        $path = $row->getPath();

        if (!isset($this->paths[$path])) {
            $this->paths[$path] = true;

            foreach ($this->patterns as $pattern) {
                if (preg_match($pattern, $path)) {
                    $this->paths[$path] = false;
                    break;
                }
            }
        }

        return $this->paths[$path];
    }
}
