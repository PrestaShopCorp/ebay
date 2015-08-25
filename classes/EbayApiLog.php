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

class EbayApiLog extends ObjectModel
{
	public $id_ebay_profile;    
	public $type;
	public $context;
	public $data_sent;
	public $response;
	
	public $id_product;
	public $id_order;

	public $date_add;    
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	// for Prestashop 1.4
	protected $tables;
	protected $fieldsRequired;
	protected $fieldsSize;
	protected $fieldsValidate;
	protected $table = 'ebay_api_log';
	protected $identifier = 'id_ebay_api_log';    
	
	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_ebay_api_log'] = (int)($this->id);

		$fields['id_ebay_profile'] = (int)($this->id_ebay_profile);
		$fields['type'] = pSQL($this->type);
		$fields['context'] = pSQL($this->context);
		$fields['data_sent'] = pSQL($this->data_sent);
		$fields['response'] = pSQL($this->response);
		$fields['id_product'] = (int)$this->id_product;
		$fields['id_order'] = (int)$this->id_order;
		$fields['date_add'] = pSQL($this->date_add);

		return $fields;
	}        
	
	public function __construct($id = null, $id_lang = null, $id_shop = null) {
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			self::$definition = array(
				'table' => 'ebay_api_log',
				'primary' => 'id_ebay_log',
				'fields' => array(
					'id_ebay_profile' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
					'type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'context' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'data_sent' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'response' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
					'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
					'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
					'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
				),
			);
		else 
		{
			$tables = array ('ebay_api_log');
			$fieldsRequired = array('date_add', 'type', 'context', 'data_sent', 'reponse');
			$fieldsValidate = array();
		}
		
		$this->date_add = date('Y-m-d H:i:s');
		
		return parent::__construct($id, $id_lang, $id_shop);     
	}
	
	public static function get($offset, $limit)
	{
		return Db::getInstance()->executeS('SELECT * 
			FROM `'._DB_PREFIX_.'ebay_api_log`
			ORDER BY `id_ebay_api_log` DESC
			LIMIT '.(int)$offset. ', '.(int)$limit);
	}
	
	public static function count()
	{
		return Db::getInstance()->getValue('SELECT count(*) 
			FROM `'._DB_PREFIX_.'ebay_api_log`');
	}
	
	public static function cleanOlderThan($nb_days)
	{
		$date = date('Y-m-d\TH:i:s', strtotime('-1 day'));
		
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_api_log`
			WHERE `date_add` < \''.pSQL($date).'\'');
	}
	
}