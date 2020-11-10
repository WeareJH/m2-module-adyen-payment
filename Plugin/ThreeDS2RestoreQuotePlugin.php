<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Plugin;

use Adyen\Payment\Model\AdyenThreeDS2Process;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;

class ThreeDS2RestoreQuotePlugin
{
    private $orderRepository;
    private $session;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Session $session
    ) {
        $this->orderRepository = $orderRepository;
        $this->session = $session;
    }

    public function afterInitiate(AdyenThreeDS2Process $subject, string $result, string $payload): string
    {
        $payload = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LocalizedException(
                __('3D secure 2.0 failed because the request was not a valid JSON')
            );
        }

        $resultDecoded = json_decode($result, true);

        if (empty($payload['orderId'])) {
            return $result;
        }

        if (($resultDecoded['result'] ?? '') === 'Authorised') {
            return $result;
        }

        $order = $this->orderRepository->get($payload['orderId']);

        // We don't actually use the session,
        // but it provides useful methods to restore the quote from a failed order.
        // however - we do need to initialise the data it requires
        $this->session->setLastRealOrderId($order->getIncrementId());
        $this->session->restoreQuote();

        return $result;
    }
}
