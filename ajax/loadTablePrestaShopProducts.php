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

$page = (int)Tools::getValue('p', 0);
if ($page < 2)
	$page = 1;
$limit = 20;
$offset = $limit * ($page - 1);

$on_ebay_only = (Tools::getValue('mode') == 'on_ebay');
$search = Tools::getValue('s');

$is_one_five = version_compare(_PS_VERSION_, '1.5', '>');

// to check if a product has attributes (multi-variations), we check if it has a "default_on" attribute in the product_attribute table
// this prevents us of doing a double "group by" which would complexify the query
$query = 'SELECT p.`id_product`, 
				pl.`name`                                    AS name, 
				pa.`id_product_attribute`                    AS hasAttributes,
				p.`id_category_default`                      AS id_category,
				cl.`name`                                    AS psCategoryName,
				'.($is_one_five ? 's' : 'p').'.`quantity`    AS stock,
				ec.`is_multi_sku`                            AS EbayCategoryIsMultiSku,
				ecc.`sync`                                   AS sync,
				epc.`blacklisted`                            AS blacklisted,
				ep.`id_product_ref`                          AS EbayProductRef,
				ec.`id_category_ref`

	FROM `'._DB_PREFIX_.'product` p
	
	INNER JOIN `'._DB_PREFIX_.'product_lang` pl
	ON pl.`id_product` = p.`id_product`
	AND pl.`id_lang` = '.$ebay_profile->id_lang.'
	
	LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
	ON pa.`id_product` = p.`id_product`
	AND pa.default_on = 1
	';
if ($is_one_five)    
		$query .= ' LEFT JOIN `'._DB_PREFIX_.'stock_available` s 
	ON p.`id_product` = s.`id_product`
	AND s.`id_product_attribute` = 0';

$query .= ' INNER JOIN `'._DB_PREFIX_.'category_lang` cl
	ON cl.`id_category` = p.`id_category_default`
	AND cl.`id_lang` = '.$ebay_profile->id_lang.'
	
	'.($on_ebay_only ? 'INNER' : 'LEFT').' JOIN `'._DB_PREFIX_.'ebay_category_configuration` ecc
	ON ecc.`id_category` = p.`id_category_default`
	AND ecc.`id_ebay_profile` = '.$ebay_profile->id.'
	
	'.($on_ebay_only ? 'INNER' : 'LEFT').' JOIN `'._DB_PREFIX_.'ebay_category` ec
	ON ec.`id_ebay_category` = ecc.`id_ebay_category`
	
	LEFT JOIN `'._DB_PREFIX_.'ebay_product_configuration` epc
	ON epc.`id_product` = p.`id_product`
	AND epc.`id_ebay_profile` = '.$ebay_profile->id.'
	
	LEFT JOIN `'._DB_PREFIX_.'ebay_product` ep
	ON ep.`id_product` = p.`id_product`
	AND ep.`id_ebay_profile` = '.$ebay_profile->id.'
	AND ep.`id_ebay_product` = (
		SELECT MIN(ep2.`id_ebay_product`)
		FROM `'._DB_PREFIX_.'ebay_product` ep2
		WHERE ep2.`id_product` = ep.`id_product`
		AND ep2.`id_ebay_profile` = ep.`id_ebay_profile`
	)'. // With this inner query we ensure to only return one row of ebay_product. The id_product_ref is only relevant for products having only one correspondant product on eBay
	'
	WHERE 1'.$ebay->addSqlRestrictionOnLang('pl').$ebay->addSqlRestrictionOnLang('cl').$ebay->addSqlRestrictionOnLang('s');
	
if ($search)
	$query .= ' AND pl.`name` LIKE \'%'.$search.'%\'';

//$query .= ' GROUP BY s.`id_product`';
	
$queryCount = preg_replace('/SELECT ([a-zA-Z.,` ]+) FROM /', 'SELECT COUNT(*) FROM ', $query);
$nbProducts = Db::getInstance()->getValue($queryCount);
	
$res = Db::getInstance()->executeS($query.' ORDER BY p.`id_product` ASC LIMIT '.$offset.', '.$limit);

// categories
$category_list = $ebay->getChildCategories(Category::getCategories($ebay_profile->id_lang, false), version_compare(_PS_VERSION_, '1.5', '>') ? 1 : 0);

// eBay categories
$ebay_categories = EbayCategoryConfiguration::getEbayCategories($ebay_profile->id);

$content = Context::getContext();
$employee = new Employee((int)Tools::getValue('id_employee'));
$context->employee = $employee;

foreach ($res as &$row) {
	
	if ($row['EbayProductRef'])
		$row['link'] = EbayProduct::getEbayUrl($row['EbayProductRef'], $ebay_request->getDev());
	
	foreach ($category_list as $cat) {
		if ($cat['id_category'] == $row['id_category']) {
			$row['category_full_name'] = $cat['name'];
			$row['is_category_active'] = $cat['active'];
			break;                
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
	
	if ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') == 'A')
		$row['sync'] = (bool)$row['id_category_ref']; // only true if category synced with an eBay category
	
	$link = $context->link;
	
	$row['link'] = ( method_exists($link, 'getAdminLink') ? ( $link->getAdminLink('AdminProducts').'&id_product='.(int)$row['id_product'].'&updateproduct' ) : $link->getProductLink((int)$row['id_product']) );
	
}

$smarty = $context->smarty;
// Smarty datas
$template_vars = array(
	'nbPerPage' => $limit,
	'nbProducts' => $nbProducts,
	'noProductFound' => Tools::getValue('ch_no_prod_str'),
	'p' => $page,
	'products' => $res
);

$smarty->assign($template_vars);
echo $ebay->display(realpath(dirname(__FILE__).'/../'), '/views/templates/hook/table_prestashop_products.tpl');
