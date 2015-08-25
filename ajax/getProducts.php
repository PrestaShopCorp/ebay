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

include_once (dirname(__FILE__).'/../../../config/config.inc.php');
include_once dirname(__FILE__).'/../classes/EbayCountrySpec.php';
include_once dirname(__FILE__).'/../classes/EbayProductConfiguration.php';

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR : INVALID TOKEN');

$ebay = new Ebay();
$ebay_country = EbayCountrySpec::getInstanceByKey(Configuration::get('EBAY_COUNTRY_DEFAULT'));
$id_lang = $ebay_country->getIdLang();
$id_ebay_profile = (int)Tools::getValue('id_ebay_profile');

$is_one_five = version_compare(_PS_VERSION_, '1.5', '>');

if ($is_one_five) {

	$sql = 'SELECT p.`id_product` as id, pl.`name`, epc.`blacklisted`, epc.`extra_images`, sa.`quantity` as stock
			FROM `'._DB_PREFIX_.'product` p';

	$sql .= Shop::addSqlAssociation('product', 'p');
	$sql .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
				ON (p.`id_product` = pl.`id_product`
				AND pl.`id_lang` = '.(int)$id_lang;
	$sql .= Shop::addSqlRestrictionOnLang('pl');
	$sql .= ')
			LEFT JOIN `'._DB_PREFIX_.'ebay_product_configuration` epc
				ON p.`id_product` = epc.`id_product` AND epc.id_ebay_profile = '.$id_ebay_profile.'
			LEFT JOIN `'._DB_PREFIX_.'stock_available` sa
				ON p.`id_product` = sa.`id_product`
				AND sa.`id_product_attribute` = 0
			WHERE ';
		
	$sql .= ' product_shop.`id_shop` = 1 AND ';
	$sql .= ' p.`id_category_default` = '.(int)Tools::getValue('category');
	$sql .= $ebay->addSqlRestrictionOnLang('sa');
	
} else {
	
	$sql = 'SELECT p.`id_product` as id, pl.`name`, epc.`blacklisted`, epc.`extra_images`, p.`quantity` as stock
			FROM `'._DB_PREFIX_.'product` p';

	$sql .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
				ON (p.`id_product` = pl.`id_product`
				AND pl.`id_lang` = '.(int)$id_lang;
	$sql .= ')
			LEFT JOIN `'._DB_PREFIX_.'ebay_product_configuration` epc
				ON p.`id_product` = epc.`id_product` AND epc.id_ebay_profile = '.$id_ebay_profile.'

			WHERE ';

	$sql .= ' p.`id_category_default` = '.(int)Tools::getValue('category');
	
}

$res = Db::getInstance()->ExecuteS($sql);
foreach ($res as &$row) 
{
	
	$row['name'] = Tools::safeOutput($row['name']);
	$row['blacklisted'] = Tools::safeOutput($row['blacklisted']);
	$row['extra_images'] = Tools::safeOutput($row['extra_images']);
	$row['stock'] = Tools::safeOutput($row['stock']);
	
}

echo Tools::jsonEncode($res);
