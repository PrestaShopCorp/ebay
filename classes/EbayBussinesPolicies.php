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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayBussinesPolicies
{

    public static function getPoliciesbyType($type, $id_ebay_profile)
    {
        return Db::getInstance()->executeS('SELECT name, id_bussines_Policie FROM ' . _DB_PREFIX_ . 'ebay_business_policies WHERE type ="' . $type . '" AND id_ebay_profile=' . $id_ebay_profile);

    }

    public static function setBussinesPolicies($id_ebay_profile, $var)
    {
        EbayConfiguration::set($id_ebay_profile, $var['type'], $var['id_bussines_Policie']);

    }

    public static function getPoliciesConfigurationbyIdCategory($id_category, $id_ebay_profile)
    {
        return Db::getInstance()->executeS('SELECT id_return, id_payment FROM ' . _DB_PREFIX_ . 'ebay_category_business_config WHERE id_category ="' . $id_category . '" AND id_ebay_profile=' . $id_ebay_profile);

    }

    public static function getPoliciesbyID($id_bussines_Policie, $id_ebay_profile)
    {
        return Db::getInstance()->executeS('SELECT name FROM ' . _DB_PREFIX_ . 'ebay_business_policies WHERE id_bussines_Policie ="' . $id_bussines_Policie . '" AND id_ebay_profile=' . $id_ebay_profile);

    }

    public static function getPoliciesbyName($name, $id_ebay_profile)
    {
        return Db::getInstance()->executeS('SELECT name, id_bussines_Policie FROM ' . _DB_PREFIX_ . 'ebay_business_policies WHERE name ="' . $name . '" AND id_ebay_profile=' . $id_ebay_profile);

    }

    public static function deletePoliciesConfgbyidCategories($id_ebay_profile, $id_category)
    {
        return Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ebay_category_business_config WHERE id_ebay_profile = "' . $id_ebay_profile . '" AND id_category ="' . $id_category . '"');

    }

    public static function addPolicies($data, $id_ebay_profile)
    {
        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'ebay_business_policies (`type`, `name`, `id_bussines_Policie`, `id_ebay_profile`) VALUES ("' . $data['ProfileType'] . '","' . $data['ProfileName'] . '","' . $data['ProfileID'] . '",' . $id_ebay_profile . ')');
        return true;
    }

    public function resetBussinesPolicies($id_ebay_profile)
    {
        if (Db::getInstance()->execute('DELETE FROM' . _DB_PREFIX_ . 'ebay_category_business_config WHERE id_ebay_profile=' . $id_ebay_profile)) {
            EbayRequest::importBusinessPolicies();
        } else {
            return false;
        }

    }
}
