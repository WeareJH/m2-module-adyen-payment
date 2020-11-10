<?php
declare(strict_types=1);

namespace Jh\AdyenPayment\Api;

use Magento\Framework\Exception\LocalizedException;

interface AdyenThreeDSAbortInterface
{
    /**
     * @param int $orderId
     * @return void
     * @throws LocalizedException
     */
    public function abort(int $orderId): void;
}
