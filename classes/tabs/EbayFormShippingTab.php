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

class EbayFormShippingTab extends EbayTab
{

	function getContent()
	{
		$configKeys = array(
			'EBAY_SECURITY_TOKEN',
			'PS_LANG_DEFAULT'
		);
		// Load prestashop ebay's configuration
		$configs = Configuration::getMultiple($configKeys);
		
		$profile_configs = $this->ebay_profile->getMultiple(array(
			'EBAY_DELIVERY_TIME',
			'EBAY_ZONE_NATIONAL',
			'EBAY_ZONE_INTERNATIONAL',
		));
		
		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
		{
			$template_vars = array('error_form_shipping' => 'true');
			return $this->display('error_paypal_email.tpl', $template_vars);
		}

		$nb_shipping_zones_excluded = DB::getInstance()->getValue('SELECT COUNT(*) 
			FROM '._DB_PREFIX_.'ebay_shipping_zone_excluded
			WHERE `id_ebay_profile` = '.(int)$this->ebay_profile->id);

		if (!$nb_shipping_zones_excluded)
			EbayShippingZoneExcluded::loadEbayExcludedLocations($this->ebay_profile->id);

		$module_filters = version_compare(_PS_VERSION_, '1.4.5', '>=') ? Carrier::CARRIERS_MODULE : 2;

		//INITIALIZE CACHE
		$psCarrierModule = $this->ebay_profile->getCarriers($configs['PS_LANG_DEFAULT'], false, false, false, null, $module_filters);

		$url_vars = array(
			'id_tab' => '3',
			'section' =>'shipping'
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::getValue('controller');
		else
			$url_vars['tab'] = Tools::getValue('tab');

		$zones = Zone::getZones(true);
		foreach ($zones as &$zone)
			$zone['carriers'] = Carrier::getCarriers($this->context->language->id, false, false, $zone['id_zone']);

		$template_vars = array(
			'eBayCarrier' => EbayShippingService::getCarriers($this->ebay_profile->ebay_site_id),
			'psCarrier' => $this->ebay_profile->getCarriers($configs['PS_LANG_DEFAULT']),
			'psCarrierModule' => $psCarrierModule,
			'existingNationalCarrier' => EbayShipping::getNationalShippings($this->ebay_profile->id),
			'existingInternationalCarrier' => EbayShippingInternationalZone::getExistingInternationalCarrier($this->ebay_profile->id),
			'deliveryTime' => $profile_configs['EBAY_DELIVERY_TIME'],
			'prestashopZone' => Zone::getZones(),
			'excludeShippingLocation' => EbayShippingZoneExcluded::cacheEbayExcludedLocation($this->ebay_profile->id),
			'internationalShippingLocations' => EbayShippingLocation::getInternationalShippingLocations(),
			'deliveryTimeOptions' => EbayDeliveryTimeOptions::getDeliveryTimeOptions(),
			'formUrl' => $this->_getUrl($url_vars),
			'ebayZoneNational' => (isset($profile_configs['EBAY_ZONE_NATIONAL']) ? $profile_configs['EBAY_ZONE_NATIONAL'] : false),
			'ebayZoneInternational' => (isset($profile_configs['EBAY_ZONE_INTERNATIONAL']) ? $profile_configs['EBAY_ZONE_INTERNATIONAL'] : false),
			'ebay_token' => $configs['EBAY_SECURITY_TOKEN'],
			'id_ebay_profile' => $this->ebay_profile->id,	
			'newPrestashopZone' => $zones,
		);

		return $this->display('shipping.tpl', $template_vars);
	}
	
	
	function postProcess()
	{
		//Update excluded location
		if (Tools::getValue('excludeLocationHidden'))
		{
			Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'ebay_shipping_zone_excluded 
				SET excluded = 0
				WHERE `id_ebay_profile` = '.(int)$this->ebay_profile->id);

			if ($exclude_locations = Tools::getValue('excludeLocation'))
			{
				$locations = array_keys($exclude_locations);
				$where = 'location IN ("'.implode('","', array_map('pSQL', $locations)).'")';
				
				$where .= ' AND `id_ebay_profile` = '.(int)$this->ebay_profile->id;

				if (version_compare(_PS_VERSION_, '1.5', '>'))
					DB::getInstance()->update('ebay_shipping_zone_excluded', array('excluded' => 1), $where);
				else
					Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_shipping_zone_excluded', array('excluded' => 1), 'UPDATE', $where );
			}
		}

		//Update global information about shipping (delivery time, ...)
		$this->ebay_profile->setConfiguration('EBAY_DELIVERY_TIME', Tools::getValue('deliveryTime'));
		//Update Shipping Method for National Shipping (Delete And Insert)
		EbayShipping::truncate($this->ebay_profile->id);

		if ($ebay_carriers = Tools::getValue('ebayCarrier'))
		{

			$ps_carriers = Tools::getValue('psCarrier');
			$extra_fees = Tools::getValue('extrafee');

			foreach ($ebay_carriers as $key => $ebay_carrier)
			{
				if (!empty($ebay_carrier) && !empty($ps_carriers[$key]))
				{
					//Get id_carrier and id_zone from ps_carrier
					$infos = explode('-', $ps_carriers[$key]); 
					EbayShipping::insert($this->ebay_profile->id, $ebay_carrier, $infos[0], $extra_fees[$key], $infos[1]);
				}
			}
		}

		Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'ebay_shipping_international_zone
			WHERE `id_ebay_profile` = '.(int)$this->ebay_profile->id);

		if ($ebay_carriers_international = Tools::getValue('ebayCarrier_international'))
		{
			$ps_carriers_international = Tools::getValue('psCarrier_international');
			$extra_fees_international = Tools::getValue('extrafee_international');
			$international_shipping_locations = Tools::getValue('internationalShippingLocation');
			$international_excluded_shipping_locations = Tools::getValue('internationalExcludedShippingLocation');

			foreach ($ebay_carriers_international as $key => $ebay_carrier_international)
			{			
				if (!empty($ebay_carrier_international) && !empty($ps_carriers_international[$key]) && isset($international_shipping_locations[$key]))
				{
					$infos = explode('-', $ps_carriers_international[$key]); 
					EbayShipping::insert($this->ebay_profile->id, $ebay_carrier_international, $infos[0], $extra_fees_international[$key], $infos[1], true);
					$last_id = EbayShipping::getLastShippingId($this->ebay_profile->id);

					foreach (array_keys($international_shipping_locations[$key]) as $id_ebay_zone)
						EbayShippingInternationalZone::insert($this->ebay_profile->id, $last_id, $id_ebay_zone);
				}
			}
		}        
		
	}
	

}