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

class EbayProduct
{
	public static function getIdProductRef($id_product, $ebay_identifier, $ebay_site_id, $id_attribute = null)
	{
		$query = 'SELECT `id_product_ref`
			FROM `'._DB_PREFIX_.'ebay_product` ep
			INNER JOIN `'._DB_PREFIX_.'ebay_profile` ep1
			ON ep.`id_ebay_profile` = ep1.`id_ebay_profile`
			AND ep1.`ebay_user_identifier` = \''.pSQL($ebay_identifier).'\'
			AND ep1.`ebay_site_id` = '.(int)$ebay_site_id.'
			WHERE ep.`id_product` = '.(int)$id_product;
		
		if ($id_attribute)
			$query .= ' AND ep.`id_attribute` = '.(int)$id_attribute;

		return Db::getInstance()->getValue($query);
	}

	public static function getPercentOfCatalog($ebay_profile)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$id_shop = $ebay_profile->id_shop;
			$sql = 'SELECT `id_product`
				FROM `'._DB_PREFIX_.'product_shop`
				WHERE `id_shop` = '.(int)$id_shop;
		}
		else
			$sql = 'SELECT `id_product` 
				FROM `'._DB_PREFIX_.'product`';    
		$results = Db::getInstance()->executeS($sql);
		$id_shop_products = array_map(array('EbayProduct', 'getProductsIdFromTable'), $results);

		$nb_shop_products = count($id_shop_products);
		
		if ($nb_shop_products)
		{
			$sql2 = 'SELECT count(*)
				FROM `'._DB_PREFIX_.'ebay_product`
				WHERE `id_product` IN ('.implode(',', $id_shop_products).')';
			$nb_synchronized_products = Db::getInstance()->getValue($sql2);            
			return number_format($nb_synchronized_products / $nb_shop_products * 100.0, 2);
		} else
			return '-';
	}

	public static function getProductsIdFromTable($a)
	{
		return (int)$a['id_product'];
	}

	public static function getNbProducts($id_ebay_profile)
	{
		return Db::getInstance()->getValue('SELECT count(*)
			FROM `'._DB_PREFIX_.'ebay_product`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile);
	}
	
	public static function getNbProductsByIdEbayProfiles($id_ebay_profiles = array())
	{
		$query = 'SELECT `id_ebay_profile`, count(*) AS `nb`
			FROM `'._DB_PREFIX_.'ebay_product`';
		if ($id_ebay_profiles)
			$query .= ' WHERE `id_ebay_profile` IN ('.implode(',', array_map('intval', $id_ebay_profiles)).')
				GROUP BY `id_ebay_profile`';
		
		$res = Db::getInstance()->executeS($query);
		
		$ret = array();
		foreach ($res as $row)
			$ret[$row['id_ebay_profile']] = $row['nb'];
		return $ret;
	}    

	public static function getProducts($not_update_for_days, $limit)
	{
		return Db::getInstance()->ExecuteS('SELECT ep.id_product_ref, ep.id_product
			FROM '._DB_PREFIX_.'ebay_product AS ep
			WHERE NOW() > DATE_ADD(ep.date_upd, INTERVAL '.(int)$not_update_for_days.' DAY)
			LIMIT '.(int)$limit);
	}
	
	public static function insert($data)
	{
			
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_product', $data, 'INSERT');
	}

	public static function updateByIdProductRef($id_product_ref, $datas)
	{
		$to_insert = array();
		if(is_array($datas) && count($datas))
			foreach($datas as $key => $data)
				$to_insert[pSQL($key)] = $data;

		//If eBay Product has been inserted then the configuration of eBay is OK
		Configuration::updateValue('EBAY_CONFIGURATION_OK', true);

		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_product', $to_insert, 'UPDATE', '`id_product_ref` = "'.pSQL($id_product_ref).'"');
	}

	public static function deleteByIdProductRef($id_product_ref)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_product`
			WHERE `id_product_ref` = \''.pSQL($id_product_ref).'\'');
	}

	public static function getProductsWithoutBlacklisted($id_lang, $id_ebay_profile, $no_blacklisted)
	{ 
		$sql = 'SELECT ep.`id_product`, ep.`id_attribute`, ep.`id_product_ref`,
			p.`id_category_default`, p.`reference`, p.`ean13`,
			pl.`name`, m.`name` as manufacturer_name
			FROM `'._DB_PREFIX_.'ebay_product` ep
			LEFT JOIN `'._DB_PREFIX_.'ebay_product_configuration` epc ON (epc.`id_product` = ep.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = ep.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = '.(int)$id_lang.')
			WHERE ep.`id_ebay_profile` = '.(int)$id_ebay_profile;
		if($no_blacklisted)
			$sql .= ' AND (epc.`blacklisted` = 0 OR epc.`blacklisted` IS NULL)';
		return Db::getInstance()->ExecuteS($sql);

	}

	public static function getEbayUrl($reference, $mode_dev = false)
	{
		$ebay_site_id = Db::getInstance()->getValue('SELECT ep.`ebay_site_id`
			FROM `'._DB_PREFIX_.'ebay_profile` ep
			INNER JOIN `'._DB_PREFIX_.'ebay_product` ep1
			ON ep.`id_ebay_profile` = ep1.`id_ebay_profile`
			AND ep1.`id_product_ref` = \''.pSQL($reference).'\'');
		if (!$ebay_site_id)
			return '';
		
		$site_extension = EbayCountrySpec::getSiteExtensionBySiteId($ebay_site_id);
		
		return 'http://cgi'.($mode_dev ? '.sandbox' : '').'.ebay.'.$site_extension.'/ws/eBayISAPI.dll?ViewItem&item='.$reference.'&ssPageName=STRK:MESELX:IT&_trksid=p3984.m1555.l2649#ht_632wt_902';
	}
	
}