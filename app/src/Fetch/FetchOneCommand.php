<?php

declare(strict_types=1);

namespace Temporal\Samples\Fetch;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Client\WorkflowStubInterface;
use Temporal\Common\IdReusePolicy;
use Temporal\Exception\Client\WorkflowExecutionAlreadyStartedException;
use Temporal\Exception\Client\WorkflowFailedException;
use Temporal\Exception\Failure\CanceledFailure;
use Temporal\SampleUtils\Enums\QueueNameEnum;
use Temporal\SampleUtils\Command;

class FetchOneCommand extends Command
{
    protected const NAME = 'fetch:one';
    protected const DESCRIPTION = 'Execute Fetch\Fetch';
    protected const ARGUMENTS = [
        ['user', InputArgument::REQUIRED, 'User uuid'],
    ];

    private string $currentTaskQueue = QueueNameEnum::HighPriority;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
//        $userUuid = Uuid::v4();
        $userUuid = $input->getArgument('user');

        $this->terminateRunningWorkflowWithLowerPriority($userUuid);

        $output->writeln(sprintf("Start <comment>%s</comment>... ", FetchWorkflow::class));

        try {
            $workflow = $this->workflowClient->newWorkflowStub(
                FetchWorkflowInterface::class,
                WorkflowOptions::new()
                    ->withTaskQueue($this->currentTaskQueue)
//                ->withWorkflowExecutionTimeout(CarbonInterval::seconds(10))
                    ->withWorkflowIdReusePolicy(IdReusePolicy::POLICY_ALLOW_DUPLICATE)
                    ->withWorkflowId($this->generateWorkflowId($userUuid))
            );

            $run = $this->workflowClient->start($workflow, $userUuid);
        } catch (WorkflowExecutionAlreadyStartedException) {
            $output->writeln('<fg=red>Workflow execution already started</fg=red>');

            $run = $this->getRunningWorkflow($userUuid)->__getUntypedStub();
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

        try {
            $output->writeln(sprintf("Result:\n<info>%s</info>", print_r($run->getResult(), true)));
        }  catch (WorkflowFailedException $e) {
            if ($e->getPrevious() instanceof CanceledFailure) {
                $output->writeln('<fg=yellow>Cancelled</fg=yellow>');

                return self::SUCCESS;
            }

            throw $e;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<fg=red>%s</fg=red>', $e::class));
        }

        return self::SUCCESS;
    }

    protected function generateWorkflowId(string $userUuid): string {
        return "fetch:{$userUuid}";
    }

    private function terminateRunningWorkflowWithLowerPriority(string $userUuid): void
    {
        $workflow = $this->getRunningWorkflow($userUuid);

        try {
            $info = $workflow->getInfo();
        } catch (\Throwable) {
            return;
        }

        if ($info->taskQueue === $this->currentTaskQueue) {
            return;
        }

        try {
            /** @var WorkflowStubInterface $stub */
            $stub = $workflow->__getUntypedStub();
            $stub->terminate(sprintf('terminated from the "%s" task queue', $this->currentTaskQueue), [
                'terminatedFrom' => $this::class,
                'workflowType' => $stub->getWorkflowType(),
            ]);
        } catch (\Throwable) {}
    }

    private function getRunningWorkflow(string $userUuid) {
        return $this->workflowClient->newRunningWorkflowStub(
            FetchWorkflowInterface::class,
            $this->generateWorkflowId($userUuid),
        );
    }
}