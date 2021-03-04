<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Model;

use Jh\AdyenPayment\Api\Data\RedirectResponseInterface;

class RedirectResponse implements RedirectResponseInterface
{
    private $orderId;
    private $redirectUrl;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getCartId(): string
    {
        return $this->cartId;
    }

    public function setCartId(int $cartId): void
    {
        $this->cartId = $cartId;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }
}
