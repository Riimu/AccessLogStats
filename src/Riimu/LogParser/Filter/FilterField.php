<?php

namespace Riimu\LogParser\Filter;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class FilterField implements DataFilter
{
    private $name;
    private $values;

    public function __construct($name, $values)
    {
        $this->name = $name;
        $this->values = $values;
        $this->group = is_array($values);
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
            $filter = in_array($row->getField($this->name), $this->values);
        } else {
            $filter = $row->getField($this->name) === $this->values;
        }

        return $this->reverse ? !$filter : $filter;
    }
}
