<?php

/*
 * 2007-2014 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayFormAdvancedParametersTab extends EbayTab
{

    function getContent()
    {
        
        // make url
		$url_vars = array(
			'id_tab' => '1',
			'section' => 'parameters'
		);
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::getValue('controller');
		else
			$url_vars['tab'] = Tools::getValue('tab');
		$url = $this->_getUrl($url_vars);

        
        $smarty_vars = array(
            'url' => $url,
            
            // pictures
			'sizes' => ImageType::getImagesTypes('products'),
			'sizedefault' => $this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_DEFAULT'),
			'sizebig' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG'),
			'sizesmall' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_SMALL'),
            'picture_per_listing' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING'),
            
            // logs
			'activate_logs' => Configuration::get('EBAY_ACTIVATE_LOGS'),
			'is_writable' => is_writable(_PS_MODULE_DIR_.'ebay/log/request.txt'),
			'log_file_exists' => file_exists(_PS_MODULE_DIR_.'ebay/log/request.txt'),
            
            // CRON sync
			'sync_products_by_cron' => Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON'),
			'sync_products_by_cron_url' => $this->_getModuleUrl().'synchronizeProducts_CRON.php',
			'sync_products_by_cron_path' => dirname(__FILE__).'/synchronizeProducts_CRON.php',
			'sync_orders_by_cron' => Configuration::get('EBAY_SYNC_ORDERS_BY_CRON'),
			'sync_orders_by_cron_url' => $this->_getModuleUrl().'synchronizeOrders_CRON.php',
			'sync_orders_by_cron_path' => dirname(__FILE__).'/synchronizeOrders_CRON.php',
            
            // send stats to eBay
            'stats' => Configuration::get('EBAY_SEND_STATS')
            
        );     
        
		return $this->display('formAdvancedParameters.tpl', $smarty_vars);
    }
    
    function postProcess()
    {

    }
    
}