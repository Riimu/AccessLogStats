<?php

namespace Riimu\LogParser\View;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class LinksView extends DataView
{
    private $referrers;
    private $domains;
    private $targets;

    private $noReferrer;
    private $crawlerReferrers;
    private $invalidReferrers;
    private $internalReferrers;
    private $searchReferrers;

    private $internalDomains;

    public function __construct()
    {
        parent::__construct();
        $this->setName('LinksView');

        $this->referrers = [];
        $this->domains = [];
        $this->targets = [];
        $this->noReferrer = 0;
        $this->crawlerReferrers = [];
        $this->invalidReferrers = [];
        $this->internalReferrers = [];
        $this->searchReferrers = [];

        $this->internalDomains = [];
    }

    public function addInternalDomain($domain)
    {
        $this->internalDomains[] = $domain;
        return $this;
    }

    public function getViewData()
    {
        $referrers = $this->referrers;
        $domains = $this->domains;
        $targets = $this->targets;

        ksort($referrers);
        ksort($domains);
        ksort($targets);

        $this->sortKey($referrers, 'targets');
        $this->sortKey($domains, 'referrers');
        $this->sortKey($targets, 'referrers');

        return [
            'referrers' => $referrers,
            'domains' => $domains,
            'targets' => $targets,
        ];
    }

    public function getDebugData()
    {
        $crawler = $this->crawlerReferrers;
        $invalid = $this->invalidReferrers;
        $internal = $this->internalReferrers;
        $search = $this->searchReferrers;

        ksort($crawler);
        ksort($invalid);
        ksort($internal);
        ksort($search);

        return [
            'missingReferrer' => $this->noReferrer,
            'crawler' => $crawler,
            'invalid' => $invalid,
            'internal' => $internal,
            'search' => $search,
        ];
    }

    public function processRow(\Riimu\LogParser\LogRow $row)
    {
        $url = $row->getReferrer();

        if ($url === null) {
            $this->noReferrer++;
            return true;
        }

        $agent = $row->getBrowser();

        if ($agent === null || $agent->Crawler) {
            $name = $agent === null ? 'Unknown' : $agent->Browser;
            $this->increment($this->crawlerReferrers[$name][$url]);
            return true;
        }

        if (!isset($this->referrers[$url])) {
            $info = $row->getUrlInfo();

            if (!$info) {
                $this->increment($this->invalidReferrers[$url]);
                return true;
            } elseif ($row->isSearchReferrer()) {
                $this->increment($this->searchReferrers[$url]);
                return true;
            }

            $domain = $info->getHostname();

            if (in_array($domain, $this->internalDomains)) {
                $this->increment($this->internalReferrers[$url]);
                return true;
            }

            $this->referrers[$url] = [
                'domain' => $domain,
                'days' => [],
                'targets' => [],
            ];

            if (!isset($this->domains[$domain])) {
                $this->domains[$domain] = [
                    'days' => [],
                    'referrers' => [],
                ];
            }

            $this->domains[$domain]['referrers'][] = $url;
        } else {
            $domain = $this->referrers[$url]['domain'];
        }

        $path = $row->getPath();

        if (!isset($this->targets[$path])) {
            $this->targets[$path] = [
                'days' => [],
                'referrers' => [],
            ];
        }
        if (!in_array($path, $this->referrers[$url]['targets'])) {
            $this->referrers[$url]['targets'][] = $path;
        }
        if (!in_array($url, $this->targets[$path]['referrers'])) {
            $this->targets[$path]['referrers'][] = $url;
        }

        $day = $row->getDay();
        $this->increment($this->referrers[$url]['days'][$day]);
        $this->increment($this->domains[$domain]['days'][$day]);
        $this->increment($this->targets[$path]['days'][$day]);

        return true;
    }
}
