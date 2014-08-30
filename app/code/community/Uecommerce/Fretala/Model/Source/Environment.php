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
 class Uecommerce_Fretala_Model_Source_Environment extends Mage_Core_Model_Config_Data
 {
 	public function toOptionArray(){
 		return array(
 			array('label'=>Mage::helper('fretala')->__('Production'),'value'=>'production'),
 			array('label'=>Mage::helper('fretala')->__('Sandbox'),'value'=>'sandbox')
 		);
 	}
 }