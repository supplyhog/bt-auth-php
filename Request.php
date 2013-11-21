<?php

/**
 * This class constructs and makes all API requests to the Blue Tarp Financial auth API
 *
 * @author Chaz Birge <chaz@supplyhog.com>
 * @license MIT http://opensource.org/licenses/MIT
 * @copyright (c) 2013, SupplyHog Inc.
 * @package bt-auth-php
 */
class Request {

	/**
	 * The merchant number provided by Blue Tarp
	 * @var integer
	 */
	private $_merchantNumber;

	/**
	 * The client key provided by Blue Tarp used to access the API
	 * @var string
	 */
	private $_clientKey;

	/**
	 * The base API url
	 */
	const URL = 'https://integration.bluetarp.com/auth/v1';
	//list of GET urls
	const GET_CUSTOMER_QUERY = 'customers?q=';
	const GET_CUSTOMER_BT = 'customers?bluetarp-cid=';
	const GET_CUSTOMER_MERCHANT = 'customers?merchant-cid=';
	const GET_TRANSACTION_VOID = 'transactions/void';
	const GET_TRANSACTION_DEPOSIT = 'transactions/deposit';

	public function __construct($merchantNumber, $clientKey) {
		$this->_merchantNumber = $merchantNumber;
		$this->_clientKey = $clientKey;
	}

	/**
	 * Send a GET request
	 * @param GET_ $type
	 * @param string $query
	 * @return string
	 */
	public function getRequest($type, $query = '') {
		$url = Request::URL . "/{$this->_merchantNumber}/" . $type . urlencode($query);
		return $this->_curlRequest($url);
	}

	/**
	 * Choose which POST type to used based on checking the token or number and send a POST request
	 * @param type $tokenOrNumber
	 * @param type $xml
	 * @return type
	 */
	public function postAuthRequest($tokenOrNumber, $xml) {
		if (strlen($tokenOrNumber) === 16) { //16 digit numbers are always 16 characters. Tokens appear to be 30
			return $this->postAuthRequestNumber($tokenOrNumber, $xml);
		} else {
			return $this->postAuthRequestToken($tokenOrNumber, $xml);
		}
	}

	/**
	 * Send a POST request with a token
	 * @param type $token
	 * @param type $xml
	 * @return string
	 */
	public function postAuthRequestToken($token, $xml) {
		//build request body
		$requestXML = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
		 <bt:bluetarp-authorization xmlns:bt=\"http://api.bluetarp.com/ns/1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://api.bluetarp.com/ns/1.0 https://api.bluetarp.com/v1/Authorization.xsd\">
			<bt:authorization-request>
				<bt:merchant-number>{$this->_merchantNumber}</bt:merchant-number>
				<bt:client-id>Test</bt:client-id>
				<bt:transaction-id>6aa73f01-4557-454f-9279-e84526a61246</bt:transaction-id>
				<bt:purchaser-with-token>
					<bt:token>{$token}</bt:token>
				</bt:purchaser-with-token>
				{$xml}
			</bt:authorization-request>
		 </bt:bluetarp-authorization>";

		$url = Request::URL . "/{$this->_merchantNumber}/";
		return $this->_curlRequest($url, $requestXML);
	}

	/**
	 * Send a POST request with a token
	 * @param type $token
	 * @param type $xml
	 * @return string
	 */
	public function postAuthRequestNumber($number, $xml) {
		//build request body
		$requestXML = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
		 <bt:bluetarp-authorization xmlns:bt=\"http://api.bluetarp.com/ns/1.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://api.bluetarp.com/ns/1.0 https://api.bluetarp.com/v1/Authorization.xsd\">
			<bt:authorization-request>
				<bt:merchant-number>{$this->_merchantNumber}</bt:merchant-number>
				<bt:client-id>Test</bt:client-id>
				<bt:transaction-id>6aa73f01-4557-454f-9279-e84526a61246</bt:transaction-id>
				<bt:purchaser>
					<bt:number>{$number}</bt:number>
			    </bt:purchaser>
				{$xml}
			</bt:authorization-request>
		 </bt:bluetarp-authorization>";

		$url = Request::URL . "/{$this->_merchantNumber}/";
		return $this->_curlRequest($url, $requestXML);
	}

