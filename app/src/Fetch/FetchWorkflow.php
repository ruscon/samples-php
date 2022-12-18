<?php

declare(strict_types=1);

namespace Temporal\Samples\Fetch;

use Carbon\CarbonInterval;
use Temporal\Activity\ActivityCancellationType;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\SampleUtils\Enums\QueueNameEnum;
use Temporal\Workflow;

class FetchWorkflow implements FetchWorkflowInterface
{
    /**
     * @var ActivityProxy<FetchActivityInterface>
     */
    private ActivityProxy $fetchActivity;

    public function __construct()
    {
        $this->fetchActivity = Workflow::newActivityStub(
            FetchActivityInterface::class,
            ActivityOptions::new()
                ->withScheduleToStartTimeout(CarbonInterval::hours(1))
                ->withStartToCloseTimeout(CarbonInterval::seconds(10))
//                ->withHeartbeatTimeout(CarbonInterval::seconds(5))
                // disable retries for example to run faster
                ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(3))
        );
    }

    public function fetch(string $userUuid): \Generator
    {
        return yield $this->fetchActivity->fetch($userUuid);
    }

    public function getInfo(): Workflow\WorkflowInfo
    {
        return Workflow::getInfo();
    }
}