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

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../ebay.php');

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN') && Tools::getValue('profile') && Tools::getValue('step') && Tools::getValue('id_category'))
	die('ERROR : INVALID TOKEN');

$ebay_request = new EbayRequest();
$id_ebay_profile = (int)Tools::getValue('profile');
$ebay_profile = new EbayProfile($id_ebay_profile);
$id_category = Tools::getValue('id_category');

// Etape 1 : Récupérer les catégorie root
if ((int)Tools::getValue('step') == 1)
{
	if ($cat_root = $ebay_request->getCategories(false))
		echo json_encode($cat_root);
	else
		echo json_encode('error');
}
else if ((int)Tools::getValue('step') == 2)
{
	$cat = $ebay_request->getCategories((int)Tools::getValue('id_category'));
	if ($toto = EbayCategory::insertCategories($ebay_profile->ebay_site_id, $cat, $ebay_request->getCategoriesSkuCompliancy()))
		echo json_encode($cat);
	else
		echo json_encode('error');
}
else if ((int)Tools::getValue('step') == 3)
{
	Configuration::updateValue('EBAY_CATEGORY_LOADED_'.$ebay_profile->ebay_site_id, 1);
}