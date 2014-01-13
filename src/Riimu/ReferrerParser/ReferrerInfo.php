<?php

namespace Riimu\ReferrerParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ReferrerInfo
{
    private $fields;

    public function __construct($info)
    {
        $this->fields = $info;
    }

    public function getType()
    {
        return $this->fields['type'];
    }

    public function getName()
    {
        return isset($this->fields['name']) ? $this->fields['name'] : null;
    }
}
