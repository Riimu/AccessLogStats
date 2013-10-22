<?php

namespace Riimu\LogParser\Filter;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class FilterDomain implements DataFilter
{
    private $domain;

    public function __construct($domain)
    {
        $this->domain = $domain;
        $this->group = is_array($domain);
        $this->reverse = false;
    }

    public function reverse($reverse = true)
    {
        $this->reverse = (bool) $reverse;
        return $this;
    }

    public function filter(\Riimu\LogParser\LogRow $row)
    {
        if ($this->group) {
            $filter = in_array($row->getDomain(), $this->domain);
        } else {
            $filter = $row->getDomain() === $this->domain;
        }

        return $this->reverse ? !$filter : $filter;
    }
}
