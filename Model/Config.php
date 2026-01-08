<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Model;

use Adyen\Payment\Helper\Config as AdyenConfig;
use Jh\AdyenPayment\Api\Data\ConfigInterface;
use JsonException;

class Config implements ConfigInterface
{
    public function __construct(private readonly AdyenConfig $adyenConfig)
    {
    }

    /**
     * @throws JsonException
     */
    public function getConfig(): string
    {
        $mode = $this->adyenConfig->isDemoMode() ? 'test' : 'live';
        $response = [
            'environment' => $mode,
            'clientKey' => $this->adyenConfig->getClientKey($mode),
        ];

        return json_encode($response, JSON_THROW_ON_ERROR);
    }
}
