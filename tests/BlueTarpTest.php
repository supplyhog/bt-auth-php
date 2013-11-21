<?php
foreach(glob(dirname(dirname(__FILE__)).'/*.php') as $file){
    require_once $file;
}

/**
 * A basic test to hit all Blue Tarp API endpoints
 */
class BlueTarpTest extends PHPUnit_Framework_TestCase {
	/**
	 * Your test merchant number
	 * @var integer
	 */
	private $merchantNumber = 0;

	/**
	 * Your test client key
	 * @var string
	 */
	private $clientKey = '';

	/**
	 * A token for a test customer
	 * @var string
	 */
	private $token = '';

	/**
	 * A blue tarp customer ID for a test customer
	 * @var integer
	 */
	private $btcid = 0;

	/**
	 * A name for a test customer
	 * @var string
	 */
	private $customerName = '';

	/**
	 * @var BlueTarp
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new BlueTarp($this->merchantNumber, $this->clientKey);
	}

	/**
	 * @covers BlueTarp::authorizeSale
	 */
	public function testAuthorizeSale() {
		$auth = $this->object->authorizeSale($this->token, 1);
		$this->assertNotEmpty($auth);
		$this->assertEquals($auth['BT:MESSAGE'],'APPROVED');
	}

	/**
	 * @covers BlueTarp::authorizeCredit
	 */
	public function testAuthorizeCredit() {
		$token = 'YXPjR6wLvvQ6sCYRv9AISl3eAb3Itx';
		$auth = $this->object->authorizeCredit($this->token, 1);
		$this->assertNotEmpty($auth);
		$this->assertEquals($auth['BT:MESSAGE'],'APPROVED');
	}

	/**
	 * @covers BlueTarp::authorizeDepositHold
	 */
	public function testAuthorizeDepositHold() {
		$auth = $this->object->authorizeDepositHold($this->token, 1);
		$this->assertNotEmpty($auth);
		$this->assertEquals($auth['BT:MESSAGE'],'APPROVED');
		$authSeq = $auth['BT:AUTH-SEQ'];

		$auth = $this->object->authorizeDepositCollect($this->token, 1, $authSeq);
		$this->assertNotEmpty($auth);
		$this->assertEquals($auth['BT:MESSAGE'],'APPROVED');
	}
	/**
	 * @covers BlueTarp::authorizeDepositHold
	 */
	public function testAuthorizeVoid() {
		$auth = $this->object->authorizeDepositHold($this->token, 1);
		$this->assertNotEmpty($auth);
		$this->assertEquals($auth['BT:MESSAGE'],'APPROVED');
		$authSeq = $auth['BT:AUTH-SEQ'];

		$auth = $this->object->authorizeVoid($this->token, $authSeq);
		$this->assertNotEmpty($auth);
		$this->assertEquals($auth['BT:MESSAGE'],'APPROVED');
	}

	/**
	 * @covers BlueTarp::getAllCustomers
	 */
	public function testGetAllCustomers() {
		$customers = $this->object->getAllCustomers();
		$this->assertNotEmpty($customers);
	}

	/**
	 * @covers BlueTarp::getCustomersByBTCID
	 */
	public function testGetCustomersByBTCID() {

		$customers = $this->object->getCustomersByBTCID($this->btcid);
		$this->assertNotEmpty($customers);

		$customers = $this->object->getCustomersByBTCID(0);
		$this->assertEmpty($customers);
	}

	/**
	 * @covers BlueTarp::getCustomersByMCID
	 */
	public function testGetCustomersByMCID() {
		//$customers = $this->object->getCustomersByMCID(0); //unknown mcid to test with
		//$this->assertNotEmpty($customers);

		$customers = $this->object->getCustomersByMCID(0);
		$this->assertEmpty($customers);
	}

	/**
	 * @covers BlueTarp::searchCustomers
	 */
	public function testSearchCustomers() {
		$customers = $this->object->searchCustomers($this->customerName);
		$this->assertNotEmpty($customers);

		$customers = $this->object->searchCustomers('This should be empty');
		$this->assertEmpty($customers);
	}

	/**
	 * @covers BlueTarp::getVoidableTransactions
	 */
	public function testGetVoidableTransactions() {
		$transactions = $this->object->getVoidableTransactions();
		$this->assertNotEmpty($transactions);
	}

	/**
	 * @covers BlueTarp::getHeldTransactions
	 */
	public function testGetHeldTransactions() {
		$transactions = $this->object->getHeldTransactions();
		$this->assertEmpty($transactions);

		//TODO: hold transaction and run again
	}

}
