<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Gateway\Request;

use Adyen\Payment\Gateway\Request\CheckoutDataBuilder as OrigCheckoutDataBuilder;
use Adyen\Payment\Helper\ChargedCurrency;
use Adyen\Payment\Helper\Data;
use Magento\Quote\Api\CartRepositoryInterface;

class CheckoutDataBuilder extends OrigCheckoutDataBuilder
{
    private $adyenHelper;

    public function __construct(
        Data $adyenHelper,
        CartRepositoryInterface $cartRepository,
        ChargedCurrency $chargedCurrency
    ) {
        parent::__construct($adyenHelper, $cartRepository, $chargedCurrency);
        $this->adyenHelper = $adyenHelper;
    }

    public function build(array $buildSubject)
    {
        $request = parent::build($buildSubject);

        $customOrigin = $this->adyenHelper->getAdyenAbstractConfigData('origin_key_domain');
        if ($customOrigin) {
            $customPath = $this->adyenHelper->getAdyenAbstractConfigData('pwa_return_path');
            $path = $customPath ?? 'adyen/process/result';
            $request['body']['returnUrl'] = sprintf('%s/%s', $customOrigin, $path);
        }

        return $request;
    }
}
