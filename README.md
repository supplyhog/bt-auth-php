bt-auth-php
===========

A PHP interface implementation of the Blue Tarp Financial Authorization API.

Features
--------
* Use all current (as of V1) POST and GET methods of the Blue Tarp Financial Auth API.
* All responses are in a PHP array, with only the relevant sections present.

Example Request
---------------
```php
$bt = BlueTarp($yourMerchantNumber, $yourClientKey);

$auth = $bt->authorizeSale($purchaserTokenOrNumber, $dollarAmount);

$response = $auth['BT:MESSAGE'];

```

Auth Responses
--------------
```php
[BT:CODE]
[BT:MESSAGE]
[BT:TRANSACTION]
[BT:AUTH-SEQ]
[BT:APPROVAL-CODE]
```

Customer Response
-----------------
```php
[BT:CUSTOMER] => Array
	[BT:NUMBER]
	[BT:NAME]
	[BT:STATUS]
	[BT:REASON]
	[BT:ADDRESS] => Array
		[BT:LINE1]
		[BT:CITY]
		[BT:STATE]
		[BT:ZIP]
	[BT:PURCHASERS] => Array
		[BT:PURCHASER] => Array
			[BT:LAST4]
			[BT:NAME]
			[BT:TOKEN]
```

Transaction Response
--------------------
```php
[BT:TRANSACTION] => Array
  [BT:AMOUNT]
  [BT:AUTH-SEQ]
  [BT:INVOICE]
  [BT:JOB-CODE]
  [BT:CUSTOMER] => Array
  	[BT:NUMBER]
  	[BT:NAME]
  	[BT:PURCHASERS] => Array
  		[BT:PURCHASER] => Array
  			[BT:LAST4]
  			[BT:NAME]
  			[BT:TOKEN]
```

Additional Information
----------------------
* [Blue Tarp Github](https://github.com/BlueTarp)
* [Blue Tarp Financial](https://www.bluetarp.com/index)
* API Version 1
