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

    private $directCount;
    private $internalCount;
    private $referredCount;

    private $referrers;
    private $internals;
    private $domains;
    private $other;

    private $cache;

    public function __construct()
    {
        $this->parser = new \Riimu\Kit\UrlParser\UrlParser();
        $this->internalDomains = [];

        $this->directCount = 0;
        $this->internalCount = 0;
        $this->referredCount = 0;

        $this->referrers = [];
        $this->internals = [];
        $this->domains = [];
        $this->other = [];

        $this->cache = [];
    }

    public function addInternalDomain($domain)
    {
        $this->internalDomains[] = $domain;
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
            'internal' => $this->internals,
            'domains' => $this->domains,
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

        switch ($data['type']) {
            case 'other':
                $this->addReferrer('other', $referrer, $date);
                break;

            case 'internals':
                $this->internalCount++;
                $this->addReferrer('internals', $referrer, $date);
                break;

            case 'referrers':
                $this->referredCount++;
                $this->addReferrer('domains', $data['domain'], $date);

                if ($this->addReferrer('referrers', $referrer, $date)) {
                    $this->referrers[$referrer]['domain'] = $data['domain'];
                    $this->domains[$data['domain']]['urls'][] = $referrer;
                }
                break;
        }

        return true;
    }

    private function addReferrer($type, $referrer, \DateTime $date)
    {
        $new = false;

        if (isset($this->{$type}[$referrer])) {
            $this->{$type}[$referrer]['count']++;

            if ($date->getTimestamp() > $this->{$type}[$referrer]['last']) {
                $this->{$type}[$referrer]['last'] = $date->getTimestamp();
            }
        } else {
            $this->{$type}[$referrer] = [
                'count' => 1,
                'last' => $date->getTimestamp(),
                'days' => [],
            ];

            $new = true;
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
