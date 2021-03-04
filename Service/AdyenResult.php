<?php

declare(strict_types=1);

namespace Jh\AdyenPayment\Service;

use Adyen\Payment\Model\Notification;
use Adyen\Payment\Helper\Data;
use Adyen\Payment\Logger\AdyenLogger;
use Jh\AdyenPayment\Api\AdyenResultInterface;
use Jh\AdyenPayment\Api\Data\ResultResponseInterface;
use Jh\AdyenPayment\Api\Data\ResultResponseInterfaceFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteRepository\SaveHandler;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class AdyenResult implements AdyenResultInterface
{
    private $commentHistory = [
        'firstLine' => 'Adyen Result URL response: <br />authResult: %s <br />pspReference: %s <br />paymentMethod: %s',
        'bankTransfer' => '<br /><br />Waiting for the customer to transfer the money.',
        'sepaDirectDebit' => '<br /><br />This request will be send to the bank at the end of the day.',
        'pending' => '<br /><br />The payment result is not confirmed (yet).
                     <br />Once the payment is authorised, the order status will be updated accordingly.
                     <br />If the order is stuck on this status, the payment can be seen as unsuccessful.
                     <br />The order can be automatically cancelled based on the OFFER_CLOSED notification.
                     Please contact Adyen Support to enable this.'
    ];

    private $adyenHelper;
    private $orderRepository;
    private $cartRepository;
    private $adyenLogger;
    private $storeManager;
    private $quoteSaveHandler;
    private $resultResponseFactory;
    private $eventManager;
    private $session;

    public function __construct(
        Data $adyenHelper,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,
        SaveHandler $quoteSaveHandler,
        AdyenLogger $adyenLogger,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        ResultResponseInterfaceFactory $resultResponseFactory,
        Session $session
    ) {
        $this->adyenHelper = $adyenHelper;
        $this->orderRepository = $orderRepository;
        $this->quoteSaveHandler = $quoteSaveHandler;
        $this->cartRepository = $cartRepository;
        $this->adyenLogger = $adyenLogger;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->resultResponseFactory = $resultResponseFactory;
        $this->session = $session;
    }

    public function execute(int $orderId, string $cartId, string $response, string $resultCode = null): ResultResponseInterface
    {
        $order = $this->orderRepository->get($orderId);

        $responseArray = $this->getReturnResponse($order, $response);

        if ($resultCode) {
            $responseArray['resultCode'] = $resultCode;
        }

        $result = $this->validateUpdateOrder($order, $responseArray);
        $quote = $this->cartRepository->get($order->getQuoteId());

        $authResult = $this->getAuthResult($responseArray);

        /** @var ResultResponseInterface $resultResponse */
        $resultResponse = $this->resultResponseFactory->create();
        $resultResponse->setOrderId($orderId);
        $resultResponse->setCartId($cartId);
        $resultResponse->setQuoteId((int) $quote->getId());
        $resultResponse->setResponse(strtolower($authResult ?? 'unknown'));
        $resultResponse->setMessage($this->getMessage($authResult));

        if (!$result) {
            $this->adyenHelper->cancelOrder($order);
            $quote->setIsActive(true)->setReservedOrderId(null);
            return $resultResponse;
        }

        $quote->setIsActive(false);
        $this->quoteSaveHandler->save($quote);

        return $resultResponse;
    }

    private function getReturnResponse(Order $order, string $response)
    {
        $this->adyenLogger->addAdyenResult(sprintf('Processing ResultUrl. Order ID: %s', $order->getId()));

        $client = $this->adyenHelper->initializeAdyenClient($this->storeManager->getStore()->getId());
        $service = $this->adyenHelper->createAdyenCheckoutService($client);

        $request = [];

        $payment = $order->getPayment();
        if (!empty($payment) && !empty($payment->getAdditionalInformation('paymentData'))) {
            $request['paymentData'] = $payment->getAdditionalInformation('paymentData');
        }

        $request['details']['redirectResult'] = $response;

        $response = $service->paymentsDetails($request);

        return $response;
    }

    private function validateUpdateOrder(Order $order, array $response): bool
    {
        if (isset($response['handled_response'])) {
            return $response['handled_response'];
        }

        $this->eventManager->dispatch('adyen_payment_process_resulturl_before', [
            'order' => $order,
            'adyen_response' => $response
        ]);

        $result = false;
        $authResult = $this->getAuthResult($response);

        if (empty($authResult)) {
            $this->adyenLogger->addError(
                sprintf('Unexpected result query parameter. Response: %s', json_encode($response))
            );

            return $result;
        }

        $this->adyenLogger->addAdyenResult(sprintf('Updating the order. Order ID: %s', $order->getId()));

        $paymentMethod = isset($response['paymentMethod']) ? trim($response['paymentMethod']) : '';
        $pspReference = isset($response['pspReference']) ? trim($response['pspReference']) : '';

        $comment = sprintf(
            $this->commentHistory['firstLine'],
            $authResult,
            $pspReference,
            $paymentMethod
        );

        $order->setAdyenResulturlEventCode($authResult);

        switch (strtoupper($authResult)) {
            case Notification::AUTHORISED:
            case Notification::RECEIVED:
                $result = true;
                $this->adyenLogger->addAdyenResult(
                    sprintf('Do nothing, wait for the notification. Order ID: %s', $order->getId())
                );
                break;
            case Notification::PENDING:
                $result = true;
                $extraComment = $this->commentHistory['pending'];

                if (strpos($paymentMethod, 'bankTransfer') !== false) {
                    $extraComment = $this->commentHistory['bankTransfer'];
                }
                if ($paymentMethod == 'sepadirectdebit') {
                    $extraComment = $this->commentHistory['sepaDirectDebit'];
                }

                $comment .= $extraComment;
                $this->adyenLogger->addAdyenResult(
                    sprintf('Do nothing, wait for the notification. Order ID: %s', $order->getId())
                );
                break;
            case Notification::CANCELLED:
            case Notification::REFUSED:
            case Notification::ERROR:
                $this->restoreQuote($order);

                $this->adyenLogger->addAdyenResult(
                    sprintf('Cancel or Hold the order. Order ID: %s', $order->getId())
                );
                break;
            default:
                $this->restoreQuote($order);

                $this->adyenLogger->addAdyenResult(
                    sprintf('This event is not supported: %s', $authResult)
                );
                break;
        }

        $order->addCommentToStatusHistory($comment);
        $this->orderRepository->save($order);

        $this->eventManager->dispatch('adyen_payment_process_resulturl_after', [
            'order' => $order,
            'adyen_response' => $response
        ]);

        return $result;
    }

    private function restoreQuote(Order $order)
    {
        if (!$order->canCancel()) {
            $order->setState(Order::STATE_NEW);
        }

        $order->cancel()->save();

        // We don't actually use the session,
        // but it provides useful methods to restore the quote from a failed order.
        // however - we do need to initialise the data it requires
        $this->session->setLastRealOrderId($order->getIncrementId());
        $this->session->restoreQuote();
    }

    private function getAuthResult(array $response): string
    {
        $authResult = '';

        if (isset($response['authResult']) && !empty($response['authResult'])) {
            $authResult = $response['authResult'];
        }

        if (empty($authResult) && isset($response['resultCode']) && !empty($response['resultCode'])) {
            $authResult = $response['resultCode'];
        }

        return $authResult;
    }

    private function getMessage(string $authResult): string
    {
        switch (strtoupper($authResult)) {
            case Notification::AUTHORISED:
            case Notification::RECEIVED:
            case Notification::PENDING:
                $message = 'Your order has been successfully placed.';
                break;
            case Notification::CANCELLED:
                $message = 'Your order has been cancelled.';
                break;
            case Notification::REFUSED:
                $message = 'Your order has been refused.';
                break;
            default:
                $message = 'Something went wrong, please try again later.';
                break;
        }

        return $message;
    }
}
