<?php

require '../vendor/autoload.php';

$loader = new Riimu\Kit\ClassLoader\BasePathLoader();
$loader->addBasePath('../src/');
$loader->register();

$parser = new Riimu\LogParser\LogParser(
    new Riimu\LogParser\Source\AccessLogSource('access.log'),
    'output');
$report = new Riimu\LogParser\Report('Referrers for www subdomain');
$report->addFilter(new Riimu\LogParser\Filter\FilterDomain('www.example.com'));
$report->addView((new Riimu\LogParser\View\ReferrerView())
    ->addInternalDomain('www.example.com'));

$parser->addReport($report);
$parser->process();
$parser->saveJSON();
