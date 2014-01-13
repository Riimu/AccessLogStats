<?php

namespace Riimu\LogParser\View;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class DataView
{
    private $name;

    public function __construct()
    {
        $this->name = 'view';
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    abstract public function getViewData();
    abstract public function getDebugData();
    abstract public function processRow(\Riimu\LogParser\LogRow $row);

    protected function increment(& $value)
    {
        if (isset($value)) {
            $value += 1;
        } else {
            $value = 1;
        }
    }

    protected function sortKey(& $array, $key)
    {
        foreach ($array as & $values) {
            sort($values[$key]);
        }
    }
}
