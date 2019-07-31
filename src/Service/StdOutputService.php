<?php
declare(strict_types=1);

namespace App\Service;

class StdOutputService
{

    const CONSOLE_HEADER_PRE = 'SamKnows Metric Analyser v';
    const CONSOLE_HEADER_POST = '===============================';
    const METRICS_VERSION = '1.0.0';


    /**
     * @var string
     */
    private $consoleHeaderText;

    public function __construct()
    {
        $this->consoleHeaderText = self::CONSOLE_HEADER_PRE . self::METRICS_VERSION;
    }

    public function printConsoleHeader(): void
    {
        $this->print($this->consoleHeaderText);
        $this->print(self::CONSOLE_HEADER_POST);
    }

    public function printEmptyLine(): void
    {
        $this->print('');
    }

    public function printBlock(array $array): void
    {
        foreach ($array as $elem){
            $this->print($elem);
        }

    }

    public function print(string $output): void
    {
        echo $output . PHP_EOL;
    }
}