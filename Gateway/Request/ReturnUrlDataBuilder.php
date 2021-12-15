<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Gateway\Request;

use Adyen\Payment\Gateway\Request\ReturnUrlDataBuilder as OrigReturnUrlDataBuilder;
use Adyen\Payment\Helper\BaseUrlHelper;
use Adyen\Payment\Helper\Data;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class ReturnUrlDataBuilder extends OrigReturnUrlDataBuilder
{
    private $adyenHelper;

    public function __construct(
        Data $adyenHelper,
        BaseUrlHelper $baseUrlHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($baseUrlHelper, $storeManager);
        $this->adyenHelper = $adyenHelper;
    }

    public function build(array $buildSubject)
    {
        $request = parent::build($buildSubject);

        /** @var PaymentDataObject $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        /** @var Order $order */
        $order = $payment->getOrder();

        $customOrigin = $this->adyenHelper->getAdyenAbstractConfigData('origin_key_domain');
        if ($customOrigin) {
            $customPath = $this->adyenHelper->getAdyenAbstractConfigData('pwa_return_path');
            $path = $customPath
                ? $customPath . '?merchantReference=' . $order->getIncrementId()
                : 'adyen/process/result?merchantReference=' . $order->getIncrementId();
            $request['body']['returnUrl'] = sprintf('%s/%s', $customOrigin, $path);
        }

        return $request;
    }
}
