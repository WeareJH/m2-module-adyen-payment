<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Api;

use Jh\AdyenPayment\Api\Data\ResultResponseInterface;

interface AdyenResultInterface
{
    /**
     * @param int $orderId
     * @param string $response
     * @param string $resultCode
     * @return \Jh\AdyenPayment\Api\Data\ResultResponseInterface
     */
    public function execute(int $orderId, string $response, string $resultCode = null): ResultResponseInterface;
}
