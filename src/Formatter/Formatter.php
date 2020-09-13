<?php

namespace SunAsterisk\LaravelSecurityChecker\Formatter;

use SensioLabs\Security\Result;
use Symfony\Component\Console\Output\OutputInterface;

interface Formatter
{
    public function render(Result $result, OutputInterface $output);
}
