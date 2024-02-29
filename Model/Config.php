<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Model;

use Adyen\Payment\Helper\Data;
use Jh\AdyenPayment\Api\Data\ConfigInterface;

class Config implements ConfigInterface
{
    private $adyenHelper;

    public function __construct(Data $adyenHelper)
    {
        $this->adyenHelper = $adyenHelper;
    }

    public function getConfig(): string
    {
        $response = [
            'environment' => $this->adyenHelper->isDemoMode() ? 'test' : 'live',
            'clientKey' => $this->adyenHelper->getClientKey(),
        ];

        return json_encode($response);
    }
}
