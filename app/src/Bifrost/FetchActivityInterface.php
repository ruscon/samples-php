<?php

declare(strict_types=1);

namespace Temporal\Samples\Bifrost;

use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface(prefix: 'Bifrost.')]
interface FetchActivityInterface
{
    public function fetch(string $userUuid): array;
}