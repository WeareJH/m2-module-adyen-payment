<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Service;

use Adyen\Payment\Helper\Data;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Jh\AdyenPayment\Api\AdyenThreeDS1ProcessInterface;

class AdyenThreeDS1Process implements AdyenThreeDS1ProcessInterface
{
    private $adyenHelper;
    private $logger;
    private $orderRepository;
    private $session;

    public function __construct(
        Data $adyenHelper,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        Session $session
    ) {
        $this->adyenHelper = $adyenHelper;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->session = $session;
    }

    public function authorisePayment($payment, string $md, $paRes) : void
    {
        if ($payment->getAdditionalInformation('md') === $md) {
            $result = $this->authorise3d($payment, $paRes);
            $responseCode = $result['resultCode'];
            if ($responseCode == 'Authorised') {
                $payment->setAdditionalInformation('3dActive', '');
                $payment->setAdditionalInformation('3dSuccess', true);
                $payment->setAdditionalInformation("paymentsResponse", $result);
                $payment->save();
            } elseif ($responseCode === 'Refused') {
                $this->logger->debug('An error occurred during payment processing: transaction refused');
                throw new Exception(
                    'There was a problem processing your card - please check your details or try a different card'
                );
            } else {
                throw new Exception(sprintf('3D-secure validation was unsuccessful. Response Code: %s', $responseCode));
            }
        } else {
            throw new Exception('3D-secure validation was unsuccessful');
        }
    }

    public function authorise(int $orderId, string $md, string $paRes): void
    {
        try {
            /** @var Order $order */
            $order = $this->orderRepository->get($orderId);

            $this->authorisePayment($order->getPayment(), $md, $paRes);
        } catch (Exception $e) {
            if (!$order->canCancel()) {
                $order->setState(Order::STATE_NEW);
            }

            $order->cancel()->save();

            // We don't actually use the session,
            // but it provides useful methods to restore the quote from a failed order.
            // however - we do need to initialise the data it requires
            $this->session->setLastRealOrderId($order->getIncrementId());
            $this->session->restoreQuote();

            $this->logger->debug($e->getMessage());
            throw new LocalizedException(
                __('There was a problem processing your card - please check your details or try a different card')
            );
        }
    }

    private function authorise3d(\Magento\Payment\Model\InfoInterface $payment, string $paRes)
    {
        $client = $this->adyenHelper->initializeAdyenClient();
        $service = $this->adyenHelper->createAdyenCheckoutService($client);
        return $service->paymentsDetails([
            'paymentData' => $payment->getAdditionalInformation('paymentData'),
            'details' => [
                'MD' => $payment->getAdditionalInformation('md'),
                'PaRes' => $paRes
            ]
        ]);
    }
}
