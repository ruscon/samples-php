<?php

declare(strict_types=1);

namespace Temporal\Samples\Bifrost;

use Psr\Log\LoggerInterface;
use Temporal\SampleUtils\Logger;

class FetchActivity implements FetchActivityInterface
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function fetch(string $userUuid): array
    {
        $this->log('Fetch user %s info', $userUuid);
        // Emulate long response
        sleep(3);

        $this->log('User %s successfully fetched', $userUuid);

        return [
            'user_uuid' => $userUuid,
        ];
    }

    /**
     * @param string $message
     * @param mixed ...$arg
     */
    protected function log(string $message, ...$arg)
    {
        // by default all error logs are forwarded to the application server log and docker log
        $this->logger->debug(sprintf($message, ...$arg));
    }
}