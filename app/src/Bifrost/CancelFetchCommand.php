<?php

declare(strict_types=1);

namespace Temporal\Samples\Bifrost;

use Carbon\CarbonInterval;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Temporal\Client\GRPC\StatusCode;
use Temporal\Client\WorkflowOptions;
use Temporal\Common\IdReusePolicy;
use Temporal\Common\Uuid;
use Temporal\Exception\Client\ServiceClientException;
use Temporal\Exception\Client\WorkflowNotFoundException;
use Temporal\SampleUtils\Command;

class CancelFetchCommand extends FetchCommand
{
    protected const NAME = 'bifrost-fetch:cancel';
    protected const DESCRIPTION = 'Execute Bifrost\Fetch cancellation by user uuid';

    protected const ARGUMENTS = [
        ['user', InputArgument::REQUIRED, 'User uuid'],
    ];
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $userUuid = $input->getArgument('user');

        $output->writeln(sprintf("Cancelling <comment>%s</comment> for user: <fg=magenta>%s</fg=magenta>", FetchWorkflow::class, $userUuid));

        $workflow = $this->workflowClient->newUntypedRunningWorkflowStub($this->generateWorkflowId($userUuid));

        try {
            $workflow->cancel();
            $output->writeln('Cancelled');
        } catch (ServiceClientException $e) {
            if ($e->getCode() === StatusCode::NOT_FOUND) {
                $output->writeln('<fg=red>Workflow not found</fg=red>');
                return self::SUCCESS;
            }

            $output->writeln(sprintf('<fg=red>%s</fg=red>', $e));
        }

        return self::SUCCESS;
    }
}