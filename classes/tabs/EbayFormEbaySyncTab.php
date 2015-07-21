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

class EbayFormEbaySyncTab extends EbayTab
{

	function getContent()
	{
		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
			return '<p class="error"><b>'.$this->ebay->l('Please configure the \'General settings\' tab before using this tab').'</b></p><br /><script type="text/javascript">$("#menuTab5").addClass("wrong")</script>';
		
		if (!EbayCategoryConfiguration::getTotalCategoryConfigurations($this->ebay_profile->id))
			return '<p class="error"><b>'.$this->ebay->l('Please configure the \'Category settings\' tab before using this tab').'</b></p><br /><script type="text/javascript">$("#menuTab5").addClass("wrong")</script>';

		if (version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$sql = '
				SELECT COUNT( * ) FROM (
					SELECT COUNT(p.id_product) AS nb
						FROM  `'._DB_PREFIX_.'product` AS p
						INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s 
						ON p.id_product = s.id_product';
			if (version_compare(_PS_VERSION_, '1.5', '>'))
				$sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps 
						ON p.id_product = ps.id_product 
						AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
			$sql .= ' WHERE s.`quantity` > 0
						AND  p.`active` = 1
						AND  p.`id_category_default`
						IN (
							SELECT  `id_category`
							FROM  `'._DB_PREFIX_.'ebay_category_configuration`
							WHERE  `id_ebay_category` > 0
							AND `id_ebay_profile` = '.(int)$this->ebay_profile->id.'
						)
						'.$this->ebay->addSqlRestrictionOnLang('s').'
						AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($this->ebay_profile->id).')
						GROUP BY p.id_product
				)TableReponse';
			$nb_products_mode_a = Db::getInstance()->getValue($sql);
			
			$sql = '
				SELECT COUNT( * ) FROM (
					SELECT COUNT( p.id_product ) AS nb
						FROM  `'._DB_PREFIX_.'product` AS p
						INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s 
						ON p.id_product = s.id_product';
			if (version_compare(_PS_VERSION_, '1.5', '>'))
				$sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
						ON p.id_product = ps.id_product 
						AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
			$sql .= ' WHERE s.`quantity` > 0
						AND  p.`active` = 1
						AND  p.`id_category_default`
						IN (
							SELECT  `id_category`
							FROM  `'._DB_PREFIX_.'ebay_category_configuration`
							WHERE  `id_ebay_category` > 0 
							AND `sync` = 1
							AND `id_ebay_profile` = '.(int)$this->ebay_profile->id.'
						)'.$this->ebay->addSqlRestrictionOnLang('s').'
						AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($this->ebay_profile->id).')
						GROUP BY p.id_product
				)TableReponse';
			$nb_products_mode_b = Db::getInstance()->getValue($sql);
		}
		else
		{
			$sql = '
				SELECT COUNT(`id_product`) as nb
				FROM `'._DB_PREFIX_.'product` AS p';
			if (version_compare(_PS_VERSION_, '1.5', '>'))
				$sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
				ON p.id_product = ps.id_product 
				AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
			$sql .= ' WHERE p.`quantity` > 0
				AND p.`active` = 1
				AND p.`id_category_default` IN (
					SELECT `id_category`
					FROM `'._DB_PREFIX_.'ebay_category_configuration`
					WHERE `id_ebay_category` > 0
					AND `id_ebay_profile` = '.(int)$this->ebay_profile->id.')
				AND p.`id_product` NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($this->ebay_profile->id).')';
			$nb_products_mode_a = Db::getInstance()->getValue($sql);

			$sql = 'SELECT COUNT(`id_product`) as nb
				FROM `'._DB_PREFIX_.'product` AS p';
			if (version_compare(_PS_VERSION_, '1.5', '>'))
				$sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
				ON p.id_product = ps.id_product 
				AND ps.id_shop = '.(int)$this->ebay_profile->id_shop;
			$sql .= ' WHERE p.`quantity` > 0
				AND p.`active` = 1
				AND p.`id_category_default` IN (
					SELECT `id_category`
					FROM `'._DB_PREFIX_.'ebay_category_configuration`
					WHERE `id_ebay_category` > 0
					AND `sync` = 1
					AND `id_ebay_profile` = '.(int)$this->ebay_profile->id.')
				AND p.`id_product` NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($this->ebay_profile->id).')';
			$nb_products_mode_b = Db::getInstance()->getValue($sql);
		}

		$nb_products = ($this->ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') == 'B' ? $nb_products_mode_b : $nb_products_mode_a);
		$prod_nb = ($nb_products < 2 ? $this->ebay->l('product') : $this->ebay->l('products'));

		// Display Form
		$url_vars = array(
			'id_tab' => '5',
			'section' => 'sync'
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::getValue('controller');
		else
			$url_vars['tab'] = Tools::getValue('tab');

		$action_url = $this->_getUrl($url_vars);

		// Loading categories
		$category_config_list = array();

		foreach (EbayCategoryConfiguration::getEbayCategoryConfigurations($this->ebay_profile->id) as $c)
			$category_config_list[$c['id_category']] = $c;

		$category_list = $this->ebay->getChildCategories(Category::getCategories($this->context->language->id), 0);
		$categories = array();

		if ($category_list)
		{
			$alt_row = false;
			foreach ($category_list as $category)
			{
				if (isset($category_config_list[$category['id_category']]['id_ebay_category'])
					&& $category_config_list[$category['id_category']]['id_ebay_category'] > 0)
				{
					$categories[] = array(
						'row_class' => $alt_row ? 'alt_row' : '',
						'value' => $category['id_category'],
						'checked' => ($category_config_list[$category['id_category']]['sync'] == 1 ? 'checked="checked"' : ''),
						'name' => $category['name']
					);

					$alt_row = !$alt_row;
				}
			}
		}

		$nb_products_sync_url = _MODULE_DIR_.'ebay/ajax/getNbProductsSync.php?token='.Configuration::get('EBAY_SECURITY_TOKEN').'&time='.pSQL(date('Ymdhis')).'&profile='.$this->ebay_profile->id;
		$sync_products_url = _MODULE_DIR_.'ebay/ajax/eBaySyncProduct.php?token='.Configuration::get('EBAY_SECURITY_TOKEN').'&option=\'+option+\'&profile='.$this->ebay_profile->id.'&time='.pSQL(date('Ymdhis'));

		$smarty_vars = array(
			'category_alerts' => $this->_getAlertCategories(),
			'path' => $this->path,
			'nb_products' => $nb_products ? $nb_products : 0,
			'nb_products_mode_a' => $nb_products_mode_a ? $nb_products_mode_a : 0,
			'nb_products_mode_b' => $nb_products_mode_b ? $nb_products_mode_b : 0,
			'nb_products_sync_url' => $nb_products_sync_url,
			'sync_products_url' => $sync_products_url,
			'action_url' => $action_url,
			'ebay_sync_option_resync' => $this->ebay_profile->getConfiguration('EBAY_SYNC_OPTION_RESYNC'),
			'categories' => $categories,
			'sync_1' => (Tools::getValue('section') == 'sync' && Tools::getValue('ebay_sync_mode') == "1" && Tools::getValue('btnSubmitSyncAndPublish')),
			'sync_2' => (Tools::getValue('section') == 'sync' && Tools::getValue('ebay_sync_mode') == "2" && Tools::getValue('btnSubmitSyncAndPublish')),
			'is_sync_mode_b' => ($this->ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') == 'B'),
			'ebay_sync_mode' => (int)($this->ebay_profile->getConfiguration('EBAY_SYNC_MODE') ? $this->ebay_profile->getConfiguration('EBAY_SYNC_MODE') : 2),
			'prod_str' => $nb_products >= 2 ? $this->ebay->l('products') : $this->ebay->l('product')
		);

		return $this->display('formEbaySync.tpl', $smarty_vars);
		
	}
	
	function postProcess()
	{
		
		// Update Sync Option
		if ($this->ebay_profile == null)
			return;
		
		$this->ebay_profile->setConfiguration('EBAY_SYNC_OPTION_RESYNC', (Tools::getValue('ebay_sync_option_resync') == 1 ? 1 : 0));

		// Empty error result
		$this->ebay_profile->setConfiguration('EBAY_SYNC_LAST_PRODUCT', 0);

		if (file_exists(dirname(__FILE__).'/../../log/syncError.php'))
			@unlink(dirname(__FILE__).'/../../log/syncError.php');

		$this->ebay_profile->setConfiguration('EBAY_SYNC_MODE', Tools::getValue('ebay_sync_mode'));

		if (Tools::getValue('ebay_sync_products_mode') == 'A')
			$this->ebay_profile->setConfiguration('EBAY_SYNC_PRODUCTS_MODE', 'A');
		else
		{
			$this->ebay_profile->setConfiguration('EBAY_SYNC_PRODUCTS_MODE', 'B');

			// Select the sync Categories and Retrieve product list for eBay (which have matched and sync categories)
			if (Tools::getValue('category'))
			{
				EbayCategoryConfiguration::updateByIdProfile($this->ebay_profile->id, array('sync' => 0));
				foreach (Tools::getValue('category') as $id_category)
					EbayCategoryConfiguration::updateByIdProfileAndIdCategory((int)$this->ebay_profile->id, $id_category, array('id_ebay_profile' => $this->ebay_profile->id, 'sync' => 1));
			}
		}
		
	}
	
	/*
	 *
	 * Get alert to see if some multi variation product on PrestaShop were added to a non multi sku categorie on ebay
	 *
	 */
	private function _getAlertCategories()
	{
		$alert = '';
		
		$cat_with_problem = EbayCategoryConfiguration::getMultiVarToNonMultiSku($this->ebay_profile, $this->context);

		$var = implode(', ', $cat_with_problem);

		if (count($cat_with_problem) > 0)
		{
			if (count($cat_with_problem) == 1)
				$alert = $this->ebay->l('You have chosen eBay category : ').' "'.$var.'" '.$this->ebay->l(' which does not support multivariation products. Each variation of a product will generate a new product in eBay');
			else
				$alert = $this->ebay->l('You have chosen eBay categories : ').' "'.$var.'" '.$this->ebay->l(' which do not support multivariation products. Each variation of a product will generate a new product in eBay');
		}

		return $alert;
	}    
	
}

