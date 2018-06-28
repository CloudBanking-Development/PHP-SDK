# PHP-SDK

PHP Class for Integration with CloudBanking / ReadyPay / TomiPay.

The following class was built to provide a lightweight, easily 
transferable PHP class for connecting to the CloudBanking 
Payments Platform.

It is provided as is and is still a work in progress.

CloudBanking does not currently provide SDK for apps as we offer 
a whitelabelling service.

Contact us on sales@cloudbanking.com for more info.


## Integration

CloudBanking not only provides a singular set of endpoints for
your merchant integrations, we allow you to have multiple
merchant facilities configured and you can choose what card type
goes through what gateway, allowing you to take advantage of
the most competitive rates on the market through the vendor
of your choice.

Currently, CloudBanking supports the following gateways;

- Authorize.net
- Braintree
- CardConnect
- CyberSource
- Merchant Warrior
- NAB [National Australia Bank]
- Stripe
- WireCard

More integrations are on the way and our physical terminals can
also work with any of the above gateways and merchant facilities.


## Connecting

To connect to the CloudBanking API, you will need your Private 
API Key.

Once you have your Private API Key, this can be used to retrieve
an `authkey` from CloudBanking.

If you are working from a static IP, you can configure permanent
authentication for that IP.

If you are working from a dynamic IP, can simply pass in the 
`apikey` and the system will connect and generate a temporary
`authkey` for the request.


### Permanent Authentication

To permanently authenticate a fixed IP with CloudBanking, you
can make a `GET` request to the following URI;

```
https://api.cloudbanking.com.au/version2/auth/{APIKEY}/{SHA256-IP}
```
{APIKEY} should be replaced with your API Key.
{SHA256-IP} should be a sha-256 encode of your IP address.

This must match the IP address that makes the request for auth.

If authenticated, you will be returned with a permanent `authkey`.

You can then save this as the `$authkey` in the Cloud class file.

When calls are made, it will send your permanent key instead of
having to validate each time, resulting in quicker processing
of your transaction.


## Configuration

To use the CloudBanking PHP SDK, you will need your API Key.

This can be found in your company / merchant profile under API.

The settings that you can edit include;

### $apikey
This is your API Key from your CB Profile.

### $authkey
You can leave blank or generate a permanent authkey and save on
file to speed up the transaction process.

### $resptype
You can select if you want the `$response` variable in the class
to be returned as an `object` or as an `array`.

The settings are either `obj` (default) or `arr`.

Object Example
```
$cloud->response->success;
```
Array Example
```
$cloud->response['success'];
```


## Endpoints

CloudBanking offers a secure credit card vault for storing credit
cards in exchange for a token, as well as offering ad-hoc payments
and your payments can then settle through to MYOB or XERO.

The endpoints are listed on our API documentation but at this time
they include the following:

- card/add
- card/remove
- customer/cards
- transaction/process
- transaction/adhoc
- transaction/refund*
- transaction/find

*No function is included for refunds in the supplied SDK.


## Sample Usage

CloudBanking designed its PHP class to be lightweight and compact.
Available as a singular class file, here is a example of how to
use the CloudBanking PHP SDK.
```
$cloud = new Cloud();
  
$cloud
  ->data('cardnumber', '4444333322221111')
  ->data('cardexpiry', '12/2021')
  ->data('cardname', 'Cardholder Name')
  ->data('customerid', '123456')
  ->addcard();
```  


## Response

You can choose if you want the response to be returned as an array
or as an object.

Using the above example, if you have selected `array` as your
response method, it will look something like this:
```
$cloud->response['success'] = 1;
$cloud->response['trxresult'] = 1;
```
If you have selected `object` as your method, it will look 
something like this:
```
$cloud->response->success = 1;
$cloud->response->trxresult = 1;
```

You can view a full set of response variables for a transaction
on our API documentation found here;

https://api.cloudbanking.com.au/methods
