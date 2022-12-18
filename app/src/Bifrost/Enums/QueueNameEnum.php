<?php

declare(strict_types=1);

namespace Temporal\Samples\Bifrost\Enums;
abstract class QueueNameEnum
{
    const LowPriority = 'low_priority';
    const Default = 'default';
    const HighPriority = 'high_priority';
}