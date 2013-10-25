<?php

namespace Riimu\LogParser\View;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class HitsView implements DataView
{
    private $lastSeen;
    private $hits;
    private $views;
    private $uniques;
    private $new;
    private $pathCache;
    private $pathPatterns;

    public function __construct()
    {
        $this->lastSeen = [];
        $this->hits = [];
        $this->views = [];
        $this->uniques = [];
        $this->new = [];
        $this->pathCache = [];
        $this->pathPatterns = [];
    }

    public function addPathPattern($pattern)
    {
        if (is_array($pattern)) {
            $this->pathPatterns = array_merge($this->pathPatterns, $pattern);
        } else {
            $this->pathPatterns[] = $pattern;
        }

        return $this;
    }

    public function getData()
    {
        return [
            'ipCount' => count($this->lastSeen),
            'hitCount' => array_sum($this->hits),
            'viewCount' => array_sum($this->views),
            'uniqueCount' => array_sum($this->uniques),
            'newCount' => array_sum($this->new),
            'hits' => $this->hits,
            'views' => $this->views,
            'uniques' => $this->uniques,
            'new' => $this->new,
            'pathCache' => [
                'true' => array_keys($this->pathCache, true),
                'false' => array_keys($this->pathCache, false),
            ],
        ];
    }

    public function getName()
    {
        return 'HitsView';
    }

    public function processRow(\Riimu\LogParser\LogRow $row)
    {
        $ip = $row->getIp();
        $date = $row->getDate();
        $day = $date->format('Y-m-d');

        if (!isset($this->lastSeen[$ip])) {
            $unique = true;
            $new = true;
        } else {
            if ($this->lastSeen[$ip]->format('Y-m-d') != $day) {
                $unique = true;
                $new = $this->isNewVisit($date, $this->lastSeen[$ip]);
            } else {
                $unique = false;
            }
        }

        if (!isset($this->hits[$day])) {
            $this->hits[$day] = 0;
            $this->uniques[$day] = 0;
            $this->new[$day] = 0;
            $this->views[$day] = 0;
        }

        $this->lastSeen[$row->getIp()] = $row->getDate();
        $this->hits[$day]++;

        if ($unique) {
            $this->uniques[$day]++;

            if ($new) {
                $this->new[$day]++;
            }
        }

        if ($this->isPageView($row)) {
            $this->views[$day]++;
        }
    }

    private function isNewVisit(\DateTime $current, \DateTime $previous)
    {
        $compare = clone $current;
        $compare->modify("-7 days");
        $compare->modify("today");
        return $compare->getTimestamp() > $previous->getTimestamp();
    }

    private function isPageView(\Riimu\LogParser\LogRow $row)
    {
        if ($row->getCode() != '200' && $row->getCode() != '304') {
            return false;
        }

        $path = $row->getPath();

        if (isset($this->pathCache[$path])) {
            return $this->pathCache[$path];
        }

        foreach ($this->pathPatterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return $this->pathCache[$path] = false;
            }
        }

        return $this->pathCache[$path] = true;
    }
}
