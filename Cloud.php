<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/************************************************************************************************************************
 ************************************************************************************************************************
 * Cloud Banking Class
 *
 * @package		CloudBanking API Model
 * @author		CloudBanking Development
 * @link		https://api.cloudbanking.com.au/methods
 * @license		Apache 2.0 License [LICENSE]
 * @since		Version 2.0.0
 * @date		2018-06-28
 * @filename	Cloud.php
 * @desc		Built as an extension of a framework originally, this class allows for easy integration with the
 *	 			CloudBanking API.
 *
 *				CloudBanking supports the following gateways:
 *				- Authorize.net
 *				- Braintree
 *				- CardConnect
 *				- CyberSource
 *				- Merchant Warrior
 *				- NAB
 *				- Stripe
 *				- WireCard
 */
class Cloud  { 

# Endpoints
private	$authuri	= 'https://api.cloudbanking.com.au/version2/auth/{id}';				# Auth URI
private	$carduri	= 'https://api.cloudbanking.com.au/version2/card/add';				# Add Card URI
private	$processuri	= 'https://api.cloudbanking.com.au/version2/transaction/process';	# Process Token Endpoint
private	$adhocuri	= 'https://api.cloudbanking.com.au/version2/transaction/adhoc';		# Process Card Endpoint
private	$requesturi	= '';

# Account Settings
private $apikey		= '';		# API Key	[Found in company profile]
private $authkey	= '';		# Auth Key 	[Run _getauth or open the authuri and replace id with your authcode and ip with a sha256 encryption of your IP]
private $authip		= '';		# Auth IP	[Input the IP address of your server / website]

# Action Statuses
var	$completed		= false;	# Did the cURL call finish running (Finishing does not mean it was successful, it just means the code ran without issues)
var	$validated		= true;		# Is everything valid
var	$successful		= false;	# Was the action successful
var	$error			= '';		# Error message

# Submitted Data
var $data			= array();	# Data to submit. Use $this->data(column, value) or $this->data(array(key => value)) to add info to the data array
var $datastring		= '';		# String of variables submitted to the cURL endpoint

# Response Data
var $response;					# The response as an object / array
var $rawresp		= '';		# The raw JSON string response
var $restyp			= 'obj';	# obj or arr (Object or Array)

/* cURL Variables */
private	$_curl;						# cURL Object
public 	$curl_error		= '';		# cURL Error
public 	$curl_httpcode	= '';		# cURL HTTP Code

public function __construct()
	{
	parent::__construct();
	
	# Uncomment the next line if you want to get an authkey on class load
	# Init function includes this and runs at start of cURL _run command.
	# if($this->authkey==''){$this->_getauth();}	
	}

/**
 * Variable Setters
 *
 * Chainable methods for setting variables.
 *
 * Sample Usage
 * $this->cloud
 *		->authcode(myauthcode)
 *		->authkey(myauthkey)
 *		->authip(myauthip); 
 *
 * These allow you to dynamically pass in settings from a database instead of hardcoding them into the library.
 */

# Set authcode
public function authcode($v)	{$this->authcode 	= $v!='' ? $v : $this->authcode; return $this;}

# Set authkey
public function authkey($v)		{$this->authkey 	= $v!='' ? $v : $this->authkey; return $this;}

# Set authip
public function authip($v)		{$this->authip 		= $v!='' ? $v : $this->authip; return $this;}

# Set status of action
public function completed($v)	{$this->completed 	= $v; return $this;}

# Set validation status
public function validated($v)	{$this->validated 	= $v; return $this;}

# Set success status
public function successful($v)	{$this->successful 	= $v; return $this;}

# Set the uri to request
public function requesturi($v)	{$this->requesturi 	= $v; return $this;}

# Set the error message, mark successful and validated as false.
public function error($v)		{$this->error = $v!='' ? ($this->error!='' ? '<br />'.$v : $v) : $this->error; $this->successful(false)->validated(false); return $this;}

# cURL Variables
private function _curl_error($v)	{$this->_curl_error 	= $v;	return $this;}

private function _curl_httpcode($v)	{$this->_curl_httpcode 	= $v;	return $this;}

/**
 * data
 *
 * Chainable method that can be used to set, unset or echo variables from the data array.
 * Use this to pass in variables that will be submitted to the API.
 * See our API documentation for information on required fields for different endpoints.
 *
 * https://api.cloudbanking.com.au/methods
 *
 * Single Variable Building
 * $cloud->data(fieldname, value);
 *
 * Array Variable Building
 * $cloud->data(array(fielda => valuea, fieldb => valueb));
 *
 * Clearing a Single Variable
 * $cloud->data(fieldname, false);
 *
 * Echoing a Single Variable
 * $cloud->data(fieldname);
 */
public function data($k, $v=NULL)
	{
	# If $k is an array, merge / set the data array
	if(is_array($k))
		{
		$this->data = !empty($this->data) ? array_merge($this->data, $k) : $k;	
		}
	# Otherwise if you pass in a key ($k) and value ($v), then set the single variable
	elseif($v)
		{
		$this->data[$k] = $v; 
		}
	# If you passed in a key ($k) but said false for the value, this will unset the varaible
	elseif($v===false && isset($this->data[$k]))
		{
		unset($this->data[$k]);
		}
	# Otherise if you just passed in the key and the variable exists, it will echo the value.
	elseif(isset($this->data[$k]))
		{
		echo $this->data[$k];
		}
	
	# Chain
	return $this;
	}


/**
 * init
 *
 * Used to generate an auth key if not hard coded.
 * Can be chained into call.
 * See Sample Code for calls below for sample usage.
 * If you hard code an authkey, you do not need to run this.
 */
public function init()
	{
	# If the authkey is not hardcoded in then get it
	if($this->authkey==''){$this->_getauth();}
	
	# Clear the Raw Response Variable
	$this->rawresp = '';
	
	# Clear the Response
	$this->response = array();
	
	# Chain
	return $this;
	}


/**
 * addcard
 *
 * Used to add a card to the vault and return a token
 *
 * Sample Code
 *
 * $this->cloud
 *		->init()
 *		->data('customerid', $_POST['customerid'])
 *		->data('cardname', $_POST['cardname'])
 *		->data('cardnumber', $_POST['cardnumber'])
 *		->data('cardexpiry', $_POST['cardexpiry']) 
 *		->addcard();
 */
public function addcard()
	{
	# Make sure we have each of the required fields and if not, add to the error message [Also set successful and validated to false]
	!isset($this->data['customerid']) 	|| $this->data['customerid']=='' 	? $this->error('You must include the customer ID.') 		: true;
	!isset($this->data['cardname']) 	|| $this->data['cardname']=='' 		? $this->error('You must include the name on the card.') 	: true;
	!isset($this->data['cardnumber']) 	|| $this->data['cardnumber']=='' 	? $this->error('You must include the card number.') 		: true;
	!isset($this->data['cardexpiry']) 	|| $this->data['cardexpiry']=='' 	? $this->error('You must include the card expiry.') 		: true;
	
	# If we are valid...
	if($this->validated)
		{
		# Set the uri, add the authkey to the data and run the call
		$this	->requesturi($this->carduri)
				->data('authkey', $this->authkey)
				->_run();	
		# Response Data is in the $cloud->response variable as an array or object
		}
	
	# Chain
	return $this;	
	}


/**
 * process
 *
 * Used to process a payment via token
 *
 * Sample Code
 *
 * $this->cloud
 *		->init()
 *		->data('customerid', $_POST['customerid'])
 *		->data('cardtoken', $_POST['cardtoken'])
 *		->data('transactionamount', $_POST['transactionamount'])
 *		->process();
 */
public function process()
	{
	# Make sure we have each of the required fields and if not, add to the error message [Also set successful and validated to false]
	!isset($this->data['customerid']) 			|| $this->data['customerid']=='' 			? $this->error('You must include the customer ID.') 		: true;
	!isset($this->data['cardtoken']) 			|| $this->data['cardtoken']=='' 			? $this->error('You must include the token for the card.') 	: true;
	!isset($this->data['transactionamount']) 	|| $this->data['transactionamount']=='' 	? $this->error('You must include the transaction amount.')	: true;
	
	# If we are valid...
	if($this->validated)
		{
		# Set the uri, add the authkey to the data and run the call
		$this	
			->requesturi($this->processuri)
			->data('authkey', $this->authkey)
			->_run();
		# Response Data is in the $cloud->response variable as an array or object	
		}
	
	# Chain
	return $this;	
	}


/**
 * payment
 *
 * Used to add a card to the vault and return a token
 *
 * Sample Code
 *
 * $this->cloud
 *		->init()
 *		->data('customerid', $_POST['customerid'])
 *		->data('cardname', $_POST['cardname'])
 *		->data('cardnumber', $_POST['cardnumber'])
 *		->data('cardexpiry', $_POST['cardexpiry']) 
 *		->data('transactionamount', $_POST['transactionamount']) 
 *		->payment();
 */
public function payment()
	{
	# Make sure we have each of the required fields and if not, add to the error message [Also set successful and validated to false]
	!isset($this->data['customerid']) 			|| $this->data['customerid']=='' 			? $this->error('You must include the customer ID.') 		: true;
	!isset($this->data['cardname']) 			|| $this->data['cardname']=='' 				? $this->error('You must include the name on the card.') 	: true;
	!isset($this->data['cardnumber']) 			|| $this->data['cardnumber']=='' 			? $this->error('You must include the card number.') 		: true;
	!isset($this->data['cardexpiry']) 			|| $this->data['cardexpiry']=='' 			? $this->error('You must include the card expiry.') 		: true;
	!isset($this->data['transactionamount']) 	|| $this->data['transactionamount']=='' 	? $this->error('You must include the transaction amount.')	: true;
	
	# If we are valid...
	if($this->validated)
		{
		# Set the uri, add the authkey to the data and run the call
		$this	
			->requesturi($this->adhocuri)
			->data('authkey', $this->authkey)
			->_run();
		# Response Data is in the $cloud->response variable as an array or object	
		}
	
	# Chain
	return $this;
	}

/**
 * payment
 *
 * Used to add a card to the vault and return a token
 *
 * Sample Code
 *
 * $this->cloud
 *		->init()
 *		->data('customerid', $_POST['customerid'])
 *		->data('cardname', $_POST['cardname'])
 *		->data('cardnumber', $_POST['cardnumber'])
 *		->data('cardexpiry', $_POST['cardexpiry']) 
 *		->data('transactionamount', $_POST['transactionamount']) 
 *		->payment();
 */
public function find()
	{
	# Make sure we have each of the required fields and if not, add to the error message [Also set successful and validated to false]
	!isset($this->data['customerid']) 			|| $this->data['customerid']=='' 			? $this->error('You must include the customer ID.') 		: true;
	
	# If we are valid...
	if($this->validated)
		{
		# Set the uri, add the authkey to the data and run the call
		$this	
			->requesturi($this->adhocuri)
			->data('authkey', $this->authkey)
			->_run();
		# Response Data is in the $cloud->response variable as an array or object	
		}
	
	# Chain
	return $this;
	}


/**
 * _run [private]
 *
 * Used to run an action
 */
private function _run()
	{
	# Initialise
	$this->init();
	
	# Initialise the cURL
	$this->_curl = curl_init();	
	
	# Build our data
	$this->datastring = http_build_query($this->data);
	
	# Set some cURL options
	curl_setopt($this->_curl, CURLOPT_HEADER, false);
	curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($this->_curl, CURLOPT_TIMEOUT, 60);	
	
	# Set the URI
	curl_setopt($this->_curl, CURLOPT_URL, $this->requesturi);
	
	# Post the Data
	curl_setopt($this->_curl, CURLOPT_POST, true);
	curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $this->datastring);
	
