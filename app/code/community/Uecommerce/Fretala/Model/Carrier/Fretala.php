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
		//Mage::log(Mage::getStoreConfig('shipping/origin/street_line1',Mage::app()->getStore()->getStoreId()));
		$storeId = Mage::app()->getStore()->getStoreId();
		if(!$this->getConfigData('active') || !$this->isValidProductsInCart() 
			|| !Mage::helper('fretala')->validStreetNumber(Mage::getStoreConfig('shipping/origin/street_line1',$storeId))
			)
		{
			return false;
		}
		$result = Mage::getModel('shipping/rate_result');
		$route = array(
			"from" => Mage::getStoreConfig('shipping/origin/postcode',Mage::app()->getStore()->getStoreId()),
			"to" => $this->getAreaQuote()->getShippingAddress()->getPostcode()
			);
		try{
			$fretelaApi = Mage::getModel('fretala/api_fretala');
			
			$shipping = $fretelaApi->cost($route);
			//Mage::log($shipping);
			$shipping->price = $shipping->price/100;
			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier($this->_code)
			->setCarrierTitle($this->getConfigData('title'))
			->setMethod('fretala')
			->setMethodTitle($this->getConfigData('name').' ('.$shipping->deadline.') - ')
			->setPrice($shipping->price)
			->setCost($shipping->price);


			$result->append($method);

		}catch(Exception $e){
			switch (get_class($e)) {
				case 'ValidationException':
				//Mage::log('ValidationException: '.$e->getMessage());
				if(Mage::getStoreConfig('carriers/fretala/notification_validation',$storeId)){
					$dados = 'Para o cep: '.$route['to'];
					Mage::getModel('adminnotification/inbox')->add(3, 'Freta.lá - Erro de validação na precificação', 'Retorno: <b>'.$e->getMessage().'</b><br> '.$dados, '', true);
				}
				break;

				case 'BadRequestException':
				Mage::logException($e);
				Mage::log('Precificação - '.$e->getMessage());
				Mage::log($route);
				Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro de validação na precificação', '<b>Erro de conexão</b>', true);
				
				break;

				case 'InternalErrorException':
				Mage::logException($e);
				Mage::log('Precificação - '.$e->getMessage());
				Mage::log($route);
				Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro de validação na precificação', 'Retorno: <b>'.$e->getMessage().'</b>', true);
				break;

				case 'NotFoundException':
				Mage::logException($e);
				Mage::log('Precificação - '.$e->getMessage());
				Mage::log($route);
				Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro de validação na precificação', 'Sistema Freta.la fora do ar.', true);
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