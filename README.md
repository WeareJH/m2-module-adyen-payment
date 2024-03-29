# Adyen Payment module

The module extends Adyen_Payment module to allow using it with headless frontends.

This module is required for our older PWAs using Adyen module v9 onwards.

### Installation

```sh
$ composer config repositories.jh-adyen-payment vcs git@github.com:WeareJH/m2-module-adyen-payment.git
$ composer require wearejh/m2-module-adyen-payment
$ php bin/magento setup:upgrade
```

### Adyen Config

Returns the environment and client key:

```
/rest/V1/adyen/config
```

Response:

```
"{\"environment\":\"test\",\"clientKey\":\"test_0123456789\"}"
```

### Abort 3DS2

Aborts the 3DS2 if the client fails the 3DS check or they cancel themselves:

```
/rest/V1/adyen/threeDSAbort
```

Payload:

```
{
    "orderId": 12345
}
```
