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
class Uecommerce_Fretala_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup {
    /*
     * @return array
     */

    public function getDefaultEntities() {
        return array(
            'catalog_product' => array(
                'entity_model' => 'catalog/product',
                'attribute_model' => 'catalog/resource_eav_attribute',
                'additional_attribute_table' => 'catalog/eav_attribute',
                'entity_attribute_collection' => 'catalog/product_attribute_collection',
                'table' => 'catalog/product',
                'attributes' => array(
                    'fretala_vip_delivery' => array(
                        'group' => 'General',
                        'label' => 'Entrega VIP Freta.lá',
                        'type' => 'int',
                        'input' => 'select',
                        'default' => '1',
                        'source' => 'eav/entity_attribute_source_boolean',
                        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible' => true,
                        'required' => true,
                        'user_defined' => false,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'visible_in_advanced_search' => false,
                        'unique' => false
                    ),
                )
            )
        );
    }

}
