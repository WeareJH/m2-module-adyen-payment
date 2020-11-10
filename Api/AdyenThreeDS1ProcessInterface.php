<?php
declare(strict_types=1);

namespace Jh\AdyenPayment\Api;

use Magento\Framework\Exception\LocalizedException;

interface AdyenThreeDS1ProcessInterface
{
    /**
     * @param int $orderId
     * @param string $md
     * @param string $paReq
     * @return void
     * @throws LocalizedException
     */
    public function authorise(int $orderId, string $md, string $paReq): void;

    /**
     * @param object $payment
     * @param string $md
     * @param string $paRes
     * @return void
     */
    public function authorisePayment($payment, string $md, $paRes): void;
}
