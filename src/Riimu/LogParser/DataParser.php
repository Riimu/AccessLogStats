<?php

namespace Riimu\LogParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */

class DataParser
{
    private $browscap;
    private $urlParser;
    private $referrerParser;

    private $browserCache;
    private $urlInfoCache;
    private $referrerInfoCache;

    public function __construct()
    {
        $this->browscap = new \phpbrowscap\Browscap(__DIR__ . DIRECTORY_SEPARATOR . 'data');
        $this->browscap->remoteIniUrl = 'http://browscap.org/stream?q=Full_PHP_BrowsCapINI';
        $this->browscap->remoteVerUrl = 'http://browscap.org/version';

        $this->urlParser = new \Riimu\Kit\UrlParser\UrlParser();
        $this->referrerParser = new \Riimu\ReferrerParser\ReferrerParser();
    }

    public function getBrowser($agent)
    {
        if (!isset($this->browserCache[$agent])) {
            $this->browserCache[$agent] = $this->browscap->getBrowser($agent);
        }

        return $this->browserCache[$agent];
    }

    public function getUrlInfo($url)
    {
        if (!isset($this->urlInfoCache[$url])) {
            $this->urlInfoCache[$url] = $this->urlParser->parseUrl($url);
        }

        return $this->urlInfoCache[$url];
    }

    public function getReferrerInfo($referrer)
    {
        if (!isset($this->referrerInfoCache[$referrer])) {
            $urlInfo = $this->getUrlInfo($referrer);
            $this->referrerInfoCache[$referrer] = $urlInfo !== null
                ? $this->referrerParser->getInfo($urlInfo) : null;
        }

        return $this->referrerInfoCache[$referrer];
    }
}