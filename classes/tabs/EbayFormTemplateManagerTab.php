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

class EbayFormTemplateManagerTab extends EbayTab
{

	function getContent()
	{
		// Check if the module is configured
		if (!$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL'))
			return '<p class="error"><b>'.$this->ebay->l('Please configure the \'General settings\' tab before using this tab').'</b></p><br /><script type="text/javascript">$("#menuTab4").addClass("wrong")</script>';

		$iso = $this->context->language->iso_code;
		$iso_tiny_mce = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');

		// Display Form
		$url_vars = array(
			'id_tab' => '4',
			'section' => 'template'
		);

		if (version_compare(_PS_VERSION_, '1.5', '>'))
			$url_vars['controller'] = Tools::getValue('controller');
		else
			$url_vars['tab'] = Tools::getValue('tab');

		$action_url = $this->_getUrl($url_vars);
		
		if (Tools::getValue('reset_template'))
			$ebay_product_template = EbayProductTemplate::getContent($this->ebay, $this->smarty);
		else
			$ebay_product_template = Tools::getValue('ebay_product_template', $this->ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE'));
		
		$ebay_product_template_title = $this->ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE');

		$smarty_vars = array(
			'action_url' => $action_url,
			'ebay_product_template' => $ebay_product_template,
			'ebay_product_template_title' => $ebay_product_template_title,
			'features_product' => Feature::getFeatures($this->context->language->id),
			'ad' => dirname($_SERVER['PHP_SELF']),
			'base_uri' => __PS_BASE_URI__,
			'is_one_dot_three' => (Tools::substr(_PS_VERSION_, 0, 3) == '1.3'),
			'is_one_dot_five' => version_compare(_PS_VERSION_, '1.5', '>'),
			'theme_css_dir' => _THEME_CSS_DIR_,
			
		);

		if (Tools::substr(_PS_VERSION_, 0, 3) == '1.3')
		{
			$smarty_vars['theme_name'] = _THEME_NAME_;
			$smarty_vars['language'] = file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en';
		}
		elseif (version_compare(_PS_VERSION_, '1.5', '>'))
			$smarty_vars['iso'] = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		else
		{
			$smarty_vars['iso_type_mce'] = $iso_tiny_mce;
			$smarty_vars['ps_js_dir'] = _PS_JS_DIR_;
		}
		
		return $this->display('formTemplateManager.tpl', $smarty_vars);
	}
	
	
	function postProcess()
	{
		$ebay_product_template = Tools::getValue('ebay_product_template');
		$ebay_product_template_title = Tools::getValue('ebay_product_template_title');
		if (empty($ebay_product_template_title))
			$ebay_product_template_title = '{TITLE}';

		// work around for the tinyMCE bug deleting the css line
		$css_line = '<link rel="stylesheet" type="text/css" href="'.$this->_getModuleUrl().'views/css/ebay.css" />';
		$ebay_product_template = $css_line.TotFormat::formatDescription($ebay_product_template);

			// Saving new configurations
		if ($this->ebay_profile->setConfiguration('EBAY_PRODUCT_TEMPLATE', $ebay_product_template, true) && $this->ebay_profile->setConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE', $ebay_product_template_title))
			return $this->ebay->displayConfirmation($this->ebay->l('Settings updated'));
		else
			return $this->ebay->displayError($this->ebay->l('Settings failed'));    
	
	}
	  
}