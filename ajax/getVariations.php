<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once (dirname(__FILE__).'/../../../config/config.inc.php');
include_once dirname(__FILE__).'/../classes/EbayCountrySpec.php';
include_once dirname(__FILE__).'/../classes/EbayProductConfiguration.php';

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR : INVALID TOKEN');

$ebay = new Ebay();
$ebay_country = EbayCountrySpec::getInstanceByKey(Configuration::get('EBAY_COUNTRY_DEFAULT'));
$ebay_request = new EbayRequest();
$id_lang = $ebay_country->getIdLang();
$id_ebay_profile = (int)Tools::getValue('id_ebay_profile');
$id_product = (int)Tools::getValue('product');

$is_one_five = version_compare(_PS_VERSION_, '1.5', '>');

$sql = 'SELECT pa.`id_product_attribute`,
		sa.`quantity`           AS stock,
		al.`name`               AS name,
		ep.`id_product_ref`     AS id_product_ref
	FROM `'._DB_PREFIX_.'product_attribute` pa
	
	INNER JOIN `'._DB_PREFIX_.'product_attribute_combination` pac
	ON pac.`id_product_attribute` = pa.`id_product_attribute`
	
	INNER JOIN `'._DB_PREFIX_.'attribute` a
	ON a.`id_attribute` = pac.`id_attribute`
	
	INNER JOIN `'._DB_PREFIX_.'attribute_lang` al
	ON al.`id_attribute` = pac.`id_attribute`
	AND al.`id_lang` = '.(int)$id_lang.'
	
	LEFT JOIN '. ($is_one_five ? '`'._DB_PREFIX_.'stock_available`' : '`'._DB_PREFIX_.'product_attribute`').' sa
	ON sa.`id_product_attribute` = pa.`id_product_attribute`
	
	LEFT JOIN `'._DB_PREFIX_.'ebay_product` ep
	ON ep.`id_product` = pa.`id_product`
	AND ep.`id_attribute` = pac.`id_product_attribute`
	AND ep.`id_ebay_profile` = '.$id_ebay_profile.'
	
	WHERE pa.`id_product` = '.$id_product.$ebay->addSqlRestrictionOnLang('sa').'
	
	ORDER BY a.`position` ASC';
	
$res = Db::getInstance()->ExecuteS($sql);

$final_res = array();
foreach ($res as $row) 
{
	if (isset($final_res[$row['id_product_attribute']])) {

		$final_res[$row['id_product_attribute']]['name'].= ' '.Tools::safeOutput($row['name']);

	} else {

		$row['name'] = Tools::safeOutput($row['name']);
		$row['stock'] = Tools::safeOutput($row['stock']);
		$row['id_product_ref'] = Tools::safeOutput($row['id_product_ref']);
		if ($row['id_product_ref'])
			$row['link'] = EbayProduct::getEbayUrl($row['id_product_ref'], $ebay_request->getDev());
		
		$final_res[$row['id_product_attribute']] = $row;

	}
	
}

echo Tools::jsonEncode($final_res);
