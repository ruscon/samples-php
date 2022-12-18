<?php

declare(strict_types=1);

use Temporal\Samples\Bifrost\Enums\QueueNameEnum;
use Temporal\Samples\Bifrost\FetchActivity;
use Temporal\Samples\Bifrost\FetchWorkflow;
use Temporal\Worker\WorkerOptions;

ini_set('display_errors', 'stderr');
require __DIR__ . '/../../vendor/autoload.php';

$factory = Temporal\WorkerFactory::create();

$highPriorityWorker = $factory->newWorker(
    QueueNameEnum::HighPriority,
    WorkerOptions::new()
//        ->withMaxConcurrentActivityExecutionSize(5)
);
$highPriorityWorker->registerWorkflowTypes(FetchWorkflow::class);
$highPriorityWorker->registerActivity(FetchActivity::class);
$highPriorityWorker->registerActivityFinalizer(static function (): void { echo '$highPriorityWorker'; });

$defaultQueueWorker = $factory->newWorker(
    QueueNameEnum::Default,
    WorkerOptions::new()
        ->withMaxConcurrentActivityExecutionSize(2)
);
$defaultQueueWorker->registerWorkflowTypes(FetchWorkflow::class);
$defaultQueueWorker->registerActivity(FetchActivity::class);


$factory->run();
