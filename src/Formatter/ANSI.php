<?php

namespace SunAsterisk\LaravelSecurityChecker\Formatter;

use SensioLabs\Security\Result;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

class ANSI implements Formatter
{
    /** @var FormatterHelper */
    protected $helper;

    /** @var bool */
    protected $decorated = true;

    public function __construct(FormatterHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param  bool $decorated
     * @return self
     */
    public function setDecorated(bool $decorated)
    {
        $this->decorated = $decorated;

        return $this;
    }

    public function render(Result $result, OutputInterface $output)
    {
        $result = json_decode((string) $result, true);

        $output->setDecorated($this->decorated);

        $this->printSummary($result, $output);

        if (!empty($result)) {
            $this->printDetails($result, $output);
        }
    }

    protected function printSummary(array $results, OutputInterface $output)
    {
        $vulnerablePackagesCount = count($results);

        if ($vulnerablePackagesCount === 0) {
            $this->writeln($output, 'No package has known vulnerabilities', 'info');

            return;
        }

        $this->writeln($output, 'Security Check Report', 'comment');
        $this->writeln($output, $this->fence('=', 21), 'comment');

        $has = $vulnerablePackagesCount > 1 ? 'packages have' : 'package has';
        $msg = "$vulnerablePackagesCount $has known vulnerabilities";

        $this->block($output, $msg, 'error');
    }

    protected function printDetails(array $results, OutputInterface $output)
    {
        foreach ($results as $name => $details) {
            $version = $details['version'];
            $package = "$name ($version)";

            $this->writeln($output, $package, 'comment');
            $this->writeln($output, $this->fence('-', strlen($package)), 'comment');
            $this->writeln($output, '');

            foreach ($details['advisories'] as $advisory) {
                $cve = $advisory['cve'];
                $title = substr($advisory['title'], strlen($cve) + 2);

                $this->write($output, ' * [');
                $this->write($output, $cve, 'info');
                $this->write($output, ']: ');
                $this->writeln($output, $title);
                $this->writeln($output, '   '.$advisory['link']);
                $output->writeln('');
            }
        }
    }

    protected function fence(string $char, int $length)
    {
        return str_repeat($char, $length);
    }

    protected function block(OutputInterface $output, string $msg, string $style)
    {
        $block = $this->helper->formatBlock($msg, $style, true);

        $this->writeln($output);
        $this->writeln($output, $block);
        $this->writeln($output);
    }

    protected function write(
        OutputInterface $output,
        $msg = '',
        ?string $style = null,
        bool $newLine = false,
        int $options = 0
    ) {
        $styled = $style ? $this->styled($msg, $style) : $msg;

        $output->write($styled, $newLine, $options);
    }

    protected function writeln(OutputInterface $output, $msg = '', ?string $style = null, int $options = 0)
    {
        $this->write($output, $msg, $style, true, $options);
    }

    protected function styled(string $text, string $style)
    {
        return sprintf('<%s>%s</>', $style, $text);
    }
}
