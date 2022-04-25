<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Setup\Model\AdminAccount;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Epicor\Telemetry\Model\ApplicationInsights;
use Symfony\Component\Console\Question\Question;

/**
 * Class TelemetrySetup
 * @package Epicor\Telemetry\Console\Command
 * @author Epicor Websales Team
 */
class TelemetrySetup extends Command
{

    /**
     * @var ApplicationInsights
     */
    private $applicationInsights;

    /**
     * TelemetrySetup constructor.
     *
     * @param ApplicationInsights $applicationInsights
     */
    public function __construct(ApplicationInsights $applicationInsights)
    {
        $this->applicationInsights = $applicationInsights;
        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $description = 'Setup integration with Application Insights.';

        $this->setName('telemetry:setup')
            ->setDescription($description)
            ->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * Get list of arguments for the command
     *
     * @param int $mode The mode of options.
     *
     * @return InputOption[]
     */
    public function getOptionsList($mode = InputOption::VALUE_REQUIRED)
    {
        $requiredStr = ($mode === InputOption::VALUE_REQUIRED ? '(Required) ' : '');

        return [
            new InputOption(
                ApplicationInsights::INSTRUMENTATION_KEY_ARGUMENT,
                null,
                $mode,
                $requiredStr . ApplicationInsights::INSTRUMENTATION_KEY_ARGUMENT
            ),
            new InputOption(
                ApplicationInsights::CUSTOMER_NAME,
                null,
                $mode,
                $requiredStr . ApplicationInsights::CUSTOMER_NAME
            ),
            new InputOption(
                ApplicationInsights::CUSTOMER_CODE,
                null,
                $mode,
                $requiredStr . ApplicationInsights::CUSTOMER_CODE
            ),
            new InputOption(
                ApplicationInsights::CUSTOMER_COUNTRY,
                null,
                $mode,
                $requiredStr . ApplicationInsights::CUSTOMER_COUNTRY
            ),
            new InputOption(
                ApplicationInsights::DEPLOYMENT_TYPE,
                null,
                $mode,
                $requiredStr . ApplicationInsights::DEPLOYMENT_TYPE
            ),
        ];
    }

    /**
     * Application insights setup in interaction mode.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        if (!$input->getOption(ApplicationInsights::INSTRUMENTATION_KEY_ARGUMENT)) {
            $question = new Question('<question>Instrumentation Key:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                ApplicationInsights::INSTRUMENTATION_KEY_ARGUMENT,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(ApplicationInsights::CUSTOMER_NAME)) {
            $question = new Question('<question>Customer Name:</question> ', '');

            $this->addNotEmptyValidator($question);

            $input->setOption(
                ApplicationInsights::CUSTOMER_NAME,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(ApplicationInsights::CUSTOMER_CODE)) {
            $question = new Question('<question>Customer Code:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                ApplicationInsights::CUSTOMER_CODE,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(ApplicationInsights::CUSTOMER_COUNTRY)) {
            $question = new Question('<question>Customer Country:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                ApplicationInsights::CUSTOMER_COUNTRY,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(ApplicationInsights::DEPLOYMENT_TYPE)) {
            $question = new Question('<question>Deployment Type (1 - Epicor SaaS, 2 - On Prem):</question> ', '');
            $this->addDeploymentTypeValidator($question);

            $input->setOption(
                ApplicationInsights::DEPLOYMENT_TYPE,
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Add deployment type validator.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    private function addDeploymentTypeValidator(Question $question)
    {
        $question->setValidator(function ($value) {
            if ($value != '1' && $value != '2') {
                throw new \Exception('Invalid option entered.');
            }
            if (trim($value) == '') {
                throw new \Exception('The value cannot be empty');
            }

            return $value;
        });
    }

    /**
     * Add not empty validator.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    private function addNotEmptyValidator(Question $question)
    {
        $question->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new \Exception('The value cannot be empty');
            }

            return $value;
        });
    }

    /**
     * Sets the Instrumentation Key.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->applicationInsights->setConfigs($input->getOptions());
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }
    }
}
