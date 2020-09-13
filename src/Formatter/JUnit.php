<?php

namespace SunAsterisk\LaravelSecurityChecker\Formatter;

use DateTime;
use SensioLabs\Security\Result;
use Symfony\Component\Console\Output\OutputInterface;
use XMLWriter;

class JUnit implements Formatter
{
    public function render(Result $result, OutputInterface $output)
    {
        $result = json_decode((string) $result, true);

        $xml = $this->generateReport($result);

        $output->write($xml);
    }

    protected function generateReport(array $result): string
    {
        $count = count($result);
        $time = (new DateTime())->format(DateTime::ATOM);

        $writer = new XMLWriter();

        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');

        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('testsuites');
        $writer->writeAttribute('tests', $count);
        $writer->writeAttribute('failures', $count);
        $writer->startElement('testsuite');
        $writer->writeAttribute('id', 0);
        $writer->writeAttribute('name', 'Security check');
        $writer->writeAttribute('package', 'composer.lock');
        $writer->writeAttribute('tests', $count);
        $writer->writeAttribute('failures', $count);
        $writer->writeAttribute('timestamp', $time);

        if ($count === 0) {
            $writer->startElement('testcase');
            $writer->writeAttribute('name', 'composer.lock');
            $writer->writeAttribute('classname', 'dependency-check');
            $writer->endElement();
        } else {
            foreach ($result as $package => $detail) {
                foreach ($detail['advisories'] as $advisory) {
                    $writer->startElement('testcase');
                    $writer->writeAttribute('name', $package.' '.$detail['version']);
                    $writer->writeAttribute('classname', $advisory['cve']);
                    $writer->startElement('failure');
                    $writer->writeAttribute('messaage', $advisory['title']);
                    $writer->endElement();
                    $writer->startElement('system-out');
                    $writer->text($advisory['title']."\n".$advisory['link']);
                    $writer->endElement();
                    $writer->endElement();
                }
            }
        }

        $writer->endElement();
        $writer->endElement();

        return $writer->outputMemory();
    }
}