	/**
	 * Make a curl request, standardized with Blue Tarp API required headers
	 * Default to 15 second timeout
	 * @param string $url
	 * @param string $requestXML
	 * @return string
	 */
	private function _curlRequest($url, $requestXML = null) {
		//build curl request
		$curl = curl_init();
		$header = array();
		$header[] = "Authorization: Bearer {$this->_clientKey}";

		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($curl, CURLOPT_POST, isset($requestXML));
		curl_setopt($curl, CURLOPT_NOBODY, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);

		if (isset($requestXML)) {
			//add post specific info
			curl_setopt($curl, CURLOPT_POSTFIELDS, $requestXML);
			$header[] = "Content-Type: text/xml";
		}

		//add headers after post data is added
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

		$output = curl_exec($curl);

		//check if there is a response
		if ($output === false) {
			$errorNumber = curl_errno($curl);
			curl_close($curl);

			if ($errorNumber === 28)
				throw new Exception ('Request timed out.');
			else
				throw new Exception ('Error with request. Error Number: ' . $errorNumber);
		}

		curl_close($curl);

		return $output;
	}

	/**
	 * Build XML parameter for sale
	 * @param type $amount
	 * @param type $jobCode
	 * @param type $invoice
	 * @return xml
	 */
	public static function getSaleXML($amount, $jobCode = '', $invoice = '') {
		return "<bt:sale>
					<bt:amount>{$amount}</bt:amount>
					<bt:job-code>{$jobCode}</bt:job-code>
					<bt:invoice>{$invoice}</bt:invoice>
				</bt:sale>";
	}

	/**
	 * Build XML parameter for credit
	 * @param decimal $amount
	 * @param string $jobCode
	 * @param string $invoice
	 * @param string $originalInvoice
	 * @return xml
	 */
	public static function getCreditXML($amount, $jobCode = '', $invoice = '', $originalInvoice = '') {
		return "<bt:credit>
					<bt:amount>{$amount}</bt:amount>
					<bt:job-code>{$jobCode}</bt:job-code>
					<bt:invoice>{$invoice}</bt:invoice>
					<bt:original-invoice>{$originalInvoice}</bt:original-invoice>
               </bt:credit>";
	}

	/**
	 * Build XML parameter for deposit hold
	 * @param decimal $amount
	 * @param string $jobCode
	 * @param string $invoice
	 * @return xml
	 */
	public static function getDepositHoldXML($amount, $jobCode = '', $invoice = '') {
		return "<bt:deposit-hold>
					<bt:amount>{$amount}</bt:amount>
					<bt:job-code>{$jobCode}</bt:job-code>
					<bt:invoice>{$invoice}</bt:invoice>
				</bt:deposit-hold>";
	}

	/**
	 * Build XML parameter for deposit collect
	 * @param decimal $amount
	 * @param string $authSequence
	 * @param string $jobCode
	 * @param string $invoice
	 * @return xml
	 */
	public static function getDepositCollectXML($amount, $authSequence, $jobCode = '', $invoice = '') {
		return "<bt:deposit-collect>
					<bt:amount>{$amount}</bt:amount>
					<bt:auth-seq>{$authSequence}</bt:auth-seq>
					<bt:job-code>{$jobCode}</bt:job-code>
					<bt:invoice>{$invoice}</bt:invoice>
				</bt:deposit-collect>";
	}

	/**
	 * Build XML parameter for void
	 * @param string $authSequence
	 * @return xml
	 */
	public static function getVoidXML($authSequence) {
		return "<bt:void>
					<bt:auth-seq>{$authSequence}</bt:auth-seq>
				</bt:void>";
	}

}
