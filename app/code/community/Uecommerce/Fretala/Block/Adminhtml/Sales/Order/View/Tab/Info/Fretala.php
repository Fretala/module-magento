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
class Uecommerce_Fretala_Block_Adminhtml_Sales_Order_View_Tab_Info_Fretala extends Mage_Adminhtml_Block_Template
{
	protected $shippingConfigPath;

	public function fretalaShippingExists(){

		$orderId = Mage::registry('current_order')->getIncrementId();

		try{
			$fretala = Mage::getSingleton('fretala/api_fretala')->getFrete($orderId);

		}catch(Exception $e){
			
			switch (get_class($e)) {
				case 'ValidationException':
				Mage::getModel('adminnotification/inbox')->add(3, 'Freta.lá - Erro de validação da requisição do frete', 'Retorno: <b>'.$e->getMessage().'</b><br>','', true);
				$fretala = false;
				break;

				case 'BadRequestException':
				Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro na requisição do frete', '<b>Erro de conexão:</b> - '.$e->getMessage(),'', true);
				$fretala = 'error';
				break;

				case 'InternalErrorException':
				Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro na requisição do frete', 'Retorno: <b>'.$e->getMessage().'</b>', '',true);
				$fretala = 'error';
				break;

				case 'NotFoundException':
				//Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá -Erro na requisição do frete', 'Retorno: <b>'.$e->getMessage().'</b>');
				$fretala = false;
				break;

				default:
				Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro na requisição do frete', 'Retorno: <b>'.$e->getMessage().'</b>');
				break;
			}
			if(!$fretala || $fretala == 'error'){
				Mage::logException($e);
				Mage::log('Criação do frete - '.$e->getMessage());
			}
			

		}

		return $fretala;
	}

	public function getParamsCreate(){
		$order = Mage::registry('current_order');
		$customerStreet = '';
		foreach($order->getShippingAddress()->getStreet() as $street){
			$customerStreet .= $street.' ';
		}
		$streetStore = Mage::helper('fretala')->validStreetNumber($this->getShippingConfig('street_line1'));
		$frete = array(
			"id" => $order->getIncrementId(),
			"productValue" => $order->getSubtotal()*100,
			"fromNumber" => (int)$streetStore[1],
			"fromStreet" => $streetStore[0],
			"fromCity" => $this->getShippingConfig('city'), 
			"fromState" => $this->getShippingConfig('region_id'),
			"toStreet" => $customerStreet,
			"toCity" => $order->getShippingAddress()->getCity(), 
			"toState" => $order->getShippingAddress()->getRegion()
			);
		return json_encode($frete);
	}

	public function getShippingConfig($config){
		if(empty($this->shippingConfigPath)){
			$this->shippingConfigPath = 'shipping/origin/';
		}
		return Mage::getStoreConfig($this->shippingConfigPath.$config,$this->storeId);
	}

	public function getOrder(){
		return Mage::registry('current_order');
	}
	
}