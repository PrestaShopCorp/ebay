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

class EbayOrderHistoryTab extends EbayTab
{

    public function getContent()
    {
        // Check if the module is configured
        if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL')) {
            return '<p><b>'.$this->ebay->l('Please configure the \'General settings\' tab before using this tab', 'ebayorderhistorytab').'</b></p><br />';
        }

        $dateLastImport = '-';

        if (file_exists(dirname(__FILE__).'/../../log/orders.php')) {
            include dirname(__FILE__).'/../../log/orders.php';
        }

        $template_vars = array(
            'date_last_import' => $dateLastImport,
            'orders' => isset($orders) ? $orders : array(),
            'help' => array(
                'lang' => $this->context->country->iso_code,
                'module_version' => $this->ebay->version,
                'ps_version' => _PS_VERSION_),
        );

        return $this->display('ordersHistory.tpl', $template_vars);
    }
}
