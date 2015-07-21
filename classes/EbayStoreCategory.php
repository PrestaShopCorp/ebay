<?php
/**
 * 2007-2015 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayStoreCategory extends ObjectModel
{
	public $ebay_category_id;
	public $id_ebay_profile;
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

		$fields['id_ebay_profile'] = (int)$this->id_ebay_profile;
		$fields['ebay_category_id'] = pSQL($this->ebay_category_id);
		$fields['name'] = pSQL($this->name);
		$fields['order'] = (int)$this->order;
		$fields['ebay_parent_category_id'] = pSQL($this->ebay_parent_category_id);

		return $fields;
	}        
	
	public function __construct($id = null, $id_lang = null, $id_shop = null) {
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			self::$definition = array(
				'table' => 'ebay_store_category',
				'primary' => 'id_ebay_store_category',
				'fields' => array(
					'id_ebay_profile' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
					'ebay_category_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'order' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
					'ebay_parent_category_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString')
				),
			);
		else 
		{
			$tables = array ('ebay_store_category');
			$fieldsRequired = array('id_ebay_profile', 'ebay_category_id', 'name', 'order');
			$fieldsValidate = array();
		}
		return parent::__construct($id, $id_lang, $id_shop);     
	}
	
	/**
	 * return compatible and not capatible categories, i.e. having children or being a child
	 *
	 **/
	public static function getStoreCategories($id_ebay_profile)
	{
		$store_categories = Db::getInstance()->executeS('SELECT * 
			FROM `'._DB_PREFIX_.'ebay_store_category` 
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile);
		
		$compatible_store_categories = self::_filterCategories($store_categories);



		// all categories are compatible
		if (count($store_categories) == count($compatible_store_categories))
			$not_compatible_store_categories = array();
		else {
			$not_compatible_store_categories = array();
			foreach ($store_categories as $cat) {
				$is_not_compatible = true;
				foreach ($compatible_store_categories as $cat2) {
					if ($cat['ebay_category_id'] == $cat2['ebay_category_id']) {
						$is_not_compatible = false;
						break;
					}
				}
				if ($is_not_compatible)
					$not_compatible_store_categories[] = $cat;
			}            
		}
		
		return array(
			'compatible'     => $compatible_store_categories,
			'not_compatible' => $not_compatible_store_categories,
		);
		
	}
	
	public static function updateStoreCategoryTable($store_categories, $ebay_profile)
	{
		// clean table before inserts
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_store_category`
			WHERE `id_ebay_profile` = '.(int)$ebay_profile->id);
		
		if($store_categories)
			foreach ($store_categories as $custom_cat)
				EbayStoreCategory::_writeStoreCategory($custom_cat, $ebay_profile->id);
		
		// make sure that all referenced categories still exists
		EbayStoreCategoryConfiguration::checkExistingCategories($ebay_profile->id);

	}  
	
	private static function _writeStoreCategory($category_data, $id_ebay_profile, $ebay_parent_category_id = null)
	{
		$store_category = new EbayStoreCategory();
		$store_category->id_ebay_profile = (int)$id_ebay_profile;
		$store_category->ebay_category_id = pSQL($category_data->CategoryID);
		$store_category->name = (string)$category_data->Name;
		$store_category->order = (int)$category_data->Order;

		if ($ebay_parent_category_id)
			$store_category->ebay_parent_category_id = $ebay_parent_category_id;
		else
			$store_category->ebay_parent_category_id = 0;

		$store_category->save();
		
		if (isset($category_data->ChildCategory))
			foreach ($category_data->ChildCategory as $child_category)
				EbayStoreCategory::_writeStoreCategory($child_category, $id_ebay_profile, $store_category->ebay_category_id);
			
	}
	
	public static function getCategoriesWithConfiguration($id_ebay_profile)
	{
		$categories = Db::getInstance()->executeS('SELECT esc.`ebay_category_id`, esc.`name`, escc.`id_category`, esc.`ebay_parent_category_id`
			FROM `'._DB_PREFIX_.'ebay_store_category` esc
			LEFT JOIN `'._DB_PREFIX_.'ebay_store_category_configuration` escc
			ON esc.`ebay_category_id` = escc.`ebay_category_id`
			AND esc.`id_ebay_profile` = escc.`id_ebay_profile`
			WHERE esc.`id_ebay_profile` = '.(int)$id_ebay_profile.'
			ORDER BY `ebay_parent_category_id` ASC, `order` ASC');
		
		$categories = self::_filterCategories($categories);
			
		$final_categories = array();
		foreach ($categories as $category) {
			$ebay_category_id = $category['ebay_category_id'];
			$id_category = $category['id_category'];
			unset($category['id_category']);
			
			if (!isset($final_categories['c_'.$ebay_category_id])) {
				
				$final_categories['c_'.$ebay_category_id] = $category;
				$final_categories['c_'.$ebay_category_id]['id_categories'] = array($id_category);

			} else {
				
				$final_categories['c_'.$ebay_category_id]['id_categories'][] = $id_category;
				
			}
		}
		
		return $final_categories;
	}
	
	/*
	 *
	 * don't keep categories with subcategories
	 *
	 **/
	private static function _filterCategories($store_categories)
	{
		$blacklist_ids = array();        
		foreach ($store_categories as $cat) {
		
			if ($cat['ebay_parent_category_id']) {
				$blacklist_ids[] = $cat['ebay_parent_category_id'];
			}
		
		}
	
		$final_categories = array();
		foreach ($store_categories as $cat) {
			if (!in_array($cat['ebay_category_id'], $blacklist_ids))
				$final_categories[] = $cat;
		}
		
		return $final_categories;

	}
	

	
}