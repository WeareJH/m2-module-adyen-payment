<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Service;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Jh\AdyenPayment\Api\AdyenThreeDSAbortInterface;
use Psr\Log\LoggerInterface;

class AdyenThreeDSAbort implements AdyenThreeDSAbortInterface
{
    private $logger;
    private $orderRepository;
    private $session;

    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        Session $session
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
    }

    public function abort(int $orderId): void
    {
        $order = $this->orderRepository->get($orderId);

        if (!$order->canCancel()) {
            $order->setState(Order::STATE_NEW);
        }

        $order->cancel()->save();

        // We don't actually use the session,
        // but it provides useful methods to restore the quote from a failed order.
        // however - we do need to initialise the data it requires
        $this->session->setLastRealOrderId($order->getIncrementId());
        $this->session->restoreQuote();

        $this->logger->debug('User aborted 3DS verify');
    }
}
