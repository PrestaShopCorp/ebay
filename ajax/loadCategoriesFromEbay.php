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

$base_path = dirname(__FILE__).TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'..'.TMP_DS;
require_once dirname(__FILE__).TMP_DS.'..'.TMP_DS.'classes'.TMP_DS.'EbayTools.php';

if (EbayTools::getValue('admin_path')) {
    define('_PS_ADMIN_DIR_', realpath(dirname(__FILE__).TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'..'.TMP_DS).TMP_DS.EbayTools::getValue('admin_path').TMP_DS);
}

require_once dirname(__FILE__).TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'config'.TMP_DS.'config.inc.php';

if (version_compare(_PS_VERSION_, '1.5', '>')) {
    include_once _PS_ADMIN_DIR_.'init.php';
} else {
    include_once dirname(__FILE__).TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'..'.TMP_DS.'init.php';
}

if (!Tools::getValue('token')
    || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN')) {
    die('ERROR : INVALID TOKEN');
}

if (!($id_profile = Tools::getValue('profile')) || !Validate::isInt($id_profile)
    || !($step = Tools::getValue('step')) || !Validate::isInt($step)
    || !($cat = Tools::getValue('id_category'))
) {
    die('ERROR : INVALID DATA');
}

if (Module::isInstalled('ebay')) {
    $ebay = Module::getInstanceByName('ebay');

    if (version_compare(_PS_VERSION_, '1.5', '<')) {
        $enable = $ebay->active;
    } else {
        $enable = Module::isEnabled('ebay');
    }

    if ($enable) {
        $ebay_request = new EbayRequest();
        $ebay_profile = new EbayProfile((int) $id_profile);

        if ($step == 1) {
            EbayCategory::deleteCategoriesByIdCountry($ebay_profile->ebay_site_id);
            if ($cat_root = $ebay_request->getCategories(false)) {
                die(Tools::jsonEncode($cat_root));
            } else {
                die(Tools::jsonEncode('error'));
            }
        } else if ($step == 2) {
            $cat = $ebay_request->getCategories((int) $cat);

            if (EbayCategory::insertCategories($ebay_profile->ebay_site_id, $cat, $ebay_request->getCategoriesSkuCompliancy())) {
                die(Tools::jsonEncode($cat));
            } else {
                die(Tools::jsonEncode('error'));
            }
        } elseif ($step == 3) {
            Configuration::updateValue('EBAY_CATEGORY_LOADED_'.$ebay_profile->ebay_site_id, 1);
        }
    }
}
