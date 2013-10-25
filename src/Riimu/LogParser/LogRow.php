<?php

namespace Riimu\LogParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface LogRow
{
    public function getField($name);
    public function getDomain();
    public function getReferrer();
    public function getDate();
    public function getPath();
}
