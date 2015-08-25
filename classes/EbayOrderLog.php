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

class EbayOrderLog extends ObjectModel
{
	public $id_ebay_profile;
	public $id_ebay_order;
	public $id_orders;
	public $type;
	public $success;
	public $data;

	public $date_add;
	public $date_update;	

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	// for Prestashop 1.4
	protected $tables;
	protected $fieldsRequired;
	protected $fieldsSize;
	protected $fieldsValidate;
	protected $table = 'ebay_order_log';
	protected $identifier = 'id_ebay_order_log';    
	
	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_ebay_order_log'] = (int)($this->id);

		$fields['id_ebay_profile'] = (int)($this->id_ebay_profile);
		$fields['id_ebay_order'] = (int)($this->id_ebay_order);
		$fields['id_orders'] = pSQL($this->id_orders);
		$fields['type'] = pSQL($this->type);
		$fields['success'] = (int)($this->success);
		$fields['data'] = pSQL($this->data);
		
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_update'] = pSQL($this->date_update);

		return $fields;
	}        
	
	public function __construct($id = null, $id_lang = null, $id_shop = null) {
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			self::$definition = array(
				'table' => 'ebay_order_log',
				'primary' => 'id_ebay_order_log',
				'fields' => array(
					'id_ebay_profile' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
					'id_ebay_order' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
					'id_orders' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'success' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
					'data' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
					'date_update' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
				),
			);
		else 
		{
			$tables = array ('ebay_order_log');
			$fieldsRequired = array('id_ebay_profile', 'type', 'success', 'data_add');
			$fieldsValidate = array();
		}
		
		$this->date_add = date('Y-m-d H:i:s');
		
		return parent::__construct($id, $id_lang, $id_shop);     
	}
	
	public static function get($offset, $limit)
	{
		return Db::getInstance()->executeS('SELECT * 
			FROM `'._DB_PREFIX_.'ebay_order_log`
			ORDER BY `id_ebay_order_log` DESC        
			LIMIT '.(int)$offset. ', '.(int)$limit);
	}
	
	public static function count()
	{
		return Db::getInstance()->getValue('SELECT count(*) 
			FROM `'._DB_PREFIX_.'ebay_order_log`');
	}    
	
	public static function cleanOlderThan($nb_days)
	{
		$date = date('Y-m-d\TH:i:s', strtotime('-1 day'));
		
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_order_log`
			WHERE `date_add` < \''.pSQL($date).'\'');
	}
}