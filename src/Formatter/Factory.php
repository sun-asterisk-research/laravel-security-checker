<?php

namespace SunAsterisk\LaravelSecurityChecker\Formatter;

use Symfony\Component\Console\Helper\FormatterHelper;

class Factory
{
    public function make(string $format): Formatter
    {
        $makeFormatter = 'make'.ucfirst($format).'Formatter';

        if (!method_exists($this, $makeFormatter)) {
            return $this->makeAnsiFormatter();
        }

        return $this->$makeFormatter();
    }

    protected function makeAnsiFormatter()
    {
        $helper = new FormatterHelper();

        return new ANSI($helper);
    }

    protected function makePlainFormatter()
    {
        $helper = new FormatterHelper();
        $ansi = new ANSI($helper);

        return $ansi->setDecorated(false);
    }

    protected function makeJsonFormatter()
    {
        return new JSON();
    }

    protected function makeJunitFormatter()
    {
        return new JUnit();
    }
}
