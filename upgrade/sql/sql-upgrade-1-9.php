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

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_store_category` (
    `id_ebay_store_category` int(16) NOT NULL AUTO_INCREMENT,
	`id_ebay_profile` int(16) NOT NULL,    
	`ebay_category_id` int(16) NOT NULL,
	`name` varchar(255) NOT NULL,
	`order` int(16) NOT NULL,
	`ebay_parent_category_id` int(16) NOT NULL,
	UNIQUE(`id_ebay_profile`, `ebay_category_id`),
	PRIMARY KEY  (`id_ebay_store_category`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
    
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_store_category_configuration` (
    `id_ebay_store_category_configuration` int(16) NOT NULL AUTO_INCREMENT,
	`id_ebay_profile` int(16) NOT NULL,
    `ebay_category_id` int(16) NOT NULL,
    `id_category` int(16) NOT NULL,
	UNIQUE(`id_ebay_profile`, `id_category`),
	PRIMARY KEY  (`id_ebay_store_category_configuration`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_api_log` (
	`id_ebay_api_log` int(16) NOT NULL AUTO_INCREMENT,
	`id_ebay_profile` int(16) NOT NULL,
	`type` varchar(40) NOT NULL,
	`context` varchar(40) NOT NULL,
	`data_sent` text NOT NULL,
	`response` text NOT NULL,
	`id_product` int(16),
	`id_order` int(16),
    `date_add` datetime NOT NULL,
	PRIMARY KEY  (`id_ebay_api_log`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_order_log` (
	`id_ebay_order_log` int(16) NOT NULL AUTO_INCREMENT,
	`id_ebay_profile` int(16) NOT NULL,
    `id_ebay_order` int(16) NOT NULL,
    `id_orders` varchar(255),
    `type` varchar(40) NOT NULL,
	`success` tinyint(1) NOT NULL,
	`data` text,
    `date_add` datetime NOT NULL,
    `date_update` datetime,
	PRIMARY KEY  (`id_ebay_order_log`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';