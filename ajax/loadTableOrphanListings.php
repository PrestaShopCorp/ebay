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

include_once dirname(__FILE__).'/../../../config/config.inc.php';
include_once dirname(__FILE__).'/../../../init.php';
include_once dirname(__FILE__).'/../ebay.php';

$ebay = new Ebay();

$ebay_profile = new EbayProfile((int)Tools::getValue('profile'));
$ebay_request = new EbayRequest();

if (!Configuration::get('EBAY_SECURITY_TOKEN') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	return Tools::safeOutput(Tools::getValue('not_logged_str'));

// to check if a product has attributes (multi-variations), we check if it has a "default_on" attribute in the product_attribute table
$query = 'SELECT DISTINCT(ep.`id_ebay_product`),
		ep.`id_product_ref`,
		ep.`id_product`,
		ep.`id_attribute`                    AS `notSetWithMultiSkuCat`,
		epc.`blacklisted`,
		p.`id_product`                       AS `exists`,
		p.`id_category_default`,   
		p.`active`,
		pa.`id_product_attribute`            AS isMultiSku,
		pl.`name`                            AS psProductName,
		ecc.`id_ebay_category_configuration` AS EbayCategoryExists,
		ec.`is_multi_sku`                    AS EbayCategoryIsMultiSku,
		ecc.`sync`                           AS sync,
		ec.`id_category_ref`
	FROM `'._DB_PREFIX_.'ebay_product` ep

	LEFT JOIN `'._DB_PREFIX_.'ebay_product_configuration` epc
	ON epc.`id_product` = ep.`id_product`
	AND epc.`id_ebay_profile` = '.$ebay_profile->id.'

	LEFT JOIN `'._DB_PREFIX_.'product` p
	ON p.`id_product` = ep.`id_product`
	
	LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
	ON pl.`id_product` = p.`id_product`
	AND pl.`id_lang` = '.$ebay_profile->id_lang.'    
	
	LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
	ON pa.`id_product` = p.`id_product`
	AND pa.default_on = 1    
	
	LEFT JOIN `'._DB_PREFIX_.'ebay_category_configuration` ecc
	ON ecc.`id_category` = p.`id_category_default`
	AND ecc.`id_ebay_profile` = '.$ebay_profile->id.'
	
	LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec
	ON ec.`id_ebay_category` = ecc.`id_ebay_category`
	
	WHERE ep.`id_ebay_profile` = '.$ebay_profile->id;

//$currency = new Currency((int)$ebay_profile->getConfiguration('EBAY_CURRENCY'));

// categories
$category_list = $ebay->getChildCategories(Category::getCategories($ebay_profile->id_lang, false), version_compare(_PS_VERSION_, '1.5', '>') ? 1 : 0);

// eBay categories
$ebay_categories = EbayCategoryConfiguration::getEbayCategories($ebay_profile->id);

$res = Db::getInstance()->executeS($query);

$final_res = array();
foreach ($res as &$row) {
	
	if ($row['id_product_ref'])
		$row['link'] = EbayProduct::getEbayUrl($row['id_product_ref'], $ebay_request->getDev());
	
	if ($row['id_category_default']) {

		foreach ($category_list as $cat) {
			if ($cat['id_category'] == $row['id_category_default']) {
				$row['category_full_name'] = $cat['name'];
				break;                
			}
		}
		
	}
	
	if ($row['id_category_ref']) {

		foreach($ebay_categories as $cat) {
		
			if ($cat['id'] == $row['id_category_ref']) {
				$row['ebay_category_full_name'] = $cat['name'];
				break;                
			}        
		}
		
	}
	
	if ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') == 'A') {
		$row['sync'] = (bool)$row['EbayCategoryExists']; // only true if category synced with an eBay category
	}

	// filtering
	if (!$row['exists'])
		$final_res[] = $row;
	
	elseif (!$row['EbayCategoryExists'])
		$final_res[] = $row;
	
	elseif ($row['isMultiSku']
		&& !$row['notSetWithMultiSkuCat'] // set as if on a MultiSku category
		&& !$row['EbayCategoryIsMultiSku']
		)
		$final_res[] = $row;
	
	elseif ($row['notSetWithMultiSkuCat']
		&& $row['EbayCategoryIsMultiSku'])
		$final_res[] = $row;
	
	elseif (!$row['active'] || $row['blacklisted'])            
		$final_res[] = $row;
	
	elseif (!$row['sync'])
		$final_res[] = $row;

}

$smarty = Context::getContext()->smarty;

// Smarty datas
$template_vars = array(
	'ads' => $final_res
);

$smarty->assign($template_vars);
echo $ebay->display(realpath(dirname(__FILE__).'/../'), '/views/templates/hook/table_orphan_listings.tpl');