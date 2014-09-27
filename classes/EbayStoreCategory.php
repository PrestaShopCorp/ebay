<?php

/*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayStoreCategory extends ObjectModel
{
	public $ebay_category_id;
	public $name;
    public $order;
    public $ebay_parent_category_id;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
    
    // for Prestashop 1.4
	protected $tables;
	protected $fieldsRequired;
	protected $fieldsSize;
	protected $fieldsValidate;
	protected $table = 'ebay_store_category';
	protected $identifier = 'id_ebay_store_category';    
    
	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_ebay_store_category'] = (int)($this->id);

		$fields['ebay_category_id'] = (int)$this->ebay_category_id;
		$fields['name'] = pSQL($this->name);
		$fields['order'] = (int)$this->order;
		$fields['ebay_parent_category_id'] = (int)$this->ebay_parent_category_id;

		return $fields;
	}        
    
    public function __construct($id = null, $id_lang = null, $id_shop = null) {
        if (version_compare(_PS_VERSION_, '1.5', '>'))
            self::$definition = array(
           		'table' => 'ebay_store_category',
           		'primary' => 'id_ebay_store_category',
           		'fields' => array(
                    'ebay_category_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
                    'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
                    'order' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
                    'ebay_parent_category_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt')
           		),
           	);
        else 
        {
        	$tables = array ('ebay_store_category');
        	$fieldsRequired = array('ebay_category_id', 'name', 'order');
        	$fieldsValidate = array();
        }
        return parent::__construct($id, $id_lang, $id_shop);     
    }
    
    /**
     * return true if there are store categories in the ebay_store_category table
     * there is always one at list ('Other') so we need to test > 1 to make sure
     *
     **/
    public static function hasStoreCategories()
    {
        $nb_categories = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'ebay_store_category`
            WHERE `order` = 0');
        return ($nb_categories > 1);
        
    }
	
	public static function updateStoreCategoryTable($store_categories)
	{
        foreach ($store_categories as $custom_cat)
            EbayStoreCategory::_writeStoreCategory($custom_cat);

		Configuration::updateValue('EBAY_STORE_CATEGORY_UPDATE', 1, false, 0, 0);
	}  
    
    private static function _writeStoreCategory($category_data, $ebay_parent_category_id = null)
    {
        $store_category = new EbayStoreCategory();
        $store_category->ebay_category_id = (int)$category_data->CategoryID;
        $store_category->name = (string)$category_data->Name;
        $store_category->order = (int)$category_data->Order;
        
        if ($ebay_parent_category_id)
            $store_category->ebay_parent_category_id = $ebay_parent_category_id;
        
        $store_category->save();
        
        if (isset($category_data->ChildCategory))
            foreach ($category_data->ChildCategory as $child_category)
                EbayStoreCategory::_writeStoreCategory($child_category, $store_category->ebay_category_id);
            
    }
    
    public static function getCategoriesWithConfiguration($id_ebay_profile)
    {
        $query = 'SELECT esc.`ebay_category_id`, esc.`name`, escc.`id_category`, esc.`ebay_parent_category_id`
            FROM `'._DB_PREFIX_.'ebay_store_category` esc
            LEFT JOIN `'._DB_PREFIX_.'ebay_store_category_configuration` escc
            ON esc.`ebay_category_id` = escc.`ebay_category_id`
            AND escc.`id_ebay_profile` = '.(int)$id_ebay_profile.'
            ORDER BY `ebay_parent_category_id` ASC, `order` ASC';
        return Db::getInstance()->executeS($query);        
    }
    
}