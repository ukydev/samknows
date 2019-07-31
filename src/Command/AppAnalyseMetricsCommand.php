<?php declare(strict_types=1);

namespace App\Command;

use App\Service\MetricsService;
use App\Service\StdOutputService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AppAnalyseMetricsCommand
 *
 * @package App\Command
 */
class AppAnalyseMetricsCommand extends Command
{
    /**
     * @var MetricsService $metricsService
     */
    private $metricsService;

    /**
     * @var StdOutputService $stdOutputService
     */
    private $stdOutputService;

    /**
     * @var string
     */
    protected static $defaultName = 'app:analyse-metrics';

    /**
     * Configure the command.
     */
    protected function configure(): void
    {
        $this->setDescription('Analyses the metrics to generate a report.');
        $this->addOption('input', null, InputOption::VALUE_REQUIRED, 'The location of the test input');
    }

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->metricsService = new MetricsService();
        $this->stdOutputService = new StdOutputService();
    }

    /**
     * Detect slow-downs in the data and output them to stdout.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $inputArgument = $this->getFileContentFromInput($input->getOptions()['input']);

        $jsonArray = $this->getArrayFromJson($inputArgument);

        $values = $this->metricsService->getMetricValues($jsonArray);

        $dates = $this->metricsService->getDateValues($jsonArray);

        $investigate = $this->metricsService->getInvestigation($jsonArray);

        $this->outputConsoleResults($dates, $values, $investigate);
    }

    /**
     * @param string $input
     * @return string
     */
    private function getFileContentFromInput(string $input): string
    {
        if (is_file($input)) {
            return file_get_contents($input);
        }

        return $input;
    }

    private function getArrayFromJson(string $jsonString): array
    {
        return \json_decode($jsonString, true, 8, 0);
    }

    private function outputConsoleResults(array $dates, array $values, array $investigate): void
    {

        $this->stdOutputService->printConsoleHeader();
        $this->stdOutputService->printEmptyLine();
        $this->stdOutputService->printBlock([
            'Period checked:',
            '',
            '    From: ' . $dates['from'],
            '    To:   ' . $dates['to'],
        ]);
        $this->stdOutputService->printEmptyLine();
        $this->stdOutputService->printBlock([
            'Statistics:',
            '',
            '    Unit: Megabits per second',
            '',
            '    Average: ' . $values['average'],
            '    Min: ' . $values['min'],
            '    Max: ' . $values['max'],
            '    Median: ' . $values['median'],
        ]);

        if(!empty($investigate)){
            $this->stdOutputService->printEmptyLine();
            $this->stdOutputService->printBlock([
                'Investigate:',
                '',
                '    * The period between ' . $investigate['from'] . ' and ' . $investigate['to'],
                '      was under-performing.'
            ]);
            $this->stdOutputService->printEmptyLine();
        }
    }

}
