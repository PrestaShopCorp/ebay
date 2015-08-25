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

class TotCompatibility
{
	/*
	 * for backward compatibility
	 *
	 *
	 */
	
	public static function getCurrenciesByIdShop($id_shop = 0)
	{
		if (version_compare(_PS_VERSION_, '1.5.1', '>='))
			return Currency::getCurrenciesByIdShop($id_shop);
		elseif (version_compare(_PS_VERSION_, '1.5', '>')) {
			$sql = 'SELECT *
					FROM `'._DB_PREFIX_.'currency` c
					LEFT JOIN `'._DB_PREFIX_.'currency_shop` cs ON (cs.`id_currency` = c.`id_currency`)
					'.($id_shop != 0 ? ' WHERE cs.`id_shop` = '.(int)$id_shop : '').'
					GROUP BY c.id_currency
					ORDER BY `name` ASC';

			return Db::getInstance()->executeS($sql);
		} else {
			$sql = 'SELECT *
					FROM `'._DB_PREFIX_.'currency` c';            

			return Db::getInstance()->executeS($sql);
		}
	}
}