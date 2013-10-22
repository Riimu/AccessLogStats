<?php

namespace Riimu\LogParser\Filter;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface DataFilter
{
    public function reverse($reverse = true);
    public function filter(\Riimu\LogParser\LogRow $row);
}
