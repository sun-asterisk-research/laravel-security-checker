<?php

namespace SunAsterisk\LaravelSecurityChecker\Console;

use Illuminate\Console\Command;
use SensioLabs\Security\Result;
use SensioLabs\Security\SecurityChecker;
use SunAsterisk\LaravelSecurityChecker\Formatter\Factory as FormatterFactory;
use Symfony\Component\Console\Output\StreamOutput;
use Throwable;

class SecurityCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = <<<EOF
        security:check
            {--o|format=ansi : The output format}
            {--report-json= : Store report in JSON format to a file}
            {--report-junit= : Store report in JUnit format to a file}
    EOF;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for security vulnerabilities in the project dependencies';

    /** @var SecurityChecker */
    protected $checker;

    /** @var FormatterFactory */
    protected $formatter;

    public function __construct(SecurityChecker $checker, FormatterFactory $formatter)
    {
        parent::__construct();

        $this->checker = $checker;
        $this->formatter = $formatter;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $composerLock = base_path('composer.lock');

        try {
            $result = $this->checker->check($composerLock, 'json');

            $this->printReport($result);
            $this->writeReportTofile($result, 'json');
            $this->writeReportTofile($result, 'junit');
        } catch (Throwable $e) {
            $msg = $this->getHelperSet()->get('formatter')->formatBlock($e->getMessage(), 'error', true);
            $this->output->writeln($msg);

            return 1;
        }

        return $result->count() > 0 ? 1 : 0;
    }

    /**
     * Print report to stdout
     *
     * @param  Result $result
     * @return void
     */
    protected function printReport(Result $result)
    {
        $format = $this->option('format');

        if ($format === 'ansi' && $this->option('no-ansi')) {
            $format = 'plain';
        }

        $this->formatter->make($format)->render($result, $this->output);
    }

    /**
     * Write report to a file
     *
     * @param  Result $result
     * @param  string $format
     * @return void
     */
    protected function writeReportTofile(Result $result, string $format)
    {
        $reportFile = $this->option("report-$format");

        if ($reportFile) {
            $this->line("Writing $format report to $reportFile");
            $file = fopen($reportFile, 'w+');
            $this->formatter->make($format)->render($result, new StreamOutput($file));
            fclose($file);
        }
    }
}
