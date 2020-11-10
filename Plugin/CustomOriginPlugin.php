<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Plugin;

use Adyen\Payment\Helper\Data as Subject;

class CustomOriginPlugin
{
    public function aroundGetOrigin(Subject $subject, callable $proceed)
    {
        if ($customOrigin = $subject->getAdyenAbstractConfigData('origin_key_domain')) {
            return $customOrigin;
        }
        return $proceed();
    }
}
