<?php

namespace Riimu\LogParser\View;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ReferrerView implements DataView
{
    private $parser;
    private $internalDomains;
    private $includeInternalTraffic;

    private $directCount;
    private $internalCount;
    private $referredCount;

    private $referrers;
    private $internals;
    private $domains;
    private $targets;
    private $other;

    private $cache;

    public function __construct()
    {
        $this->parser = new \Riimu\Kit\UrlParser\UrlParser();
        $this->internalDomains = [];
        $this->includeInternalTraffic = true;

        $this->directCount = 0;
        $this->internalCount = 0;
        $this->referredCount = 0;

        $this->referrers = [];
        $this->internals = [];
        $this->domains = [];
        $this->targets = [];
        $this->other = [];

        $this->cache = [];
    }

    public function addInternalDomain($domain)
    {
        $this->internalDomains[] = $domain;
        return $this;
    }

    public function setIncludeInternalTraffic($state)
    {
        $this->includeInternalTraffic = (bool) $state;
        return $this;
    }

    public function getName()
    {
        return 'ReferrerView';
    }

    public function getData()
    {
        return [
            'directCount' => $this->directCount,
            'internalCount' => $this->internalCount,
            'referredCount' => $this->referredCount,
            'referrers' => $this->referrers,
            'domains' => $this->domains,
            'targets' => $this->targets,
            'internal' => $this->internals,
            'other' => $this->other,
        ];
    }

    public function processRow(\Riimu\LogParser\LogRow $row)
    {
        $referrer = $row->getReferrer();

        if ($referrer === false) {
            $this->directCount++;
            return true;
        }

        if (isset($this->cache[$referrer])) {
            $data = $this->cache[$referrer];
        } else {
            $info = $this->parser->parseUrl($referrer);

            if ($info === null) {
                $data = ['type' => 'other'];
            } elseif (in_array($info->getHostname(), $this->internalDomains)) {
                $data = ['type' => 'internals'];
            } else {
                $data = [
                    'type' => 'referrers',
                    'domain' => $info->getHostname(),
                ];
            }

            $this->cache[$referrer] = $data;
        }

        $date = $row->getDate();
        $target = $row->getPath();

        switch ($data['type']) {
            case 'other':
                $this->addReferrer('other', $referrer, $target, $date);
                break;

            case 'internals':
                $this->internalCount++;
                if ($this->includeInternalTraffic) {
                    $this->addReferrer('internals', $referrer, $target, $date);
                }
                break;

            case 'referrers':
                $this->referredCount++;
                $this->addReferrer('domains', $data['domain'], false, $date);

                if ($this->addReferrer('referrers', $referrer, $target, $date)) {
                    $this->referrers[$referrer]['domain'] = $data['domain'];
                    $this->domains[$data['domain']]['urls'][] = $referrer;
                }
                break;
        }

        return true;
    }

    private function addReferrer($type, $referrer, $target, \DateTime $date)
    {
        $new = false;

        if (isset($this->{$type}[$referrer])) {
            $this->{$type}[$referrer]['count']++;
            $this->{$type}[$referrer]['last'] = $date->getTimestamp();
        } else {
            $this->{$type}[$referrer] = [
                'count' => 1,
                'last' => $date->getTimestamp(),
                'days' => [],
            ];

            $new = true;
        }

        if ($target !== false) {
            $this->addReferrer('targets', $target, false, $date);

            if (isset($this->targets[$target]['urls'][$referrer])) {
                $this->targets[$target]['urls'][$referrer]++;
            } else {
                $this->targets[$target]['urls'][$referrer] = 1;
            }

            if (!isset($this->{$type}[$referrer]['targets'][$target])) {
                $this->{$type}[$referrer]['targets'][$target] = 1;
            } else {
                $this->{$type}[$referrer]['targets'][$target]++;
            }
        }

        $day = $date->format('Y-m-d');

        if (isset($this->{$type}[$referrer]['days'][$day])) {
            $this->{$type}[$referrer]['days'][$day]++;
        } else {
            $this->{$type}[$referrer]['days'][$day] = 1;
        }

        return $new;
    }
}
