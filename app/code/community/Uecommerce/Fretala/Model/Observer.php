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
class Uecommerce_Fretala_Model_Observer
{
	protected $shippingConfigPath;
	protected $storeId;
	public function checkSettings($observer){
		$controller = $observer->getEvent()->getControllerAction();
		if($controller->getFullActionName() == 'adminhtml_system_config_edit' && $controller->getRequest()->getParam('section') == 'carriers'){
			$this->shippingConfigPath = 'shipping/origin/';
			$this->storeId = Mage::app()->getStore()->getStoreId();
			$street = $this->getShippingConfig('street_line1');
			$postcode = $this->getShippingConfig('postcode');
			$streetNumber = $this->validStreet($street);
			if($this->getShippingConfig('region_id') == ''
				|| $postcode == ''
				|| !$this->validPostcode($postcode)
				|| $this->getShippingConfig('city') == ''
				|| $street == ''
				|| !$streetNumber)
			{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fretala')->__('There is something wrong with the address settings from the store, %s click here check %s','<a href="'.$controller->getUrl('adminhtml/system_config/edit/section/shipping').'">','</a>'));
			}

		}
	}

	public function getShippingConfig($config){
		return Mage::getStoreConfig($this->shippingConfigPath.$config,$this->storeId);
	}

	public function validPostcode($postcode) {
		$validPostcode = Mage::helper('fretala')->validPostcode($postcode);
		if(!$validPostcode){
			Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('fretala')->__('The format of the postcode should be xxxxx-xxx or xxxxxxxx'));
		}
		return $validPostcode;
	}

	public function validStreet($street){
		$validStreet = Mage::helper('fretala')->validStreetNumber($street);
		if(!$validStreet){
			Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('fretala')->__('The format of the address of your shop is incorrect, please follow the following format: Street, number'));
		}
		return $validStreet;
	}

	public function createShipping($observer){
		if(Mage::getStoreConfig('carriers/fretala/shipping_mode',Mage::app()->getStore()->getStoreId()) == 'automatic'){
			$shippingMethod = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
			if($shippingMethod == 'fretala_fretala'){
				$this->shippingConfigPath = 'shipping/origin/';
				$order = $observer->getOrder();
				$customerStreet = '';
				foreach($order->getShippingAddress()->getStreet() as $street){
					$customerStreet .= $street.' ';
				}
				$streetStore = Mage::helper('fretala')->validStreetNumber($this->getShippingConfig('street_line1'));

                $frete = array(
                    "id" => $order->getIncrementId(),
                    "productValue" => $order->getSubtotal()*100,
                    "from" => array(
                        "name" => Mage::app()->getStore()->getName(),
                        "cep" => preg_replace("/[^0-9]/", "",Mage::getStoreConfig('shipping/origin/postcode',Mage::app()->getStore()->getStoreId())),
                        "number" => (int)$streetStore[1],
                        "street" => $streetStore[0],
                        "city" => $this->getShippingConfig('city'),
                        "state" => $this->getShippingConfig('region_id')
                    ),
                    "to" => array(
                        //"number" => "2500",
                        "name" => $order->getCustomerName(),
                        "street" => $customerStreet,
                        "city" => $order->getShippingAddress()->getCity(),
                        "state" => $order->getShippingAddress()->getRegion(),
                        "cep" => preg_replace("/[^0-9]/", "",$order->getShippingAddress()->getPostcode())
                    )
                );


                try{
					Mage::getSingleton('fretala/api_fretala')->insertFrete($frete);
				}catch(Exception $e){
					switch (get_class($e)) {
						case 'ValidationException':
						Mage::getModel('adminnotification/inbox')->add(3, 'Freta.lá - Erro de validação na criação do frete', 'Retorno: <b>'.$e->getMessage().'</b><br>','', true);
						break;

						case 'BadRequestException':
						Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro na criação do frete', '<b>Erro de conexão:</b> - '.$e->getMessage(),'', true);

						break;

						case 'InternalErrorException':
						Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro na criação do frete', 'Retorno: <b>'.$e->getMessage().'</b>', '',true);
						break;

						case 'NotFoundException':
						Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá -Erro na criação do frete', 'Retorno: <b>'.$e->getMessage().'</b>');

						default:
						Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro na criação do frete', 'Retorno: <b>'.$e->getMessage().'</b>');
						break;
					}
					Mage::logException($e);
					Mage::log('Criação do frete - '.$e->getMessage());
					Mage::log($frete);
				}
				
			}
		}
	}
}