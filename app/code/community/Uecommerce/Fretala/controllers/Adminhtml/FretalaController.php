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
class Uecommerce_Fretala_Adminhtml_FretalaController extends Mage_Adminhtml_Controller_Action
{
	public function createshippingAction(){
		$request = $this->getRequest();
		if($request->isPost()){
			$frete = $request->getPost();
			unset($frete['form_key']);
			
			try{
				$frete = array(
					'id' => $frete['id'],
					'productValue' => $frete['productValue'],
					'from' => array(
						'number' => $frete['fromNumber'],
						'street' => $frete['fromStreet'],
						'city' => $frete['fromCity'],
						'state' => $frete['fromState']
					),
					'to' => array(
						'street' => $frete['toStreet'],
						'city' => $frete['toCity'],
						'state' => $frete['toState']
					)
				);
				Mage::getSingleton('fretala/api_fretala')->insertFrete($frete);
				$return = $this->__('ok');
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
					break;

					default:
					Mage::getModel('adminnotification/inbox')->add(1, 'Freta.lá - Erro na criação do frete', 'Retorno: <b>'.$e->getMessage().'</b>');
					break;
				}
				
				Mage::logException($e);
				Mage::log('Criação do frete - '.$e->getMessage());
				$return = 0;
			}
		}
		$this->getResponse()->setHeader('Content-type', 'application/json');
 		$this->getResponse()->setBody($return);
	}
}