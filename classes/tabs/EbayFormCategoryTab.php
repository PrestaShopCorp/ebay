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

class EbayFormCategoryTab extends EbayTab
{

	function getContent()
	{
		$is_one_dot_five = version_compare(_PS_VERSION_, '1.5', '>');

		// Load prestashop ebay's configuration
		$configs = Configuration::getMultiple(array('EBAY_CATEGORY_LOADED_'.$this->ebay_profile->ebay_site_id, 'EBAY_SECURITY_TOKEN'));

		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
			return $this->display('error_paypal_email.tpl', array('error_form_category', 'true'));

		// Load categories only if necessary
		if (EbayCategoryConfiguration::getTotalCategoryConfigurations($this->ebay_profile->id) && Tools::getValue('section') != 'category')
		{
			$template_vars = array(
				'isOneDotFive' => $is_one_dot_five,
				'controller' => Tools::getValue('controller'),
				'tab' => Tools::getValue('tab'),
				'configure' => Tools::getValue('configure'),
				'token' => Tools::getValue('token'),
				'tab_module' => Tools::getValue('tab_module'),
				'module_name' => Tools::getValue('module_name'),
				'form_categories' => EbaySynchronizer::getNbSynchronizableEbayCategorie($this->ebay_profile->id)
			);

			return $this->display('pre_form_categories.tpl', $template_vars);
		}

		// Display eBay Categories
		$ebay_site_id = $this->ebay_profile->ebay_site_id;
		if (!isset($configs['EBAY_CATEGORY_LOADED_'.$ebay_site_id]) || !$configs['EBAY_CATEGORY_LOADED_'.$ebay_site_id] || !EbayCategory::areCategoryLoaded($ebay_site_id))
		{
			$ebay_request = new EbayRequest();
			EbayCategory::insertCategories($ebay_site_id, $ebay_request->getCategories(), $ebay_request->getCategoriesSkuCompliancy());
			Configuration::updateValue('EBAY_CATEGORY_LOADED_'.$ebay_site_id, 1);
		}
		
		// Smarty
		$template_vars = array(
			'alerts' => $this->_getAlertCategories(),
			'tabHelp' => '&id_tab=7',
			'id_lang' => $this->context->cookie->id_lang,
			'id_ebay_profile' => $this->ebay_profile->id,
			'_path' => $this->path,
			'configs' => $configs,
			'_module_dir_' => _MODULE_DIR_,
			'isOneDotFive' => $is_one_dot_five,
			'request_uri' => $_SERVER['REQUEST_URI'],
			'controller' => Tools::getValue('controller'),
			'tab' => Tools::getValue('tab'),
			'configure' => Tools::getValue('configure'),
			'token' => Tools::getValue('token'),
			'tab_module' => Tools::getValue('tab_module'),
			'module_name' => Tools::getValue('module_name'),
			'date' => pSQL(date('Ymdhis')),
			'form_categories' => EbaySynchronizer::getNbSynchronizableEbayCategorie($this->ebay_profile->id),
			'nb_categorie' => count(Category::getCategories($this->context->cookie->id_lang, true, false))
		);

		return $this->display('form_categories.tpl', $template_vars);
	}
	
	public function postProcess()
	{
		
		// Insert and update categories
		if (($percents = Tools::getValue('percent')) && ($ebay_categories = Tools::getValue('category')))
		{
				
			$id_ebay_profile = Tools::getValue('profile') ? Tools::getValue('profile') : $this->ebay_profile->id; 
			foreach ($percents as $id_category => $percent)
			{
				$data = array();
				$date = date('Y-m-d H:i:s');
				if ($percent['value'] != '') {
					$percent_sign_type = explode(':', $percent['sign']);
					$percentValue = ($percent_sign_type[0] == '-' ? $percent_sign_type[0] : '') . $percent['value'] . ($percent['type'] == 'percent' ? '%' : '');
				} 
				else 
					$percentValue = null;
				
				if (isset($ebay_categories[$id_category]))
					$data = array(
						'id_ebay_profile' => (int)$id_ebay_profile,
						'id_country' => 8,
						'id_ebay_category' => (int)$ebay_categories[$id_category],
						'id_category' => (int)$id_category,
						'percent' => pSQL($percentValue),
						'date_upd' => pSQL($date),
						'sync' => 0
					);
					

				if (EbayCategoryConfiguration::getIdByCategoryId($id_ebay_profile, $id_category))
				{
					if ($data)
						EbayCategoryConfiguration::updateByIdProfileAndIdCategory($id_ebay_profile, $id_category, $data);
					else
						EbayCategoryConfiguration::deleteByIdCategory($id_ebay_profile, $id_category);
				}
				elseif ($data)
				{
					$data['date_add'] = $date;
					EbayCategoryConfiguration::add($data);
				}
			}

			// make sur the ItemSpecifics and Condition data are refresh when we load the dedicated config screen the next time
			$this->ebay_profile->deleteConfigurationByName('EBAY_SPECIFICS_LAST_UPDATE');
		}


		// update extra_images for all products
		if (($all_nb_extra_images = Tools::getValue('all-extra-images-value', -1)) != -1)
		{
			$product_ids = EbayCategoryConfiguration::getAllProductIds($this->ebay_profile->id);

			foreach ($product_ids as $product_id)
				EbayProductConfiguration::insertOrUpdate($product_id, array(
					'extra_images' => $all_nb_extra_images ? $all_nb_extra_images : 0,
					'id_ebay_profile' => $this->ebay_profile->id
				));
		}

		// update products configuration
		if (is_array(Tools::getValue('showed_products')))
		{
			$showed_product_ids = array_keys(Tools::getValue('showed_products'));

			if (Tools::getValue('to_synchronize'))
				$to_synchronize_product_ids = array_keys(Tools::getValue('to_synchronize'));
			else
				$to_synchronize_product_ids = array();

			// TODO remove extra_images
			$extra_images = Tools::getValue('extra_images');

			foreach ($showed_product_ids as $product_id)
				EbayProductConfiguration::insertOrUpdate($product_id, array(
					'id_ebay_profile' => $this->ebay_profile->id,
					'blacklisted' => in_array($product_id, $to_synchronize_product_ids) ? 0 : 1,
					'extra_images' => 0,
				));
		}

		if (Tools::getValue('ajax'))
			die('{"valid" : true}');

		return $this->ebay->displayConfirmation($this->ebay->l('Settings updated'));        
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
				$alert = $this->ebay->l('You have chosen eBay categories : ').' "'.$var.'"" '.$this->ebay->l(' which do not support multivariation products. Each variation of a product will generate a new product in eBay');
		}

		return $alert;
	}    
	
}