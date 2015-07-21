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

class EbayFormAdvancedParametersTab extends EbayTab
{

	function getContent()
	{
		
		// make url
		$url_vars = array(
			'id_tab' => '13',
			'section' => 'advanced_parameters'
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
			'api_logs' => Configuration::get('EBAY_API_LOGS'),
			'activate_logs' => Configuration::get('EBAY_ACTIVATE_LOGS'),
			'is_writable' => is_writable(_PS_MODULE_DIR_.'ebay/log/request.txt'),
			'log_file_exists' => file_exists(_PS_MODULE_DIR_.'ebay/log/request.txt'),
			'logs_conservation_duration' => Configuration::get('EBAY_LOGS_DAYS'),            
			
			// CRON sync
			'sync_products_by_cron' => Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON'),
			'sync_products_by_cron_url' => $this->_getModuleUrl().'synchronizeProducts_CRON.php',
			'sync_products_by_cron_path' => $this->_getModuleUrl().'synchronizeProducts_CRON.php',
			'sync_orders_by_cron' => Configuration::get('EBAY_SYNC_ORDERS_BY_CRON'),
			'sync_orders_by_cron_url' => $this->_getModuleUrl().'synchronizeOrders_CRON.php',
			'sync_orders_by_cron_path' => $this->_getModuleUrl().'synchronizeOrders_CRON.php',
			
			// number of days to collect the oders for backward
			'orders_days_backward' => Configuration::get('EBAY_ORDERS_DAYS_BACKWARD'),
			
			// send stats to eBay
			'stats' => Configuration::get('EBAY_SEND_STATS')
			
		);     
		
		return $this->display('formAdvancedParameters.tpl', $smarty_vars);
	}
	
	function postProcess()
	{
		// Reset Image if they have modification on the size
		if (   $this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_DEFAULT') != (int)Tools::getValue('sizedefault')
			|| $this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_SMALL') != (int)Tools::getValue('sizesmall')
			|| $this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG') != (int)Tools::getValue('sizebig')
			)
			EbayProductImage::removeAllProductImage();
		
		// Saving new configurations
		$picture_per_listing = (int)Tools::getValue('picture_per_listing');
		if ($picture_per_listing < 0)
			$picture_per_listing = 0;
		

		if ($this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_DEFAULT', (int)Tools::getValue('sizedefault'))
			&& $this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_SMALL', (int)Tools::getValue('sizesmall'))
			&& $this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_BIG', (int)Tools::getValue('sizebig'))
			&& $this->ebay_profile->setConfiguration('EBAY_PICTURE_PER_LISTING', $picture_per_listing)
			&& $this->ebay->setConfiguration('EBAY_API_LOGS', Tools::getValue('api_logs') ? 1 : 0)
			&& $this->ebay->setConfiguration('EBAY_ACTIVATE_LOGS', Tools::getValue('activate_logs') ? 1 : 0)
			&& Configuration::updateValue('EBAY_SYNC_PRODUCTS_BY_CRON', ('cron' === Tools::getValue('sync_products_mode')))
			&& Configuration::updateValue('EBAY_SYNC_ORDERS_BY_CRON', ('cron' === Tools::getValue('sync_orders_mode')))
			&& Configuration::updateValue('EBAY_SEND_STATS', Tools::getValue('stats') ? 1 : 0, false, 0, 0)
			&& Configuration::updateValue('EBAY_ORDERS_DAYS_BACKWARD', (int)Tools::getValue('orders_days_backward'), false, 0, 0)
			&& Configuration::updateValue('EBAY_LOGS_DAYS', (int)Tools::getValue('logs_conservation_duration'), false, 0, 0)
				
		) {
			if(Tools::getValue('activate_logs') == 0)
				if(file_exists(dirname(__FILE__).'/../../log/request.txt'))
					unlink(dirname(__FILE__).'/../../log/request.txt');
			return $this->ebay->displayConfirmation($this->ebay->l('Settings updated'));                        
		} else
			return $this->ebay->displayError($this->ebay->l('Settings failed'));            
	}
	
}