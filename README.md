# PHP-SDK

PHP Class for Integration with CloudBanking / ReadyPay / TomiPay.

The following class was built to provide a lightweight, easily 
transferable PHP class for connecting to the CloudBanking 
Payments Platform.

It is provided as is and is still a work in progress.

CloudBanking does not currently provide SDK for apps as we offer 
a whitelabelling service.

Contact us on sales@cloudbanking.com for more info.


## Configuration

To use the CloudBanking PHP SDK, you will need your API Key.

This can be found in your company / merchant profile under API.

The system will then authenticate prior to each call using your
API Key.


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
	->data('cardname', 'Cloud Payman')
	->data('customerid', '123456')
	->addcard();
```  

## Response

You can choose if you want the response to be returned as an array
or as an object.

Using the above example, if you have selected `array` as your
method, it will look something like this:
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
