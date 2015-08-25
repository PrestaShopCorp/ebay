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

include_once dirname(__FILE__).'/../../../config/config.inc.php';
include_once dirname(__FILE__).'/../../../init.php';
include_once dirname(__FILE__).'/../ebay.php';

function array_insert_after($key, array &$array, $new_key, $new_value) 
{
	if (array_key_exists($key, $array)) 
	{
			
		$new = array();

		foreach ($array as $k => $value) 
		{
		
			$new[$k] = $value;
			if ($k === $key)
				$new[$new_key] = $new_value;
		
		}
		
		return $new;

	}

	return false;
}

$ebay = new Ebay();

$ebay_profile = new EbayProfile((int)Tools::getValue('profile'));

if (!Configuration::get('EBAY_SECURITY_TOKEN') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	return Tools::safeOutput(Tools::getValue('not_logged_str'));

$category_list = $ebay->getChildCategories(Category::getCategories(Tools::getValue('id_lang')), version_compare(_PS_VERSION_, '1.5', '>') ? 1 : 0);

$offset = 20;
$page = (int)Tools::getValue('p', 0);
if ($page < 2)
	$page = 1;
$limit = $offset * ($page - 1);
$category_list = array_slice($category_list, $limit, $offset);

$ebay_store_category_list = EbayStoreCategory::getCategoriesWithConfiguration($ebay_profile->id);

$smarty = Context::getContext()->smarty;

/* Smarty datas */
$template_vars = array(
	'tabHelp' => '&id_tab=7',
	'_path' => $ebay->getPath(),
	'categoryList' => $category_list,
	'eBayStoreCategoryList' => $ebay_store_category_list,
	'request_uri' => $_SERVER['REQUEST_URI'],
	'noCatFound' => Tools::getValue('ch_no_cat_str'),
	'p' => $page
);

$smarty->assign($template_vars);
echo $ebay->display(realpath(dirname(__FILE__).'/../'), '/views/templates/hook/table_store_categories.tpl');