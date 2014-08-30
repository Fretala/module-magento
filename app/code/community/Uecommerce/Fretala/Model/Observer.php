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
			$postcode = $this->getShippingConfig('postcode');
			if($this->getShippingConfig('region_id') == ''
				|| $postcode == ''
				|| !$this->validCep($postcode)
				|| $this->getShippingConfig('city') == ''
				|| $this->getShippingConfig('street_line1') == '')
			{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('fretala')->__('There is something wrong with the address settings from the store, %s click here check %s','<a href="'.$controller->getUrl('adminhtml/system_config/edit/section/shipping').'">','</a>'));
			}
		}
	}

	public function getShippingConfig($config){
		return Mage::getStoreConfig($this->shippingConfigPath.$config,$this->storeId);
	}

	public function validCep($cep) {
		$cep = trim(str_replace('-','',$cep));
		$avaliaCep = preg_match('/^[0-9]{5,5}([- ]?[0-9]{3,3})?$/', $cep);
		if(!$avaliaCep){
			Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('fretala')->__('The format of the postcode should be xxxxx-xxx or xxxxxxxx'));
		}
    	return $avaliaCep;
	}
}