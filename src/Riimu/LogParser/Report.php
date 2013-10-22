<?php

namespace Riimu\LogParser;

/**
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2013, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Report
{
    private $name;
    private $filters;
    private $views;

    public function __construct($name)
    {
        $this->name = $name;
        $this->filters = [];
        $this->views = [];
    }

    public function getName()
    {
        return $this->name;
    }

    public function addFilter(Filter\DataFilter $filter)
    {
        $this->filters[] = $filter;
    }

    public function addView(View\DataView $view)
    {
        $this->views[] = $view;
    }

    public function processRow(LogRow $row)
    {
        foreach ($this->filters as $filter) {
            if ($filter->filter($row) === false) {
                return false;
            }
        }

        foreach ($this->views as $view) {
            $view->processRow($row);
        }

        return true;
    }

    public function getViews()
    {
        return $this->views;
    }
}
