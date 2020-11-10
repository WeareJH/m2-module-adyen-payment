# Adyen Payment module

The module extends Adyen_Payment module to allow using it with headless frontends.

### Installation

```sh
$ composer config repositories.jh-adyen-payment vcs git@github.com:WeareJH/m2-module-adyen-payment.git
$ composer require wearejh/m2-module-adyen-payment
$ php bin/magento setup:upgrade
```

### 3DS1 process stateless endpoint
    
```POST /V1/payment/adyen/threeDS1Process```

#### Request body

```	
{"orderId": 0, "md": "string", "paReq": "string"}
```

### 3DS2 process stateless endpoint
    
```POST /V1/payment/adyen/threeDS2Process```

ACL: ``self``

#### Request body

Same as for ``/V1/adyen/threeDS2Process`` but needs to contain an order ID as well. Must be a string.

```
{"orderId": 1, "payload":"{\"details\":{\"threeds2.challengeResult\":\"eyJ0cmFuc1N0YXR1cyI6IlkifQ==\"}}"}
```

### Redirect endpoint (used for getting the URL where the customer needs to be redirected)

```POST /V1/payment/adyen/redirect```

#### Request body
``` 
{
  "orderId": 35
}
```

Where `orderId` is the ID of the order placed.

#### Response
```
{
    "order_id": 33,
    "redirect_url": "https://checkoutshopper-test.adyen.com/checkoutshopper/services/PaymentIncomingRedirect/checkoutPaymentRedirect?redirectData=Ab02b4c0%21BQABAgCE5H9kiEIM1NKCsdPY6YuKJWCV2Fhf%2FjoGYS5v1KR277Nq4TDBBWdvcvQGSlEYw62ymp%2FgSgzdbw0i0Bg6zzuuEU31uA6i0M6qw8vz0a%2F7QT29E5TRFQnoGcpfMXnykDuVoranAXB9uZ8yKxCTPq%2FpP3QBD7gVZnKLwIFDL%2F2Degiv3hreq3%2Bbgjf8FtmiY58%2Fe4F4pHNrcetRXwXyN5dV3itqSo4GuYtG49sEnky5ycora4FaxDKLYW8oiPmkkH9BQsL8qKY7OFPxesCwPl82ULdtKfCQnbbPztcBJTerqke%2BnuwVIm%2BU8W8N3Y0SzaEXu8TzRE9wzZzP4z6lZqYaaBDe9j%2FNaPxaSJe6WfQJR4hBzCz7%2F%2FrmaNG8P6Jkxxtj7zqOKE98vJ%2BVJKC4ZCyjyuDMbydVWHoTlNLeAcsVSEySJA5dyEPr6xeRNhhCn7jlTRA%2F9cTjs%2B2FEPXb7cxrHSbKIzM7fU9YsR8Rz2wmDneCcxGBJIXc1JLazYTz846iFF5TKa3gIvWeFJCkb5wfNr%2BuRoya%2FmIC6coRUFbk5pg2FmfCErneXjNsOZBuHMlFd%2BHYkElh8%2FvpPEevasBs2OXhJciNBeXTMYiVZGxaSMRTsCbhrMtecpOTKHJFO5Z3%2FohvztCBWhbmnpK9BOODIuBC4HBEtAh8OshsTIUGshCRUlF6R3UZwQyW%2BooKJZ4HAEp7ImtleSI6IkFGMEFBQTEwM0NBNTM3RUFFRDg3QzI0REQ1MzkwOUI4MEE3OEE5MjNFMzgyM0Q2OERBQ0M5NEI5RkY4MzA1REMifctXOPVByodxmlvrrVx1of2wzMS%2FZ08EdVJxRP3GjuTiMI6DdAf16biomJeH%2FTyv%2BSMTi5JrfdQ01mvU%2FBZbb06N52%2Bvbjvw76vTdjUJ%2FwNzPnGoBTSoIRHLGYQ6bnaC116OHlILfbkpXKWuiflrHRyl9OwE0k1FbZp8Ykis6DKN7Bn%2B9mDERt9RvtJJBZsnAUUzX2ByVEn5wWP%2FHU2haroS%2Fs0BB%2F9MeauQ%2FT2BBvPQeNjcuZqcapwXc6q93JWM7JGEJwBLHbEaRgev1DNalKwuLUYmSVfOc0iLIa2Czvf2DkTZ%2Bun9HHiVphlrb6sU4k0d9XA%3D"
}
```

### Result endpoint (used for methods that are being redirected to 3rd party sites)

```POST /V1/payment/adyen/result```

#### Request body
``` 
{
  "orderId": 35,
  "response": "Ab02b4c0%21BQABAgAZSbJctY%2B%2FB3hooxpgBncAcn799xcEK7mLgLMXmQxbrs6MyFMBFbO0ILIbY4PZnXN6mC%2BqIfSzCTKuOPmtZVKJ5nZZkI6faRET7jhRwj%2FlAV01bDeMt8tRrMQs7tV734HkdWhEivdwMArN0K5Ty7SE1jfW4dW4psIxjC%2FDv1lfz0xqneTKmrbOaCkptOvr3493pXEPdilmcfQC1pNqKZoBYdxjXyLTyAxN88p1QWQU%2FM7sktwGw7SGuf%2FWADiWcM3YJ%2FLfPi3drzHJSFghqrNOwEhLpkHaYwQq2PkYYLZl0ehwqA2AkNvFtyg4qW1IwVKqP8L8n42MhB5uX%2FAzwK59mvNxnoW%2BZu%2Bus1umsHdNw5fF8Qs%2BqYrNBCOPnp2%2FNX5FzAyBziPDuS1Z1juWcikjN%2FJjysSeH0ZlWJUfcnNXnmSX6oYf%2BAvxAz65MgWw4qWnp4C4Pt5Qm0Erh6xTcfztEXHvjLvF%2FxSd9DQGR12eVmbuJfqHnN2qmizDQStk6SZRHyO5K8xkOvUKismaeepLxD1qpM8mm3BJPLwWzIPeZYpXTDZr%2BLp8Kg3L3nVDS0kKgTTQWI%2FUWv0JvPv%2F7tzCtAzNkQXrz4FvZuG4wI0LGKaEbzIB4d69NO%2BZI9ySpDmiW9fpSxsf06ZtXGzlix5azlU5I528QzQOijTeC%2B%2BVDxAUYspPZd%2BmEs%2FYb0nJ9QPmAEp7ImtleSI6IkFGMEFBQTEwM0NBNTM3RUFFRDg3QzI0REQ1MzkwOUI4MEE3OEE5MjNFMzgyM0Q2OERBQ0M5NEI5RkY4MzA1REMifWD6zlM7VHfbCePut1wMQbxgur5Q7mPOxwJ6Li5z2vrxcNHggx9iJ1uE0YYcOrREENU%3D"
}
```

Where `orderId` is the ID of the order placed before the redirect, and `response` is the `redirectResult` parameter the third party returns.

#### Response
```
{
    "order_id": 1,
    "quote_id": 1,
    "response": "authorized",
    "message": "Your order has been successfully placed."
}
```

