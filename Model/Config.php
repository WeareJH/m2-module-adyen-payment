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

    public function getEnvironment(): string
    {
        return $this->adyenHelper->isDemoMode() ? 'test' : 'live';
    }
}
