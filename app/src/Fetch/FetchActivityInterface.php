<?php

declare(strict_types=1);

namespace Temporal\Samples\Fetch;

use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;
use Temporal\DataConverter\Type;
use Temporal\Workflow\ReturnType;

#[ActivityInterface(prefix: 'Fetch.')]
interface FetchActivityInterface
{
    public function fetch(string $userUuid): array;
}