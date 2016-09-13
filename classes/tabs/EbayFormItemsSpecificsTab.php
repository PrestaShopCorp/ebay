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

class EbayFormItemsSpecificsTab extends EbayTab
{

    public function getContent()
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
            'isOneDotFive' => $is_one_dot_five,
            'help_Cat_upd' => array(
                'lang'           => $this->context->country->iso_code,
                'module_version' => $this->ebay->version,
                'ps_version'     => _PS_VERSION_,
                'error_code'     => 'HELP-CATEGORY-UPDATE',
            ),
        );

        return $this->display('formItemsSpecifics.tpl', $template_vars);
    }

    public function postProcess()
    {
        // Save specifics
        if (Tools::getValue('specific')) {
            foreach (Tools::getValue('specific') as $specific_id => $data) {
                if ($data) {
                    list($data_type, $value) = explode('-', $data);
                } else {
                    $data_type = null;
                }

                $field_names = EbayCategorySpecific::getPrefixToFieldNames();
                $data = array_combine(array_values($field_names), array(null, null, null, null, null, null, null));

                if ($data_type) {
                    $data[$field_names[$data_type]] = pSQL($value);
                }

                if (version_compare(_PS_VERSION_, '1.5', '>')) {
                    Db::getInstance()->update('ebay_category_specific', $data, 'id_ebay_category_specific = '.(int) $specific_id);
                } else {
                    Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_specific', $data, 'UPDATE', 'id_ebay_category_specific = '.(int) $specific_id);
                }

            }
        }

        // save conditions
        foreach (Tools::getValue('condition') as $category_id => $condition) {
            foreach ($condition as $type => $condition_ref) {
                EbayCategoryConditionConfiguration::replace(array('id_ebay_profile' => $this->ebay_profile->id, 'id_condition_ref' => $condition_ref, 'id_category_ref' => $category_id, 'condition_type' => $type));
            }
        }

        return $this->ebay->displayConfirmation($this->ebay->l('Settings updated'));
    }

    /*
     * Method to call the translation tool properly on every version to translate the PrestaShop conditions
     *
     */
    private function _translatePSConditions($ps_conditions)
    {
        foreach ($ps_conditions as &$condition) {
            switch ($condition) {
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
