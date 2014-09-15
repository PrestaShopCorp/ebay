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

class EbayFormItemsSpecificsTab extends EbayTab
{

    function getContent()
    {
		$is_one_dot_five = version_compare(_PS_VERSION_, '1.5', '>');

		// Smarty
		$template_vars = array(
			'id_tab' => Tools::getValue('id_tab'),
			'controller' => Tools::getValue('controller'),
			'tab' => Tools::getValue('tab'),
			'configure' => Tools::getValue('configure'),
			'tab_module' => Tools::getValue('tab_module'),
			'module_name' => Tools::getValue('module_name'),
			'token' => Tools::getValue('token'),
			'ebay_token' => Configuration::get('EBAY_SECURITY_TOKEN'),			
			'_module_dir_' => _MODULE_DIR_,
			'ebay_categories' => EbayCategoryConfiguration::getEbayCategories($this->ebay_profile->id),
			'id_lang' => $this->context->cookie->id_lang,
			'id_ebay_profile' => $this->ebay_profile->id,
			'_path' => $this->path,
			'possible_attributes' => AttributeGroup::getAttributesGroups($this->context->cookie->id_lang),
			'possible_features' => Feature::getFeatures($this->context->cookie->id_lang, true),
			'date' => pSQL(date('Ymdhis')),
			'conditions' => $this->_translatePSConditions(EbayCategoryConditionConfiguration::getPSConditions()),
			'form_items_specifics' => EbaySynchronizer::getNbSynchronizableEbayCategoryCondition(),
			'form_items_specifics_mixed' => EbaySynchronizer::getNbSynchronizableEbayCategoryConditionMixed(),
			'isOneDotFive' => $is_one_dot_five
		);

		return $this->display('formItemsSpecifics.tpl', $template_vars);
    }
    
	/*
	 * Method to call the translation tool properly on every version to translate the PrestaShop conditions
	 *
	 */
	private function _translatePSConditions($ps_conditions)
	{
		foreach ($ps_conditions as &$condition)
		{
			switch ($condition)
			{
				case 'new':
					$condition = $this->ebay->l('new');
					break;
				case 'used':
					$condition = $this->ebay->l('used');
					break;
				case 'refurbished':
					$condition = $this->ebay->l('refurbished');
					break;
			}
		}

		return $ps_conditions;
	}

    
        
}