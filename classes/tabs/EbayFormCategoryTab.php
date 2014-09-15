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

class EbayFormCategoryTab extends EbayTab
{

    function getContent()
    {
		$is_one_dot_five = version_compare(_PS_VERSION_, '1.5', '>');

		// Load prestashop ebay's configuration
		$configs = Configuration::getMultiple(array('EBAY_CATEGORY_LOADED_'.$this->ebay_profile->ebay_site_id, 'EBAY_SECURITY_TOKEN'));

		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
		{
			$this->smarty->assign('error_form_category', 'true');
			return $this->display('error_paypal_email.tpl');
		}

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
			$this->setConfiguration('EBAY_CATEGORY_LOADED_'.$ebay_site_id, 1);
			$this->setConfiguration('EBAY_CATEGORY_LOADED_'.$ebay_site_id.'_DATE', date('Y-m-d H:i:s')); // THIS LINE MIGHT BE REMOVED
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
    
	/*
     *
     * Get alert to see if some multi variation product on PrestaShop were added to a non multi sku categorie on ebay
     *
     */
	private function _getAlertCategories()
	{
		$alert = '';
		$cat_with_problem = array();

		$sql_get_cat_non_multi_sku = 'SELECT * FROM '._DB_PREFIX_.'ebay_category_configuration AS ecc
			INNER JOIN '._DB_PREFIX_.'ebay_category AS ec ON ecc.id_ebay_category = ec.id_ebay_category
			WHERE ecc.id_ebay_profile = '.(int)$this->ebay_profile->id;

		foreach (Db::getInstance()->ExecuteS($sql_get_cat_non_multi_sku) as $cat)
		{
			if ($cat['is_multi_sku'] != 1 && EbayCategory::getInheritedIsMultiSku($cat['id_category_ref'], $this->ebay_profile->ebay_site_id) != 1)
			{
				$catProblem = 0;
				$category = new Category($cat['id_category']);
                $ebay_country = EbayCountrySpec::getInstanceByKey($this->ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'));
				$products = $category->getProductsWs($ebay_country->getIdLang(), 0, 300);

				foreach ($products as $product_ar)
				{
					$product = new Product($product_ar['id']);
					$combinations = version_compare(_PS_VERSION_, '1.5', '>') ? $product->getAttributeCombinations($this->context->cookie->id_lang) : $product->getAttributeCombinaisons($this->context->cookie->id_lang);

					if (count($combinations) > 0 && !$catProblem)
					{
						$cat_with_problem[] = $cat['name'];
						$catProblem = 1;
					}
				}
			}
		}

		$var = implode(', ', $cat_with_problem);

		if (count($cat_with_problem) > 0)
		{
			if (count($cat_with_problem == 1)) // RAPH: pb here in the test. Potential typo
				$alert = '<b>'.$this->ebay->l('You have chosen eBay category : ').' "'.$var.'" '.$this->ebay->l(' which does not support multivariation products. Each variation of a product will generate a new product in eBay').'</b>';
			else
				$alert = '<b>'.$this->ebay->l('You have chosen eBay categories : ').' "'.$var.'"" '.$this->ebay->l(' which do not support multivariation products. Each variation of a product will generate a new product in eBay').'</b>';
		}

		return $alert;
	}    
    
}