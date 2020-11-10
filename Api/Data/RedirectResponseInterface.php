<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Api\Data;

interface RedirectResponseInterface
{
    /**
    * @return int
    */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return void
     */
    public function setOrderId(int $orderId): void;

    /**
     * @return string
     */
    public function getRedirectUrl(): string;

    /**
     * @param string $orderId
     * @return void
     */
    public function setRedirectUrl(string $redirectUrl): void;
}
