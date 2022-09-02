<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Plugin;

use Adyen\Payment\Model\Api\AdyenOrderPaymentStatus;
use Adyen\Payment\Model\Ui\AdyenCcConfigProvider;
use Adyen\Payment\Model\Ui\AdyenOneclickConfigProvider;
use Magento\Sales\Api\OrderRepositoryInterface;

class ThreeDS1RedirectPlugin
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function afterGetOrderPaymentStatus(AdyenOrderPaymentStatus $adyenOrderPaymentStatus, $response, $orderId)
    {
        if (!is_string($response)) {
            return $response;
        }

        $payment = $this->orderRepository->get($orderId)->getPayment();

        if ($payment->getMethod() === AdyenCcConfigProvider::CODE ||
            $payment->getMethod() === AdyenOneclickConfigProvider::CODE
        ) {
            $response = json_decode($response, true);

            if (($response['threeDS2'] ?? false) === false && ($response['type'] ?? '') === 'RedirectShopper') {
                $additionalInformation = $payment->getAdditionalInformation();

                $response = array_merge($response, [
                    'redirect' => [
                        'data' => [
                            'PaReq' => $additionalInformation['paRequest'] ?? null,
                            'MD' => $additionalInformation['md'] ?? null,
                            'TermUrl' => null
                        ],
                        'method' => $additionalInformation['redirectMethod'] ?? 'POST',
                        'url' => $additionalInformation['redirectUrl'] ?? null
                    ]
                ]);
            }

            return json_encode($response);
        }

        return $response;
    }
}
