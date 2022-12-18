<?php

declare(strict_types=1);

namespace Temporal\Samples\Bifrost;

use Carbon\CarbonInterval;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Common\IdReusePolicy;
use Temporal\Exception\Client\WorkflowExecutionAlreadyStartedException;
use Temporal\Samples\Bifrost\Enums\QueueNameEnum;
use Temporal\SampleUtils\Command;

class BatchFetchCommand extends Command
{
    protected const NAME = 'bifrost-batch-fetch';
    protected const DESCRIPTION = 'Execute Bifrost\Fetch';
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $userUuid = 0;
        while($userUuid++ < 10) {
            $this->fetch($output, (string)$userUuid);
        }

        return self::SUCCESS;
    }

    private function fetch(OutputInterface $output, string $userUuid) {
        $workflow = $this->workflowClient->newWorkflowStub(
            FetchWorkflowInterface::class,
            WorkflowOptions::new()
                ->withTaskQueue(QueueNameEnum::Default)
                ->withWorkflowId($this->generateWorkflowId($userUuid))
                ->withWorkflowExecutionTimeout(CarbonInterval::seconds(300))
                ->withWorkflowIdReusePolicy(IdReusePolicy::POLICY_ALLOW_DUPLICATE)
        );

        $output->writeln(sprintf("Start <comment>%s</comment>... ", FetchWorkflow::class));

        try {
            $run = $this->workflowClient->start($workflow, $userUuid);
        } catch (WorkflowExecutionAlreadyStartedException $e) {
            $output->writeln('<fg=red>Already running</fg=red>');
            return self::SUCCESS;
        }

        $output->writeln(
            sprintf(
                'Started: WorkflowID=<fg=magenta>%s</fg=magenta>, RunID=<fg=magenta>%s</fg=magenta>, userUuid=<fg=magenta>%s</fg=magenta>',
                $run->getExecution()->getID(),
                $run->getExecution()->getRunID(),
                $userUuid,
            )
        );

        $output->writeln(sprintf("Fetching user info <info>%s</info>", $userUuid));
    }

    protected function generateWorkflowId(string $userUuid): string {
        return "fetch:{$userUuid}";
    }
}