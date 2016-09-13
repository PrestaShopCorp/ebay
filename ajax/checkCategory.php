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

if (!defined('TMP_DS')) {
    define('TMP_DS', DIRECTORY_SEPARATOR);
}

require_once dirname(__FILE__).TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'config'.TMP_DS.'config.inc.php';
include_once dirname(__FILE__).TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'init.php';
include dirname(__FILE__).'/../classes/EbayDbValidator.php';

if (!Configuration::get('EBAY_SECURITY_TOKEN') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN')) {
    die('INVALID TOKEN');
}

if (Module::isInstalled('ebay')) {
    $module = Module::getInstanceByName('ebay');

    if (version_compare(_PS_VERSION_, '1.5', '<')) {
        $enable = $module->active;
    } else {
        $enable = Module::isEnabled('ebay');
    }

    if ($enable) {
        $validator = new EbayDbValidator();
        if (Tools::getValue('action') == 'checkCategories') {
            $step = Tools::getValue('step');
            $ebay_request = new EbayRequest();
            if ($step == 1) {
                $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ebay_category_tmp` (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(45) DEFAULT NULL,
            id_categories int,
       id_categories_ref_parent int,
       level tinyint(1),
            PRIMARY KEY (id)
            )';
                Db::getInstance()->execute($sql);
                if ($cat_root = $ebay_request->getCategories(false)) {

                    die(Tools::jsonEncode($cat_root));
                } else {
                    die(Tools::jsonEncode('error'));
                }
            } else if ($step == 2) {
                $cat = Tools::getValue('id_categories');
                $cats = $ebay_request->getCategories((int) $cat);
                foreach ($cats as $cat) {
                    Db::getInstance()->insert('ebay_category_tmp', array(
                        'id_categories' => pSQL($cat['CategoryID']),
                        'name' => pSQL($cat['CategoryName']),
                        'id_categories_ref_parent' => pSQL($cat['CategoryParentID']),
                        'level' => pSQL($cat['CategoryLevel'])
                    ));
                };
                    die(Tools::jsonEncode($cat));

            } elseif ($step == 3) {
                $id_profile_ebay = Tools::getValue('id_profile_ebay');
                $res=$validator->comparationCategories($id_profile_ebay);
                $validator->deleteTmp();
                echo Tools::jsonEncode($res);
            }
           
           
        }
        
    }
}
