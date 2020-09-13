<?php

namespace SunAsterisk\LaravelSecurityChecker\Formatter;

use SensioLabs\Security\Result;
use Symfony\Component\Console\Output\OutputInterface;

class JSON implements Formatter
{
    public function render(Result $result, OutputInterface $output)
    {
        $json = json_decode((string) $result, true);
        $formatted = json_encode($json, JSON_PRETTY_PRINT);

        $output->writeln($formatted);
    }
}
