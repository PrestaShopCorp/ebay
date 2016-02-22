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
include dirname(__FILE__).'/../classes/EbayCategorySpecific.php';
include dirname(__FILE__).'/../classes/EbayCategoryCondition.php';

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN')) {
    die('ERROR : INVALID TOKEN');
}

$id_ebay_profile = (int) Tools::getValue('profile');
$ebay_profile = new EbayProfile($id_ebay_profile);

function loadItemsMap($row)
{
    return $row['id'];
}

/* Fix for limit db sql request in time */
sleep(1);

$category = new EbayCategory($ebay_profile, (int) Tools::getValue('ebay_category'));

$last_upd = $ebay_profile->getConfiguration('EBAY_SPECIFICS_LAST_UPDATE');

$update = false;

if (Tools::jsonDecode($last_upd) === null) {
    $last_update = array();
    $update = true;
} else {
    $last_update = get_object_vars(Tools::jsonDecode($last_upd));

    if (!isset($last_update[$category->getIdCategoryRef()])
        || ($last_update[$category->getIdCategoryRef()] < date('Y-m-d\TH:i:s', strtotime('-3 days')).'.000Z')) {
        $update = true;
    }
}

if ($update) {
    $time = time();
    $res = EbayCategorySpecific::loadCategorySpecifics($id_ebay_profile, $category->getIdCategoryRef());
    $res &= EbayCategoryCondition::loadCategoryConditions($id_ebay_profile, $category->getIdCategoryRef());

    if ($res) {
        $last_update[$category->getIdCategoryRef()] = date('Y-m-d\TH:i:s.000\Z');
        $ebay_profile->setConfiguration('EBAY_SPECIFICS_LAST_UPDATE', Tools::jsonEncode($last_update), false);
    }

}

$item_specifics = $category->getItemsSpecifics();
$item_specifics_ids = array_map('loadItemsMap', $item_specifics);

if (count($item_specifics_ids)) {
    $sql = 'SELECT `id_ebay_category_specific_value` as id, `id_ebay_category_specific` as specific_id, `value`
        FROM `'._DB_PREFIX_.'ebay_category_specific_value`
        WHERE `id_ebay_category_specific` in ('.implode(',', $item_specifics_ids).')';

    $item_specifics_values = DB::getInstance()->executeS($sql);
} else {
    $item_specifics_values = array();
}

foreach ($item_specifics as &$item_specific) {
    foreach ($item_specifics_values as $value) {
        if ($item_specific['id'] == $value['specific_id']) {
            $item_specific['values'][$value['id']] = Tools::safeOutput($value['value']);
        }
    }
}

echo Tools::jsonEncode(array(
    'specifics' => $item_specifics,
    'conditions' => $category->getConditionsWithConfiguration($id_ebay_profile),
    'is_multi_sku' => $category->isMultiSku(),
));
