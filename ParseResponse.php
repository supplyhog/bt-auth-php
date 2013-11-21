<?php
/**
* Convert the XML response into a PHP array
*
* @author Chaz Birge <chaz@supplyhog.com>
* @license MIT http://opensource.org/licenses/MIT
* @copyright (c) 2013, SupplyHog Inc.
* @package bt-auth-php
*/
class ParseResponse{
	/**
	 * Get an array of customers from the XML response
	 * @param type $xml
	 * @return array
	   Example Response:
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
	*/
	public static function parseCustomers($xml){
		$xmlArray = ParseResponse::_xMLtoArray($xml);
		if(!isset($xmlArray['BT:BLUETARP-AUTHORIZATION']['BT:CUSTOMER-RESPONSE']['BT:CUSTOMERS'])){
			//invalid xml or no matches
			return array();
		}
		$customers = $xmlArray['BT:BLUETARP-AUTHORIZATION']['BT:CUSTOMER-RESPONSE']['BT:CUSTOMERS'];
		return $customers;
	}

	/**
	 * Get an array of customers from the XML response
	 * @param type $xml
	 * @return array
	   Example Response:
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
	 */
	public static function parseTransactions($xml){
		$xmlArray = ParseResponse::_xMLtoArray($xml);
		if(!isset($xmlArray['BT:BLUETARP-AUTHORIZATION']['BT:TRANSACTION-RESPONSE']['BT:TRANSACTIONS'])){
			//invalid xml or no matches
			return array();
		}
		$transactions = $xmlArray['BT:BLUETARP-AUTHORIZATION']['BT:TRANSACTION-RESPONSE']['BT:TRANSACTIONS'];

		return $transactions;
	}

	/**
	 * Get an authorization from the XML response
	 * @param type $xml
	 * @return array
	   Example Response:
			[BT:CODE]
			[BT:MESSAGE]
			[BT:TRANSACTION]
			[BT:AUTH-SEQ]
			[BT:APPROVAL-CODE]
	 */
	public static function parseAuthorization($xml){
		$xmlArray = ParseResponse::_xMLtoArray($xml);
		if(!isset($xmlArray['BT:BLUETARP-AUTHORIZATION']['BT:AUTHORIZATION-RESPONSE'])){
			//invalid xml or no matches
			return array();
		}
		$authorization = $xmlArray['BT:BLUETARP-AUTHORIZATION']['BT:AUTHORIZATION-RESPONSE'];

		return $authorization;
	}

	/**
	* Convert XML to an Array
	*
	* @param string $xml
	* @return array
	* @link http://www.php.net/manual/es/function.xml-parse-into-struct.php#49154
	* @author dudus at onet dot pl
	*/
   private static function _xMLtoArray($xml) {
		$xmlParser = xml_parser_create();
		xml_parse_into_struct($xmlParser, $xml, $vals);
		xml_parser_free($xmlParser);

		if (!empty($vals) && $vals[0]['tag'] === 'HTML') {
			//TODO: log HTML
			return array();
		}

		// wyznaczamy tablice z powtarzajacymi sie tagami na tym samym poziomie
		// we set tables with duplicate tags at the same level
		$tmp = '';
		foreach ($vals as $xmlElem) {
			$xTag = $xmlElem['tag'];
			$xLevel = $xmlElem['level'];
			$xType = $xmlElem['type'];
			if ($xLevel !== 1 && $xType === 'close') {
				if (isset($multiKey[$xTag][$xLevel])) {
					$multiKey[$xTag][$xLevel] = 1;
				} else {
					$multiKey[$xTag][$xLevel] = 0;
				}
			}
			if ($xLevel !== 1 && $xType === 'complete') {
				if ($tmp === $xTag) {
					$multiKey[$xTag][$xLevel] = 1;
				}
				$tmp = $xTag;
			}
		}
		// jedziemy po tablicy
		// go to the blackboard
		foreach ($vals as $xmlElem) {
			$xTag = $xmlElem['tag'];
			$xLevel = $xmlElem['level'];
			$xType = $xmlElem['type'];
			if ($xType === 'open') {
				$level[$xLevel] = $xTag;
			}
			$startLevel = 1;
			$phpStmt = '$xmlArray';
			if ($xType === 'close' && $xLevel !== 1) {
				$multiKey[$xTag][$xLevel] ++;
			}
			while ($startLevel < $xLevel) {
				$phpStmt .= '[$level[' . $startLevel . ']]';
				if (isset($multiKey[$level[$startLevel]][$startLevel]) && $multiKey[$level[$startLevel]][$startLevel]) {
					$phpStmt .= '[' . ($multiKey[$level[$startLevel]][$startLevel] - 1) . ']';
				}
				$startLevel++;
			}
			$add = '';
			if (isset($multiKey[$xTag][$xLevel]) && $multiKey[$xTag][$xLevel] && ($xType === 'open' || $xType === 'complete')) {
				if (!isset($multiKey2[$xTag][$xLevel])) {
					$multiKey2[$xTag][$xLevel] = 0;
				} else {
					$multiKey2[$xTag][$xLevel] ++;
				}
				$add = '[' . $multiKey2[$xTag][$xLevel] . ']';
			}
			if (isset($xmlElem['value']) && trim($xmlElem['value']) !== '' && !array_key_exists('attributes', $xmlElem)) {
				if ($xType === 'open') {
					$phpStmtMain = $phpStmt . '[$xType]' . $add . '[\'content\'] = $xmlElem[\'value\'];';
				} else {
					$phpStmtMain = $phpStmt . '[$xTag]' . $add . ' = $xmlElem[\'value\'];';
				}
				eval($phpStmtMain);
			}
			if (array_key_exists('attributes', $xmlElem)) {
				if (isset($xmlElem['value'])) {
					$phpStmtMain = $phpStmt . '[$xTag]' . $add . '[\'content\'] = $xmlElem[\'value\'];';
					eval($phpStmtMain);
				}
				foreach ($xmlElem['attributes'] as $key => $value) {
					$phpStmtAtt = $phpStmt . '[$xTag]' . $add . '[$key] = $value;';
					eval($phpStmtAtt);
				}
			}
		}
		return $xmlArray;
	}

}
