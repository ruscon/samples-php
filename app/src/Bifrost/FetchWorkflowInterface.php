<?php

/**
 * This file is part of Temporal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Temporal\Samples\Bifrost;

use Generator;
use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface FetchWorkflowInterface
{
    /**
     * @param string $userUuid
     * @return Generator<string>
     */
    #[WorkflowMethod(name: "Bifrost.fetch")]
    public function fetch(string $userUuid);

//    #[SignalMethod]
//    public function cancel(): void;
}