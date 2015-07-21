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

class EbayShippingZoneExcluded
{
	public static function getAll($id_ebay_profile)
	{
		return Db::getInstance()->ExecuteS('SELECT * 
			FROM `'._DB_PREFIX_.'ebay_shipping_zone_excluded`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.'
			ORDER BY region, description');
	}

	public static function getExcluded($id_ebay_profile)
	{
		return Db::getInstance()->ExecuteS('SELECT * 
			FROM `'._DB_PREFIX_.'ebay_shipping_zone_excluded`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.'
			AND excluded = 1');
	}

	public static function insert($data)
	{
		return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_shipping_zone_excluded', $data, 'INSERT');
	}
	
	public static function loadEbayExcludedLocations($id_ebay_profile)
	{
		$ebay_request = new EbayRequest();
		$excluded_locations = $ebay_request->getExcludeShippingLocations();

		foreach ($excluded_locations as &$excluded_location)
		{
			foreach ($excluded_location as &$field)
				$field = pSQL($field);

			$excluded_location['excluded'] = 0;
			$excluded_location['id_ebay_profile'] = intval($id_ebay_profile);
		}

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			Db::getInstance()->insert('ebay_shipping_zone_excluded', $excluded_locations);
		else
			foreach ($excluded_locations as $location)
				EbayShippingZoneExcluded::insert($location);        
	}
	
	public static function cacheEbayExcludedLocation($id_ebay_profile)
	{
		$ebay_excluded_zones = EbayShippingZoneExcluded::getAll($id_ebay_profile);

		$all = array();
		$excluded = array();
		$regions = array();

		foreach ($ebay_excluded_zones as $key => $zone)
		{
			if (!in_array($zone['region'], $regions))
				$regions[] = $zone['region'];

			$all[$zone['region']]['country'][] = array(
				'location' => $zone['location'],
				'description' => $zone['description'],
				'excluded' => $zone['excluded']
			);
		}

		foreach ($ebay_excluded_zones as $key => $zone)
			if (in_array($zone['location'], $regions))
				$all[$zone['location']]['description'] = $zone['description'];

		unset($all['Worldwide']);

		foreach ($all as $key => $value)
			if (!isset($value['description']))
				$all[$key]['description'] = $key;

		//get real excluded location
		foreach (EbayShippingZoneExcluded::getExcluded($id_ebay_profile) as $zone)
			$excluded[] = $zone['location'];

		return array(
			'all' => $all,
			'excluded' => $excluded
		);
	}
	
}