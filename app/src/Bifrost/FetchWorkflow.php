<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Temporal\Samples\Bifrost;

use Carbon\CarbonInterval;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow;

class FetchWorkflow implements FetchWorkflowInterface
{
    /**
     * @var ActivityProxy<FetchActivityInterface>
     */
    private ActivityProxy $fetchActivity;

    public function __construct()
    {
        /**
         * To enable activity retry set {@link RetryOptions} on {@link ActivityOptions}.
         */
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
}