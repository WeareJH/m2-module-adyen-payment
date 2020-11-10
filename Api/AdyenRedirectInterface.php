<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Api;

use Jh\AdyenPayment\Api\Data\RedirectResponseInterface;

interface AdyenRedirectInterface
{
    /**
     * @param int $orderId
     * @return \Jh\AdyenPayment\Api\Data\RedirectResponseInterface
     */
    public function execute(int $orderId): RedirectResponseInterface;
}
