<?php

namespace Riimu\ReferrerParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ReferrerParser
{
    private $data;

    public function __construct()
    {
        $this->data = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'referrer_data.json'), true);
    }

    public function getInfo(\Riimu\Kit\UrlParser\UrlInfo $url)
    {
        $domain = $url->getHostname();

        foreach ($this->data as $object) {
            if (isset($object['domain']) && $object['domain'] !== $domain) {
                continue;
            } elseif (isset($object['domains']) && !in_array($domain, $object['domains'])) {
                continue;
            }
            
            return new ReferrerInfo($object);
        }

        return new ReferrerInfo([
            'type' => 'other',
        ]);
    }
}
