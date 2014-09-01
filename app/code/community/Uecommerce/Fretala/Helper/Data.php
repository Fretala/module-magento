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
class Uecommerce_Fretala_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function validPostcode($postcode){
		$cep = trim(str_replace('-','',$postcode));
		$avaliaCep = preg_match('/^[0-9]{5,5}([- ]?[0-9]{3,3})?$/', $postcode);
		return $avaliaCep;
	}

	public function validStreetNumber($street){
		if(strpos($street,',') !== false){
			$street = explode(',',$street);
			if(count($street) ==2){
				if(is_numeric($street[1])){
					
					return array($street[0],str_replace(' ','',$street[1]));
				}else{
					return false;
				}
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}
}