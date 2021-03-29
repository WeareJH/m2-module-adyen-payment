<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Service;

use Jh\AdyenPayment\Api\AdyenRedirectInterface;
use Jh\AdyenPayment\Api\Data\RedirectResponseInterface;
use Jh\AdyenPayment\Api\Data\RedirectResponseInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class AdyenRedirect implements AdyenRedirectInterface
{
    private $orderRepository;
    private $redirectResponseFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RedirectResponseInterfaceFactory $redirectResponseFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->redirectResponseFactory = $redirectResponseFactory;
    }

    public function execute(int $orderId): RedirectResponseInterface
    {
        $order = $this->orderRepository->get($orderId);
        $redirectUrl = '';

        if ($order->getPayment()) {
            $redirectUrl = $order->getPayment()->getAdditionalInformation('redirectUrl') ?? '';
        }

        if (!empty($redirectUrl)) {
            $order->setState(Order::STATE_PENDING_PAYMENT);
            $order->setStatus(Order::STATE_PENDING_PAYMENT);
            $this->orderRepository->save($order);
        }

        /** @var RedirectResponseInterface $response */
        $response = $this->redirectResponseFactory->create();

        $response->setOrderId($orderId);
        $response->setRedirectUrl($redirectUrl);

        return $response;
    }
}
