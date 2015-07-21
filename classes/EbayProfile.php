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

class EbayProfile extends ObjectModel
{
	public $id_lang;
	public $id_shop;
	public $ebay_user_identifier;
	public $ebay_site_id;
	public $id_ebay_returns_policy_configuration;
	
	private $returns_policy;
	
	private $configurations;
	
	private $token;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition;
	
	/**
	 * For PS 1.4
	 */
	protected $tables;
	protected $fieldsRequired = array();
	protected $fieldsSize = array();
	protected $fieldsValidate = array();
	protected $table = 'ebay_profile';
	protected $identifier = 'id_ebay_profile';    
	
	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_ebay_profile'] = (int)($this->id);

		$fields['id_lang'] = (int)($this->id_lang);
		$fields['id_shop'] = (int)($this->id_shop);        
		$fields['ebay_user_identifier'] = pSQL($this->ebay_user_identifier);
		$fields['ebay_site_id'] = pSQL($this->ebay_site_id);
		$fields['id_ebay_returns_policy_configuration'] = (int)($this->id_ebay_returns_policy_configuration);        

		return $fields;
	}    


	
	public function __construct($id = null, $id_lang = null, $id_shop = null) 
	{
		if (version_compare(_PS_VERSION_, '1.5', '>'))        
			self::$definition = array(
					'table' => 'ebay_profile',
					'primary' => 'id_ebay_profile',
					'fields' => array(
						'id_lang' =>		array('type' => self::TYPE_INT, 'validate' => 'isInt'),
						'id_shop' =>		array('type' => self::TYPE_INT, 'validate' => 'isInt'),
						'ebay_user_identifier' => array('type' => self::TYPE_STRING, 'size' => 255),
						'ebay_site_id' => array('type' => self::TYPE_STRING, 'size' => 32),
						'id_ebay_returns_policy_configuration' => array('type' => self::TYPE_INT, 'validate' => 'isInt')
					),
				); 
		else 
		{
			$tables = array ('ebay_profile');
			$fieldsRequired = array('id_lang', 'id_shop', 'ebay_user_identifier', 'ebay_site_id', 'id_ebay_returns_policy_configuration');
			$fieldsSize = array('ebay_user_identifier' => 32, 'ebay_site_id' => 32);
			$fieldsValidate = array(
				'id_lang' => 'isUnsignedInt',
				'id_shop' => 'isUnsignedInt',
				'id_ebay_returns_policy_configuration' => 'isUnsignedInt'
			);
		}    
		return parent::__construct($id, $id_lang, $id_shop);
	}
	
	public function getReturnsPolicyConfiguration()
	{
		
		if ($this->id_ebay_returns_policy_configuration)
			$returns_policy_configuration = new EbayReturnsPolicyConfiguration($this->id_ebay_returns_policy_configuration);
		else
			$returns_policy_configuration = new EbayReturnsPolicyConfiguration();
		return $returns_policy_configuration;
	}
	
	public function loadStoreCategories()
	{
		
		if($this->getConfiguration('EBAY_PROFILE_STORE_CAT') || !$this->getToken() || $this->getToken() == null)
			return;

		$ebay_store_categories = EbayStoreCategory::getStoreCategories($this->id);
		if(count($ebay_store_categories['compatible']) == 0)
		{
			$ebay = new EbayRequest();
			EbayStoreCategory::updateStoreCategoryTable($ebay->getStoreCategories(), $this);
		}
		$this->setConfiguration('EBAY_PROFILE_STORE_CAT', 1);
	}

	public function setReturnsPolicyConfiguration($within, $who_pays, $description, $accepted_option)
	{
		
		$returns_policy_configuration = $this->getReturnsPolicyConfiguration();
		if ($returns_policy_configuration->ebay_returns_within != $within)
			$returns_policy_configuration->ebay_returns_within = $within;
		if ($returns_policy_configuration->ebay_returns_who_pays != $who_pays)
			$returns_policy_configuration->ebay_returns_who_pays = $who_pays;
		if ($returns_policy_configuration->ebay_returns_description != $description)
			$returns_policy_configuration->ebay_returns_description = $description;
		if ($returns_policy_configuration->ebay_returns_accepted_option != $accepted_option)
			$returns_policy_configuration->ebay_returns_accepted_option = $accepted_option;
			$res = $returns_policy_configuration->save();		

		return $res;
	}	

	private function _loadConfiguration()
	{
		$sql = 'SELECT ec.`name`, ec.`value`
				FROM `'._DB_PREFIX_.'ebay_configuration` ec
				WHERE ec.`id_ebay_profile`= '.(int)$this->id;
		$configurations = Db::getInstance()->executeS($sql);

		foreach ($configurations as $configuration)
			$this->configurations[$configuration['name']] = $configuration['value'];
	}

	public function setConfiguration($name, $value, $html = false)
	{
		$data = array(
			'id_ebay_profile' => $this->id,
			'name' => pSQL($name),
			'value' => pSQL($value, $html)
		);	

		
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$res = Db::getInstance()->insert('ebay_configuration', $data, false, true, Db::REPLACE);
		else
		{
			if ($this->hasConfiguration(pSQL($name)))
				$res = Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_configuration', array('value' => pSQL($value, $html)), 'UPDATE', '`id_ebay_profile` = '.(int)$this->id. ' AND `name` = \''.pSQL($name).'\'');
			else
				$res = Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_configuration', $data, 'INSERT');
		}
		if ($res)
			$this->configurations[$name] = $value;
		return $res;
	}

	public function hasConfiguration($name)
	{
		if ($this->configurations === null)
			$this->_loadConfiguration();

		return isset($this->configurations[$name]);
	}

	public function getConfiguration($name)
	{
		if ($this->configurations === null)
			$this->_loadConfiguration();

		return isset($this->configurations[$name]) ? $this->configurations[$name] : null;
	}
	
	public function deleteConfigurationByName($name)
	{
		return Db::getInstance()->execute('
		DELETE FROM `'._DB_PREFIX_.'ebay_configuration`
		WHERE `id_ebay_profile` = '.(int)$this->id.'
		AND `name` = "'.pSQL($name).'"');		
	}
	
	/**
	  * Get several configuration values
	  *
	  * @param array $keys Keys wanted
	  * @return array Values
	  */
	public function getMultiple($keys)
	{
		if (!is_array($keys))
			throw new PrestaShopException('keys var is not an array');

		if ($this->configurations === null)
			$this->_loadConfiguration();

		$results = array();
		foreach ($keys as $key)
			$results[$key] = $this->getConfiguration($key);
		return $results;
	}
	
	public function getCarriers($id_lang, $active = false, $delete = false, $id_zone = false, $ids_group = null, $modules_filters = 1)
	{
		$carriers = Carrier::getCarriers($id_lang, $active, $delete, $id_zone, $ids_group, $modules_filters);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$sql = 'SELECT `id_carrier`
				FROM `'._DB_PREFIX_.'carrier_shop`
				WHERE `id_shop` = '.(int)$this->id_shop;
		else
			$sql = 'SELECT `id_carrier`
				FROM `'._DB_PREFIX_.'carrier`';
		$res = Db::getInstance()->executeS($sql);
		$id_carriers = array();
		foreach($res as $row)
			$id_carriers[] = $row['id_carrier'];
		
		$final_carriers = array();
		foreach($carriers as $carrier)
			if (in_array($carrier['id_carrier'], $id_carriers))
					$final_carriers[] = $carrier;
		
		return $final_carriers;
	}
	
	public function setDefaultConfig($template_content) 
	{
		$this->setConfiguration('EBAY_PRODUCT_TEMPLATE', ''); // fix to work around the PrestaShop bug when saving html for a configuration key that doesn't exist yet
		$this->setConfiguration('EBAY_PRODUCT_TEMPLATE', $template_content, true);
		$this->setConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE', '{TITLE}');
		$this->setConfiguration('EBAY_ORDER_LAST_UPDATE', date('Y-m-d\TH:i:s.000\Z'));
		$this->setConfiguration('EBAY_DELIVERY_TIME', 2);
		$this->setConfiguration('EBAY_ACTIVATE_LOGS', '0');
		$this->setConfiguration('EBAY_ACTIVATE_MAILS', '0');
		$this->setConfiguration('EBAY_LISTING_DURATION', 'GTC');
		$this->setConfiguration('EBAY_AUTOMATICALLY_RELIST', 'on');
		$this->setConfiguration('EBAY_LAST_RELIST', date('Y-m-d'));        
		$this->setConfiguration('EBAY_SEND_TRACKING_CODE', 1);        
	}
	
	public function setPicturesSettings() 
	{
		// Default
		if ($medium = ImageType::getByNameNType('thickbox', 'products')) 
			$sizeMedium = (int)$medium['id_image_type'];
		elseif ($medium = ImageType::getByNameNType('thickbox_default', 'products')) 
			$sizeMedium = (int)$medium['id_image_type'];
		else 
			$sizeMedium = 0;
		
		// Small
		if ($small = ImageType::getByNameNType('small', 'products')) 
			$sizeSmall = (int) $small['id_image_type'];
		elseif ($small = ImageType::getByNameNType('small_default', 'products')) 
			$sizeSmall = (int) $small['id_image_type'];
		else 
			$sizeSmall = 0;
		
		// Large
		if ($large = ImageType::getByNameNType('large', 'products')) 
			$sizeBig = (int) $large['id_image_type'];
		elseif ($large = ImageType::getByNameNType('large_default', 'products')) 
			$sizeBig = (int) $large['id_image_type'];
		else 
			$sizeBig = 0;

		$this->setConfiguration('EBAY_PICTURE_SIZE_DEFAULT', $sizeMedium);
		$this->setConfiguration('EBAY_PICTURE_SIZE_SMALL', $sizeSmall);
		$this->setConfiguration('EBAY_PICTURE_SIZE_BIG', $sizeBig);
		$this->setConfiguration('EBAY_PICTURE_PER_LISTING', 0);
	}
	
	/*
	 * returns true if all settings are properly configured in the settings tab
	 * returns false otherwise
	 *
	 **/
	public function isAllSettingsConfigured()
	{
		$tabs = array(
			'param' => EbayValidatorTab::getParametersTabConfiguration($this->id),
			'categories' => EbayValidatorTab::getCategoryTabConfiguration($this->id),
			'items_specifics' => EbayValidatorTab::getitemSpecificsTabConfiguration($this->id),
			'shipping' => EbayValidatorTab::getShippingTabConfiguration($this->id),
			'template' => EbayValidatorTab::getTemplateTabConfiguration($this->id)
		);
		
		$is_all_set = true;
		foreach ($tabs as $key => $tab) 
		{
			if (isset($tab['indicator']) && ($tab['indicator'] !== 'success')) 
			{
				$is_all_set = false;
				break;
			}
		}
		
		return $is_all_set;

	}
	
	/**
	  * Get token from the ebay_user_identifier
	  *
	  * @return token, null if no token
	  */
	public function getToken() 
	{
		if ($this->token === null) 
		{
			$sql = 'SELECT `token`
				FROM `'._DB_PREFIX_.'ebay_user_identifier_token` euit
				WHERE euit.`ebay_user_identifier` = \''.pSQL($this->ebay_user_identifier).'\'';
			$this->token = Db::getInstance()->getValue($sql);
		}
		
		return $this->token;
	}
	
	/**
	  * Set token for this ebay_user_identifier
	  *
	  * @return null
	  */
	public function setToken($token)
	{
		$sql = 'REPLACE INTO `'._DB_PREFIX_.'ebay_user_identifier_token` (
			`ebay_user_identifier`, 
			`token`
			)
			VALUES(
			\''.pSQL($this->ebay_user_identifier).'\',
			\''.pSQL($token).'\')';
		DB::getInstance()->Execute($sql);        
	}
	
	/**
	  * Is the profile configured
	  *
	  * @return boolean true if configured, false otherwise
	  */
	public function isConfigured()
	{	
		if ($this->configurations === null)
			$this->_loadConfiguration();
		return (count($this->configurations) > 0);
	}
	
	public static function getOneByIdShop($id_shop)
	{
		// check if one profile exists otherwise creates it
		$sql = 'SELECT `id_ebay_profile`
			FROM `'._DB_PREFIX_.'ebay_profile` ep
			WHERE ep.`id_shop` = '.(int)$id_shop;
		try 
		{ // will fail if the table doesn't exist (when doing a PS module autoupgrade for example)
			if ($profile_data = Db::getInstance()->getRow($sql)) // one row exists
				return new EbayProfile($profile_data['id_ebay_profile']);
			else 
				return false;
		} 
		catch (Exception $e) 
		{
			return false;
		}
	}
	
	public static function _getIdShop($default_if_null = true) {
		$id_shop = version_compare(_PS_VERSION_, '1.5', '>') ? Shop::getContextShopID() : Shop::getCurrentShop();
		
		if (!$id_shop && $default_if_null)
			if(Configuration::get('PS_SHOP_DEFAULT'))
				$id_shop = Configuration::get('PS_SHOP_DEFAULT');
			else
				$id_shop = 1;
			
		return $id_shop;
	}
	
	/**
	  * Is the shop has changed, returns the first profile of the shop, returns the current profile otherwise
	  *
	  * @return EbayProfile
	  */    
	public static function getCurrent($check_current_shop = true)
	{
		$id_shop = (int)EbayProfile::_getIdShop(false);
		
		$current_profile = Configuration::get('EBAY_CURRENT_PROFILE');
		if ($current_profile) 
		{
			$data = explode('_',$current_profile);
			if ($check_current_shop && $id_shop) 
			{
				$current_profile_id_shop = (int)$data[1];
				if (($current_profile_id_shop == $id_shop) || ($current_profile_id_shop == 0)) 
					return new EbayProfile((int)$data[0]);
			} 
			else
				return new EbayProfile((int)$data[0]);                
		}

		// if shop has changed we switch to the first shop profile
		$ebay_profile = self::getOneByIdShop($id_shop);
		
		if (!$ebay_profile)
			return null;
		
		Configuration::updateValue('EBAY_CURRENT_PROFILE', $ebay_profile->id.'_'.$id_shop, false, 0, 0);
		
		return $ebay_profile;
	}
	
	public static function setProfile($id_ebay_profile) 
	{
		$id_shop = (int)EbayProfile::_getIdShop(false);
		
		// check that this profile is for the current shop
		$shop_profiles = EbayProfile::getProfilesByIdShop($id_shop);
		if ($id_shop) 
		{
			$is_shop_profile = false;
			foreach ($shop_profiles as $profile) 
			{
				if ($profile['id_ebay_profile'] == $id_ebay_profile) 
				{
					$is_shop_profile = true;
					break;
				}
			}            
			if (!$is_shop_profile)
				return false;            
		}
		
		Configuration::updateValue('EBAY_CURRENT_PROFILE', $id_ebay_profile.'_'.$id_shop, false, 0, 0);
		
		return true;
	}
	
	public static function getProfilesByIdShop($id_shop = 0)
	{
		$sql = 'SELECT ep.`id_ebay_profile`, ep.`ebay_user_identifier`, ep.`ebay_site_id`, ep.`id_lang`, l.`name` AS `language_name`
				'.(version_compare(_PS_VERSION_, '1.5', '>') ? ',s.`name`' : '').'
				FROM `'._DB_PREFIX_.'ebay_profile` ep
				LEFT JOIN `'._DB_PREFIX_.'lang` l ON (ep.`id_lang` = l.`id_lang`)
				'.(version_compare(_PS_VERSION_, '1.5', '>') ? 'LEFT JOIN `'._DB_PREFIX_.'shop` s ON (ep.`id_shop` = s.`id_shop`)' : '').'
				'.($id_shop != 0 ? ' WHERE ep.`id_shop` = '.(int)$id_shop : '');
		return Db::getInstance()->executeS($sql);
	}
	
	public static function getEbayUserIdentifiers() 
	{
		$sql = 'SELECT DISTINCT(ep.`ebay_user_identifier`) AS `identifier`, euit.`token`
			FROM `'._DB_PREFIX_.'ebay_profile` ep
			LEFT JOIN `'._DB_PREFIX_.'ebay_user_identifier_token` euit
			ON ep.`ebay_user_identifier` = euit.`ebay_user_identifier`';
		$res = Db::getInstance()->executeS($sql);
		$identifiers_data = array();
		foreach ($res as $row)
			$identifiers_data[] = array(
				'identifier' => $row['identifier'],
				'token'      => $row['token']
			);
		return $identifiers_data;
	}
	
	public static function getByLangShopSiteAndUsername($id_lang, $id_shop, $ebay_country, $ebay_user_identifier, $template_content) 
	{
		$ebay_country_spec = EbayCountrySpec::getInstanceByKey($ebay_country);
		$ebay_site_id = $ebay_country_spec->getSiteID();
		
		$sql = 'SELECT `id_ebay_profile` 
			FROM `'._DB_PREFIX_.'ebay_profile` ep
			WHERE ep.`id_lang` = '.(int)$id_lang.'
			AND ep.`id_shop` = '.(int)$id_shop.'
			AND ep.`ebay_site_id` = '.(int)$ebay_site_id.'
			AND ep.`ebay_user_identifier` = \''.pSQL($ebay_user_identifier).'\'';

		if ($id_profile = Db::getInstance()->getValue($sql))
			return new EbayProfile($id_profile);
		
		// otherwise create the eBay profile
		$ebay_profile = new EbayProfile();
		$ebay_profile->id_lang = $id_lang;
		$ebay_profile->id_shop = $id_shop;
		$ebay_profile->ebay_site_id = $ebay_site_id;
		$ebay_profile->ebay_user_identifier = $ebay_user_identifier;

		$returns_policy_configuration = new EbayReturnsPolicyConfiguration();
		$returns_policy_configuration->save();		
		$ebay_profile->id_ebay_returns_policy_configuration = $returns_policy_configuration->id;

		$ebay_profile->save();
		$ebay_profile->setConfiguration('EBAY_COUNTRY_DEFAULT', $ebay_country);
		$ebay_profile->setPicturesSettings();
		$ebay_profile->setDefaultConfig($template_content);
		
		return $ebay_profile;
	}

	public static function deleteById($id_ebay_profile) 
	{
		$tables = array(
			'ebay_product',
			'ebay_category_condition_configuration',
			'ebay_category_condition',
			'ebay_category_configuration',
			'ebay_configuration',
			'ebay_product_modified',
			'ebay_shipping',
			'ebay_shipping_international_zone',
			'ebay_shipping_zone_excluded',
			'ebay_profile'
		);
		foreach ($tables as $table) 
		{
			Db::getInstance()->delete(_DB_PREFIX_.$table, '`id_ebay_profile` = '.(int)$id_ebay_profile);
		}
		
		// if the profile deleted is the current one, we reset the EBAY_CURRENT_PROFILE
		$current_profile = Configuration::get('EBAY_CURRENT_PROFILE');
		$data = explode('_', $current_profile);
		if ($data[0] == $id_ebay_profile)
			Configuration::deleteByName('EBAY_CURRENT_PROFILE');
		
		
		return true;
	}    
	
}