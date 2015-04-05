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
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once dirname(__FILE__).'/../../../config/config.inc.php';
include_once dirname(__FILE__).'/../../../init.php';
include_once dirname(__FILE__).'/../ebay.php';

$ebay = new Ebay();

$ebay_profile = new EbayProfile((int)Tools::getValue('profile'));

if (!Configuration::get('EBAY_SECURITY_TOKEN') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	return Tools::safeOutput(Tools::getValue('not_logged_str'));

// to check if a product has attributes (multi-variations), we check if it has a "default_on" attribute in the product_attribute table
$query = 'SELECT ep.`id_ebay_product`,
        ep.`id_product_ref`,
        ep.`id_product`,
        p.`id_product` AS `exists`,
        p.`active`,
        pa.`id_product_attribute` AS isMultiSku,
        pl.`name`           AS psProductName,
        cl.`name`           AS psCategoryName,
        ec.`name`           AS EbayCategoryName,
        ec.`is_multi_sku`   AS EbayCategoryIsMultiSku,
        ecc.`sync`          AS sync
    FROM `'._DB_PREFIX_.'ebay_product` ep

    LEFT JOIN `'._DB_PREFIX_.'product` p
    ON p.`id_product` = ep.`id_product`
    
    INNER JOIN `'._DB_PREFIX_.'product_lang` pl
    ON pl.`id_product` = p.`id_product`
    AND pl.`id_lang` = '.$ebay_profile->id_lang.'    
    
    LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
    ON pa.`id_product` = p.`id_product`
    AND pa.default_on = 1    
    
    LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
    ON cl.`id_category` = p.`id_category_default`
    AND cl.`id_lang` = '.$ebay_profile->id_lang.'
    
    LEFT JOIN `'._DB_PREFIX_.'ebay_category_configuration` ecc
    ON ecc.`id_category` = p.`id_category_default`
    AND ecc.`id_ebay_profile` = '.$ebay_profile->id.'
    
    LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec
    ON ec.`id_ebay_category` = ecc.`id_ebay_category`
    
    WHERE 1'.$ebay->addSqlRestrictionOnLang('cl');    

//$currency = new Currency((int)$ebay_profile->getConfiguration('EBAY_CURRENCY'));

$res = Db::getInstance()->executeS($query);
$smarty = Context::getContext()->smarty;

// Smarty datas
$template_vars = array(
    /*
	'tabHelp' => '&id_tab=15',
	'_path' => $ebay->getPath(),
	'categoryList' => $category_list,
	'eBayCategoryList' => $ebay_category_list,
	'getCatInStock' => $get_cat_in_stock,
	'categoryConfigList' => $category_config_list,
	'request_uri' => $_SERVER['REQUEST_URI'],
	'noCatSelected' => Tools::getValue('ch_cat_str'),
	'noCatFound' => Tools::getValue('ch_no_cat_str'),
	'currencySign' => $currency->sign,
    */
//    'noProductFound' => Tools::getValue('ch_no_prod_str'),
//	'p' => $page,
//    'products' => $res,
    'noAdFound' => Tools::getValue('ch_no_ad_found'),
    'ads' => $res
);

$smarty->assign($template_vars);
echo $ebay->display(realpath(dirname(__FILE__).'/../'), '/views/templates/hook/table_orphan_ads.tpl');