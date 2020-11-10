<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Api\Data;

interface ResultResponseInterface
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
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId(int $quoteId): void;

    /**
     * @return string
     */
    public function getResponse(): string;

    /**
     * @param string $response
     * @return void
     */
    public function setResponse(string $response): void;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $message
     * @return void
     */
    public function setMessage(string $message): void;
}