	# Verify the connection has been intercepted
	curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);				
	# Set this to false if you do not have a valid ca-certs in your PHP instance
	# Ideally you would run with this set to true as this will load your certs
	# file and then use this to verify that the server you are connecting to
	# is indeed the CB server and not someone else.
	
	# Execute the call
	$this->rawresp = curl_exec($this->_curl);
	
	# Grab the HTML Response Code
	$this->_curl_httpcode(curl_getinfo($this->_curl, CURLINFO_HTTP_CODE))
	# Grab any curl error messages
		->_curl_error(curl_error($this->_curl));
	
	# Close the cURL connection
	curl_close($this->_curl);
	
	# Decode the response
	$response = json_decode($this->rawresp);
	
	# Mark the action as completed
	$this->completed($this->_curl_error=='' ? true : false);
	
	# If the transaction was successful...
	isset($response->trxresult) && ($response->trxresult=='1' || $response->trxresult==1)
		# Set it as successful
		? $this->successful(true)
		# Otherwise set the error message and other options
		: $this->error(isset($response->message) ? $response->message : 'An unknown error occured running the command..');		
	
	# Decode the response
	$this->response = $this->restyp=='arr' ? json_decode($this->rawresp, true) : $response;
	
	# Chain
	return $this;
	}

/**
 * _getauth [private]
 *
 * Used to generate an auth key on the fly. Generating once and hard coding is recommended as it is quicker for the end user when making a payment.
 */
private function _getauth()
	{
	# Grab the permanent auth key
	$auth = json_decode(file_get_contents(str_replace(array('{id}'), array($this->authcode), $this->authuri)));
	
	# Set the variable
	$this->authkey(isset($auth->authkey) ? $auth->authkey : '');	
	
	$this->authkey=='' ? $this->error(isset($auth->message) ? $auth->message : 'An unknown error occured.') : true;
	
	# Chain
	return $this;
	}

}