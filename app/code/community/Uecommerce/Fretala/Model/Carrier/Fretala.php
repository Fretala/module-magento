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

class Uecommerce_Fretala_Model_Carrier_Fretala extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
	protected $_code = 'fretala';

	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		if(!$this->getConfigData('active') || !$this->isValidProductsInCart()){
			return false;
		}
		$result = Mage::getModel('shipping/rate_result');
		try{
			$fretelaApi = Mage::getModel('fretala/api_fretala');
			$route = array(
				"from" => array(
					//"number" => "234",
					"street" => Mage::getStoreConfig('shipping/origin/street_line1'),
					"city" => Mage::getStoreConfig('shipping/origin/city'), 
					"state" => Mage::getStoreConfig('shipping/origin/region_id')
					),
				"to" => $this->getAreaQuote()->getShippingAddress()->getPostcode()
				);
			$shipping = $fretelaApi->cost($route);
			Mage::log($shipping);
			$shipping->price = $shipping->price/100;
			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier($this->_code)
			->setCarrierTitle($this->getConfigData('title'))
			->setMethod('fretala')
			->setMethodTitle($this->getConfigData('name'))
			->setPrice($shipping->price)
			->setCost($shipping-price);

			$result->append($method);

		}catch(Exception $e){
			switch (get_class($e)) {
				case 'ValidationException':
				Mage::log('ValidationException: '.$e->getMessage());
				break;

				case 'BadRequestException':
				Mage::log('BadRequestException: '.$e->getMessage());
				break;

				case 'InternalErrorException':
				Mage::log('InternalErrorException: '.$e->getMessage());
				break;
				
				default:
					# code...
				break;
			}
		}

		return $result;
		
	}

	public function getAllowedMethods() {

		return array('fretala'=>$this->getConfigData('name'));
	}

	public function isTrackingAvailable() {

	}

	public function getAreaQuote(){
		if (Mage::app()->getStore()->isAdmin()) {
			$quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
		} else {
			$quote = Mage::getSingleton('checkout/session')->getQuote();
		}
		return $quote;
	}

	public function isValidProductsInCart(){
		$items = $this->getAreaQuote()->getAllVisibleItems();
		foreach($items as $item){
			$product = $item->getProduct();
			if(!$product->getData('fretala_vip_delivery')){
				return false;
			}
			return true;
		}
	}
}