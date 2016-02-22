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

if (
    !Tools::getValue('token')
    || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN')) {
    die('ERROR : INVALID TOKEN');
}

if (
    !($errorcode = Tools::getValue('errorcode'))
    || !($lang = Tools::getValue('lang'))
) {
    die('ERROR : INVALID DATA');
}

$name_module = basename(realpath(dirname(__FILE__).TMP_DS.'..'.TMP_DS));
$module_kb = Tools::ucfirst(Tools::strtolower($name_module)).'Kb';

if (Validate::isString($name_module) && Module::isInstalled($name_module)) {
    $module = Module::getInstanceByName($name_module);

    if (version_compare(_PS_VERSION_, '1.5', '<')) {
        $enable = $module->active;
    } else {
        $enable = Module::isEnabled($name_module);
    }

    if ($enable) {
        $kb = new $module_kb();
        $kb->setLanguage(Tools::getValue('lang'));
        $kb->setErrorCode(Tools::getValue('errorcode'));

        if ($result = $kb->getLink()) {
            die(Tools::jsonEncode(array('result' => $result, 'code' => 'kb-202')));
        } else {
            die(Tools::jsonEncode(array('result' => 'error', 'code' => 'kb-001', 'more' => 'Aucun lien trouvé')));
        }

    }
}
