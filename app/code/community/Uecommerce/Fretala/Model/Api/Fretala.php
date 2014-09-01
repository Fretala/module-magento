<?php 
/**
 * Uecommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.uecommerce.com.br/ for more information
 *
 * @category   Uecommerce
 * @package    Uecommerce_Fretala
 * @copyright  Copyright (c) 2014 Uecommerce (http://www.uecommerce.com.br/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fretala module
 *
 * @category   Uecommerce
 * @package    Uecommerce_Fretala
 * @author     Uecommerce Dev Team
 */
define("FRETALA_SANDBOX_URL", "https://sandbox.freta.la");
define("FRETALA_PRODUCTION_URL", "https://api.freta.la");

class ValidationException extends Exception{}
class BadRequestException extends Exception{}
class InternalErrorException extends Exception{}

class Uecommerce_Fretala_Model_Api_Fretala extends Mage_Core_Model_Abstract
{
	private $token;
	private $environment; 
	private $postfields;
	private $getfield;
	private $clientId;
	private $clientSecret;
	private $username;
	private $password;
	public $url;

	protected function getConfigData($config){
		return Mage::getStoreConfig('carriers/fretala/'.$config,Mage::app()->getStore()->getStoreId());
	}

	public function __construct() {

		$this->_init('fretala/api_fretala');
		$this->environment = $this->getConfigData('environment');
		if (!in_array("curl", get_loaded_extensions())) {
			throw new Exception("You need to install cURL, see: http://curl.haxx.se/docs/install.html");
		}

		if(!isset($this->environment)) {
			throw new Exception("environment wasn\"t set");
		}

		if(!in_array($this->environment, array("sandbox", "production"))) {
			throw new Exception("environment must be production or sandbox");
		}
		if($this->environment == "production") {
			$this->url = FRETALA_PRODUCTION_URL;
		} else {
			$this->url = FRETALA_SANDBOX_URL;
		}

		$this->clientId = $this->getConfigData('client_id');
		$this->clientSecret = $this->getConfigData('client_secret');
		$this->username = $this->getConfigData('login_email');
		$this->password = $this->getConfigData('login_password');

		$this->token = '';
	}

	public function authenticate() {
		$data = array(
			"grant_type" => "password",
			"username" => $this->username,
			"password" => $this->password
			);
		$res = $this->performRequest("POST", "/authenticate", json_encode($data), true);
		$this->token = $res->access_token;
		return $res->access_token;
	}

	public function getCards() {
		$this->authenticate();
		return $this->performRequest("GET", "/cards");
	}

	public function insertCard($card) {
		$this->authenticate();
		return $this->performRequest("POST", "/cards", json_encode($card));
	}

	public function deleteCard($cardToken) {
		$this->authenticate();
		return $this->performRequest("DELETE", "/cards/".$cardToken);
	}

	public function insertFrete($frete) {
		$this->authenticate();
		return $this->performRequest("POST", "/fretes", json_encode($frete));
	}

	public function cost($cost) {
		return $this->performRequest("POST", "/fretes/cost", json_encode($cost));
	}

	private function buildHeaders($auth = false) {
		$headers = array();
		if($auth) {
			$headers[] = "Authorization: Basic " . base64_encode($this->clientId.':'.$this->clientSecret);
		} else if($this->token != "") {
			$headers[] = "Authorization: Bearer " . $this->token;
		}
		$headers[] = "Content-Type: application/json";
		return $headers;
	}

	/**
	* Perform the actual data retrieval from the API
	*
	* @param string $type GET, POST, PUT or DELETE
	* @param string $path endpoint path
	* @param string $path data to be send in POST or PUT requests
	*
	* @return string json If $return param is true, returns json data.
	*/
	private function performRequest($type, $path, $data=null, $auth=false) {
		$options = array(
			CURLOPT_HTTPHEADER => $this->buildHeaders($auth),
			CURLOPT_HEADER => false,
			CURLOPT_URL => $this->url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_URL => $this->url . $path
			);

		if ($type == "POST") {
			$options[CURLOPT_POSTFIELDS] = $data;
		} else if($type == "DELETE") {
			$options[CURLOPT_CUSTOMREQUEST] = "DELETE";
		} else if($type == "PUT") {
			$options[CURLOPT_CUSTOMREQUEST] = "PUT";
			$options[CURLOPT_POSTFIELDS] = $data;
		}

		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$json = json_decode(curl_exec($feed));
		$status = curl_getinfo($feed, CURLINFO_HTTP_CODE);
		if($status != 200 && $status != 204) {
			$err_msg = property_exists($json, 'message') ? $json->message : $json->error_description;
			if($status == 422) {
				throw new ValidationException($err_msg);
			} else if($status = 400) {
				throw new BadRequestException($err_msg);
			} else if($status = 500) {
				throw new InternalErrorException($err_msg);
			} else {
				throw new Exception($err_msg);
			}
		}
		curl_close($feed);
		$this->token = '';
		return $json;
	}
}