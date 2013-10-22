<?php

namespace Riimu\LogParser\Source;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface DataSource
{
    public function open();
    public function getNext();
    public function close();
}
