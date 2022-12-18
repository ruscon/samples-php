<?php

declare(strict_types=1);

namespace Temporal\Samples\Fetch;

use Generator;
use Temporal\Workflow\QueryMethod;
use Temporal\Workflow\SignalMethod;
use Temporal\Workflow\WorkflowInfo;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface FetchWorkflowInterface
{
    /**
     * @param string $userUuid
     * @return Generator<string>
     */
    #[WorkflowMethod(name: "Fetch.fetch")]
    public function fetch(string $userUuid);

    #[QueryMethod('Fetch.get_info')]
    public function getInfo(): WorkflowInfo;

//    #[SignalMethod]
//    public function cancel(): void;
}