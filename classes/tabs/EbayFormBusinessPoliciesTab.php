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

class EbayFormBusinessPoliciesTab extends EbayTab
{
    public function getContent()
    {
        $url_vars = array(
            'id_tab' => '77',
            'section' => 'parameters'
        );
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $url_vars['controller'] = Tools::getValue('controller');
        } else {
            $url_vars['tab'] = Tools::getValue('tab');
        }

        $url = $this->_getUrl($url_vars);
        $is_one_dot_five = version_compare(_PS_VERSION_, '1.5', '>');

        $ebay_country = EbayCountrySpec::getInstanceByKey($this->ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'));
        $template_vars = array(
            'url_categories' => $url,
            'PAYEMENTS' =>EbayBussinesPolicies::getPoliciesbyType('PAYMENT', $this->ebay_profile->id),
            'RETURN_POLICY' => EbayBussinesPolicies::getPoliciesbyType('RETURN_POLICY', $this->ebay_profile->id),
            'ebay_token' => Configuration::get('EBAY_SECURITY_TOKEN'),
            'isOneDotFive' => $is_one_dot_five,
            'id_tab' => Tools::getValue('id_tab'),
            'controller' => Tools::getValue('controller'),
            'tab' => Tools::getValue('tab'),
            'configure' => Tools::getValue('configure'),
            'tab_module' => Tools::getValue('tab_module'),
            'module_name' => Tools::getValue('module_name'),
            'token' => Tools::getValue('token'),
            'activation_bussines' =>  EbayConfiguration::get($this->ebay_profile->id, 'EBAY_BUSINESS_POLICIES'),
            'ebay_categories' => EbayCategoryConfiguration::getEbayCategories($this->ebay_profile->id),
            'id_ebay_profile' => $this->ebay_profile->id,
            'url_help' => $ebay_country->getHelpUrlBusinesss(),
            'profile_name' => $this->ebay_profile->ebay_user_identifier,
        );
        
       
       

        return $this->display('form_business_policies.tpl', $template_vars);
        
    }

    public function postProcess()
    {
        // Save
        if (Tools::getValue('refresh_bp') == 1) {
            $request = new EbayRequest($this->ebay_profile->id);
            $request->importBusinessPolicies();

        }
        if (Tools::getValue('payement') || Tools::getValue('return_policies')) {
            $var = array();
            $var[] = array(
                'type' => 'EBAY_PAYMENT_POLICY',
                'id_bussines_Policie' => Tools::getValue('payement'),
            );
            $var[] = array(
                'type' => 'EBAY_RETURN_POLICY',
                'id_bussines_Policie' => Tools::getValue('return_policies'),
            );

            foreach ($var as $data) {
                EbayBussinesPolicies::setBussinesPolicies($this->ebay_profile->id, $data);
            }


            $ebay_categories = EbayCategoryConfiguration::getEbayCategories($this->ebay_profile->id);
            $payment = Tools::getValue('payement');
            $return = Tools::getValue('return_policies');
            foreach ($ebay_categories as $category) {
                $data = array(
                    'id_ebay_profile' => $this->ebay_profile->id,
                    'id_category' => $category['id'],
                    'id_return' => $return[$category['id']],
                    'id_payment' => $payment[$category['id']],
                );
                EbayBussinesPolicies::deletePoliciesConfgbyidCategories($this->ebay_profile->id, $category['id']);

                if (version_compare(_PS_VERSION_, '1.5', '>')) {
                    Db::getInstance()->insert('ebay_category_business_config', $data);
                } else {
                    Db::getInstance()->autoExecute(_DB_PREFIX_ . 'ebay_category_specific', $data, 'INSERT');
                }

            }

            EbayConfiguration::set($this->ebay_profile->id, 'EBAY_BUSINESS_POLICIES_CONFIG', 1);

        }



        return $this->ebay->displayConfirmation($this->ebay->l('Settings updated'));
    }
}
