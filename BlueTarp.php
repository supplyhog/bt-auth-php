<?php

require_once dirname(__FILE__).'/ParseResponse.php';
require_once dirname(__FILE__).'/Request.php';

/**
* A PHP interface to the Blue Tarp Financial authorization API.
* Please reference the Blue Tarp github for API documentation.
* @link https://github.com/BlueTarp
*
* @author Chaz Birge <chaz@supplyhog.com>
* @license MIT http://opensource.org/licenses/MIT
* @copyright (c) 2013, SupplyHog Inc.
* @package bt-auth-php
*/
class BlueTarp {

	/**
	 * A request object. Used to store the merchant number and client key on construction
	 * @var Request
	 */
	private $_request;

	public function __construct($merchantNumber, $clientKey) {
		$this->_request = new Request($merchantNumber, $clientKey);
	}

	/**
	 * Make a sale
	 * @param string $tokenOrNumber The 16 digit Purchaser number or token
	 * @param decimal $amount
	 * @param string $jobCode
	 * @param string $invoice
	 * @return array Auth Response
	 */
	public function authorizeSale($tokenOrNumber, $amount, $jobCode = '', $invoice = '') {
		$xml = $this->_request->postAuthRequest($tokenOrNumber, Request::getSaleXML( $amount, $jobCode, $invoice));
		return ParseResponse::parseAuthorization($xml);
	}

	/**
	 * Apply a credit
	 * @param string $tokenOrNumber The 16 digit Purchaser number or token
	 * @param decimal $amount
	 * @param string $jobCode
	 * @param string $invoice
	 * @param string $originalInvoice
	 * @return array Auth Response
	 */
	public function authorizeCredit($tokenOrNumber, $amount, $jobCode = '', $invoice = '', $originalInvoice = '') {
		$xml = $this->_request->postAuthRequest($tokenOrNumber, Request::getCreditXML( $amount, $jobCode, $invoice, $originalInvoice));
		return ParseResponse::parseAuthorization($xml);
	}

	/**
	 * Hold a deposit
	 * @param string $tokenOrNumber The 16 digit Purchaser number or token
	 * @param decimal $amount
	 * @param string $jobCode
	 * @param string $invoice
	 * @return array Auth Response
	 */
	public function authorizeDepositHold($tokenOrNumber, $amount, $jobCode = '', $invoice = '') {
		$xml = $this->_request->postAuthRequest($tokenOrNumber, Request::getDepositHoldXML( $amount, $jobCode, $invoice));
		return ParseResponse::parseAuthorization($xml);
	}

	/**
	 * Collect a held deposit
	 * @param string $tokenOrNumber The 16 digit Purchaser number or token
	 * @param decimal $amount
	 * @param string $authSequence
	 * @param string $jobCode
	 * @param string $invoice
	 * @return array Auth Response
	 */
	public function authorizeDepositCollect($tokenOrNumber, $amount, $authSequence, $jobCode = '', $invoice = '') {
		$xml = $this->_request->postAuthRequest($tokenOrNumber, Request::getDepositCollectXML( $amount, $authSequence, $jobCode, $invoice));
		return ParseResponse::parseAuthorization($xml);
	}

	/**
	 * Void a transaction
	 * @param string $tokenOrNumber The 16 digit Purchaser number or token
	 * @param string $authSequence
	 * @return array Auth Response
	 */
	public function authorizeVoid($tokenOrNumber, $authSequence) {
		$xml = $this->_request->postAuthRequest($tokenOrNumber, Request::getVoidXML( $authSequence));
		return ParseResponse::parseAuthorization($xml);
	}

	/**
	 * Get a list of all the merchants (our) customers
	 * @return array Customer Response
	 */
	public function getAllCustomers() {
		$xml = $this->_request->getRequest('customers');
		return ParseResponse::parseCustomers($xml);
	}


	/**
	 * Get all customers matching the blue tarp customer ID
	 * @param integer $merchantCID
	 * @return array Customer Response
	 */
	public function getCustomersByBTCID($blueTarpCID) {
		$xml = $this->_request->getRequest(Request::GET_CUSTOMER_BT, $blueTarpCID);
		return ParseResponse::parseCustomers($xml);
	}

	/**
	 * Get all customers matching the merchant (our) customer ID
	 * @param integer $merchantCID
	 * @return array Customer Response
	 */
	public function getCustomersByMCID($merchantCID) {
		$xml = $this->_request->getRequest(Request::GET_CUSTOMER_MERCHANT, $merchantCID);
		return ParseResponse::parseCustomers($xml);
	}

	/**
	 * Search for customers by customer name, purchaser name, or 10 digit phonen number
	 * @param string $query
	 * @return array Customer Response
	 */
	public function searchCustomers($query) {
		$xml = $this->_request->getRequest(Request::GET_CUSTOMER_QUERY, $query);
		return ParseResponse::parseCustomers($xml);
	}

	/**
	 * Return a list of all voidable transactions
	 * @return array Transaction Response
	 */
	public function getVoidableTransactions(){
		$xml = $this->_request->getRequest(Request::GET_TRANSACTION_VOID);
		return ParseResponse::parseTransactions($xml);
	}

	/**
	 * Return a list of all held transactions
	 * @return array Transaction Response
	 */
	public function getHeldTransactions(){
		$xml = $this->_request->getRequest(Request::GET_TRANSACTION_DEPOSIT);
		return ParseResponse::parseTransactions($xml);
	}

}
