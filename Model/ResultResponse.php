<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Model;

use Jh\AdyenPayment\Api\Data\ResultResponseInterface;

class ResultResponse implements ResultResponseInterface
{
    private $orderId;
    private $quoteId;
    private $response;
    private $message;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getQuoteId(): int
    {
        return $this->quoteId;
    }

    public function setQuoteId(int $quoteId): void
    {
        $this->quoteId = $quoteId;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
