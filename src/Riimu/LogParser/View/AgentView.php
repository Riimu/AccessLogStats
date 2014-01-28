<?php

namespace Riimu\LogParser\View;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class AgentView extends DataView
{
    private $crawlers;
    private $browsers;
    private $versions;
    private $noUserAgent;
    private $invalidAgents;

    public function __construct()
    {
        parent::__construct();
        $this->setName('AgentView');

        $this->crawlers = [];
        $this->browsers = [];
        $this->versions = [];
        $this->noUserAgent = 0;
        $this->invalidAgents = [];
    }

    public function getViewData()
    {
        $crawlers = $this->crawlers;
        $browsers = $this->browsers;
        $versions = $this->versions;

        ksort($crawlers);
        ksort($browsers);
        ksort($versions);

        foreach ($versions as & $names) {
            ksort($names);
        }

        return [
            'browsers' => $browsers,
            'versions' => $versions,
            'crawlers' => $crawlers,
        ];
    }

    public function getDebugData()
    {
        $invalid = $this->invalidAgents;

        ksort($invalid);

        return [
            'missingAgent' => $this->noUserAgent,
            'invalid' => $invalid,
        ];
    }

    public function processRow(\Riimu\LogParser\LogRow $row)
    {
        $agent = $row->getUserAgent();

        if ($agent === null) {
            $this->noUserAgent++;
            return true;
        }

        $browser = $row->getBrowser();

        if ($browser === null) {
            $this->increment($this->invalidAgents[$agent]);
            return true;
        }

        $day = $row->getDay();

        if ($browser->Crawler) {
            $this->increment($this->crawlers[$browser->Browser][$day]);
        } else {
            $this->increment($this->browsers[$browser->Browser][$day]);
            $this->increment($this->versions[$browser->Browser][$browser->Version][$day]);
        }

        return true;
    }
}
