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

class EbayStoreCategoryConfiguration
{
	public static function deleteByIdEbayProfile($id_ebay_profile)
	{
		return Db::getInstance()->delete(_DB_PREFIX_.'ebay_store_category_configuration', '`id_ebay_profile` = '.(int)$id_ebay_profile);
	}
	
	public static function insert($id_ebay_profile, $ebay_category_id, $id_category)
	{
		$data = array(
		  'id_ebay_profile'     => (int)$id_ebay_profile,
		  'ebay_category_id'    => pSQL($ebay_category_id),
		  'id_category'         => (int)$id_category
		);
		
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_store_category_configuration', $data, 'INSERT');
	}

	public static function update($id_ebay_profile, $ebay_category_id, $id_category)
	{
		if(!self::getEbayStoreCategoryIdByIdProfileAndIdCategory($id_ebay_profile, $id_category))
			self::insert($id_ebay_profile, $ebay_category_id, $id_category);
		else
		{
			$id = self::getIdByIdProfileAndIDCategory($id_ebay_profile, $id_category);
			Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."ebay_store_category_configuration SET ebay_category_id = '".pSQL($ebay_category_id)."' WHERE id_ebay_store_category_configuration = '".(int)$id."'");
		}
	}

	public static function getIdByIdProfileAndIdCategory($id_ebay_profile, $id_category)
	{
		return Db::getInstance()->getValue('SELECT `id_ebay_store_category_configuration`
			FROM `'._DB_PREFIX_.'ebay_store_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.'
			AND `id_category` = '.(int)$id_category);
	}
	
	public static function getEbayStoreCategoryIdByIdProfileAndIdCategory($id_ebay_profile, $id_category)
	{
		return Db::getInstance()->getValue('SELECT `ebay_category_id`
			FROM `'._DB_PREFIX_.'ebay_store_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.'
			AND `id_category` = '.(int)$id_category);
	}
	
	public static function checkExistingCategories($id_ebay_profile)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_store_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.'
			AND `ebay_category_id` NOT IN (
				SELECT `ebay_category_id` 
				FROM `'._DB_PREFIX_.'ebay_store_category`
				WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.')');
	}
	
}