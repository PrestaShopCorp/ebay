<?php
/**
 * 2007-2016 PrestaShop
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
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_order_return_detail` (
    `id` INT(30)AUTO_INCREMENT PRIMARY KEY,
    `id_return` VARCHAR(125)NOT NULL,
    `type` VARCHAR(125),
    `date` datetime ,
    `description` VARCHAR(255),
    `status` VARCHAR(125),
    `id_order` VARCHAR(125),
    `id_ebay_order` VARCHAR(125),
    `id_transaction` VARCHAR(125),
    `id_item` VARCHAR(125)
)ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'ebay_order_order` ADD `id_transaction` varchar(125 ) NOT NULL';

$sql[] = 'ALTER TABLE '._DB_PREFIX_.'ebay_product MODIFY `id_shipping_policies` varchar(125 )';
