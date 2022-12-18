<?php

declare(strict_types=1);

namespace Temporal\Samples\Bifrost;

use Carbon\CarbonInterval;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Client\WorkflowOptions;
use Temporal\Common\IdReusePolicy;
use Temporal\Exception\Client\WorkflowExecutionAlreadyStartedException;
use Temporal\Exception\Client\WorkflowFailedException;
use Temporal\Exception\Failure\CanceledFailure;
use Temporal\Samples\Bifrost\Enums\QueueNameEnum;
use Temporal\SampleUtils\Command;

class FetchCommand extends Command
{
    protected const NAME = 'bifrost-fetch';
    protected const DESCRIPTION = 'Execute Bifrost\Fetch';
    protected const ARGUMENTS = [
        ['user', InputArgument::REQUIRED, 'User uuid'],
    ];
    public function execute(InputInterface $input, OutputInterface $output): int
    {
//        $userUuid = Uuid::v4();
        $userUuid = $input->getArgument('user');

        $workflow = $this->workflowClient->newWorkflowStub(
            FetchWorkflowInterface::class,
            WorkflowOptions::new()
                ->withTaskQueue(QueueNameEnum::HighPriority)
//                ->withWorkflowExecutionTimeout(CarbonInterval::seconds(10))
                ->withWorkflowIdReusePolicy(IdReusePolicy::POLICY_ALLOW_DUPLICATE)
                ->withWorkflowId($this->generateWorkflowId($userUuid))
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

        try {
            $output->writeln(sprintf("Result:\n<info>%s</info>", print_r($run->getResult(), true)));
        }  catch (WorkflowFailedException $e) {
            if ($e->getPrevious() instanceof CanceledFailure) {
                $output->writeln('<fg=yellow>Cancelled</fg=yellow>');

                return self::SUCCESS;
            }

            throw $e;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<fg=red>%s</fg=red>', $e::class));
        }

        return self::SUCCESS;
    }

    protected function generateWorkflowId(string $userUuid): string {
        return "fetch:{$userUuid}";
    }
}