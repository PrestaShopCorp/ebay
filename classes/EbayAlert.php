<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayAlert
{
    private $ebay_profile;
    private $ebay;
    private $errors = array();
    private $warnings = array();
    private $infos = array();

    private $alerts;

    public function __construct(Ebay $obj)
    {
        $this->ebay = $obj;
        $this->ebay_profile = $obj->ebay_profile;
    }

    public function getAlerts()
    {
        $this->reset();
        $this->checkOrders();
        $this->checkUrlDomain();
        $this->checkCronTask();

        $this->build();

        return $this->alerts;
    }
    private function reset()
    {
        $this->errors = array();
        $this->warnings = array();
        $this->infos = array();
    }
    private function build()
    {
        $this->alerts = array_merge($this->errors, $this->warnings, $this->infos);
    }

    public function checkNumberPhoto()
    {
        $context = Context::getContext();
        if ($this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING') > 0) {
            $link = new EbayCountrySpec();
            $link->getPictureUrl();
            return array(
                'type' => 'warning',
                'message' => $this->ebay->l('You will send more than one image. This can have financial consequences. Please verify this link'),
                'link_warn' => $link->getPictureUrl(),
                'kb' => array(
                    'errorcode' => 'PICTURES_NUMBER_ABOVE_ZERO',
                    'lang' => $context->language->iso_code,
                    'module_version' => $this->ebay->version,
                    'prestashop_version' => _PS_VERSION_,
                ),
            );
        }

        if ($this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING') >= 12) {
            return array(
                'type' => 'error',
                'message' => $this->ebay->l('You can\'t send more than 12 pictures per product. Please configure in Advanced Settings'),
                'kb' => array(
                    'errorcode' => 'PICTURES_NUMBER_ABOVE_TWELVE',
                    'lang' => $context->language->iso_code,
                    'module_version' => $this->ebay->version,
                    'prestashop_version' => _PS_VERSION_,
                ),
            );
        }

        return false;
    }

    private function checkOrders()
    {
        $this->checkOrdersCountry();
    }

    private function checkOrdersCountry()
    {
        if ($countries = EbayOrderErrors::getEbayOrdersCountry()) {
            $list = array('country' => '', 'order' => '');

            foreach ($countries as $key => $orders) {

                $country = new Country(Country::getByIso($key), (int) Configuration::get('PS_LANG_DEFAULT'));

                if ($country->active) {
                    continue;
                }

                Tools::isEmpty($list['country']) ? ($list['country'] .= $country->name) : ($list['country'] .= ', '.$country->name);

                foreach ($orders as $order) {
                    Tools::isEmpty($list['order']) ? ($list['order'] .= $order['id_order_seller']) : ($list['order'] .= ', '.$order['id_order_seller']);
                }

            }

            $this->errors[] = array(
                'type' => 'error',
                'message' => $this->ebay->l('You must enable the following countries : ').$list['country'].$this->ebay->l('. In order to import this eBay order(s) : ').$list['order'].'.',
            );
        }
    }

    public function sendDailyMail()
    {
        //For the moment we do not send emails
        return true;
        $this->getAlerts();

        if (!$this->formatEmail()) {
            return;
        }

        $template_vars = array('{content}' => $this->formatEmail());

        Mail::Send(
            (int) Configuration::get('PS_LANG_DEFAULT'),
            'ebayAlert',
            Mail::l('Recap of your eBay module', (int) Configuration::get('PS_LANG_DEFAULT')),
            $template_vars,
            (string) Configuration::get('PS_SHOP_EMAIL'),
            null,
            (string) Configuration::get('PS_SHOP_EMAIL'),
            (string) Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            dirname(__FILE__).'/../views/templates/mails/'
        );
        $this->reset();
    }

    public function formatEmail()
    {

        $templates_vars = array();

        $templates_vars['errors'] = (!empty($this->errors)) ? $this->errors : '';
        $templates_vars['warnings'] = (!empty($this->warnings)) ? $this->warnings : '';
        $templates_vars['infos'] = (!empty($this->infos)) ? $this->infos : '';

        if (empty($templates_vars)) {
            return false;
        }

        $smarty = Context::getContext()->smarty;
        $smarty->assign($templates_vars);

        return $this->ebay->display(realpath(dirname(__FILE__).'/../'), '/views/templates/hook/alert_mail.tpl');
    }

    public function checkUrlDomain()
    {
        // check domain
        $shop = $this->ebay_profile instanceof EbayProfile ? new Shop($this->ebay_profile->id_shop) : new Shop();
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $wrong_domain = ($_SERVER['HTTP_HOST'] != $shop->domain && $_SERVER['HTTP_HOST'] != $shop->domain_ssl && Tools::getValue('ajax') == false);
            $domain = isset($shop->domain_ssl) ? $shop->domain_ssl : $shop->domain.DIRECTORY_SEPARATOR.$shop->physical_uri;
        } else {
            $wrong_domain = ($_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN') && $_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN_SSL'));
            $domain = isset($shop->domain_ssl) ? Configuration::get('PS_SHOP_DOMAIN_SSL') : Configuration::get('PS_SHOP_DOMAIN');
        }

        if ($wrong_domain) {
            $url_vars = array();
            // if (version_compare(_PS_VERSION_, '1.5', '>'))
            //     $url_vars['controller'] = 'AdminMeta';
            // else
            //     $url_vars['tab'] = 'AdminMeta';
            // $warning_url = $this->_getUrl($url_vars);

            $context = Context::getContext();
            $protocol = Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http';
            $this->warnings[] = array(
                'type' => 'warning',
                'message' => $this->ebay->l('You are currently connected to the Prestashop Back Office using a different URL than set up, this module will not work properly. Please log in using @link@this url.@/link@'),
                'link_warn' => $protocol.'://'.$domain.DIRECTORY_SEPARATOR.basename(_PS_ADMIN_DIR_).DIRECTORY_SEPARATOR,
                'kb' => array(
                    'errorcode' => 'HELP-ALERT-DEFAULT-PS-URL',
                    'lang' => $context->language->iso_code,
                    'module_version' => $this->ebay->version,
                    'prestashop_version' => _PS_VERSION_,
                ),
            );

        }
    }

    private function _getUrl($extra_vars = array())
    {
        $url_vars = array(
            'configure' => Tools::getValue('configure'),
            'token' => version_compare(_PS_VERSION_, '1.5', '>') ? Tools::getAdminTokenLite($extra_vars['controller']) : Tools::getAdminTokenLite($extra_vars['tab']),
            'tab_module' => Tools::getValue('tab_module'),
            'module_name' => Tools::getValue('module_name'),
        );

        return 'index.php?'.http_build_query(array_merge($url_vars, $extra_vars));
    }

    private function checkCronTask()
    {
        $cron_task = array();

        // PRODUCTS
        if ((int) Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON') == 1) {
            if ($last_sync_datetime = Configuration::get('DATE_LAST_SYNC_PRODUCTS')) {
                $warning_date = strtotime(date('Y-m-d').' - 2 days');

                $date = date('Y-m-d', strtotime($last_sync_datetime));
                $time = date('H:i:s', strtotime($last_sync_datetime));
                $msg = $this->ebay->l('Last product synchronization has been done the ').$date.$this->ebay->l(' at ').$time.$this->ebay->l(' and it tried to synchronize ').Configuration::get('NB_PRODUCTS_LAST');

                if (strtotime($last_sync_datetime) < $warning_date) {
                    $this->warnings[] = array(
                        'type' => 'warning',
                        'message' => $msg,
                    );
                } else {
                    $this->infos[] = array(
                        'type' => 'info',
                        'message' => $msg,
                    );
                }

            } else {
                $this->errors[] = array(
                    'type' => 'error',
                    'message' => $this->ebay->l('The product cron job has never been run.'),
                );
            }

        }

        // ORDERS
        if ((int) Configuration::get('EBAY_SYNC_ORDERS_BY_CRON') == 1) {
            if ($this->ebay_profile->getConfiguration('EBAY_ORDER_LAST_UPDATE') != null) {
                $datetime = new DateTime($this->ebay_profile->getConfiguration('EBAY_ORDER_LAST_UPDATE'));

                $date = date('Y-m-d', strtotime($datetime->format('Y-m-d H:i:s')));
                $time = date('H:i:s', strtotime($datetime->format('Y-m-d H:i:s')));

                $datetime2 = new DateTime();

                $interval = round(($datetime2->format('U') - $datetime->format('U')) / (60 * 60 * 24));

                if ($interval >= 1) {
                    $this->errors[] = array(
                        'type' => 'error',
                        'message' => $this->ebay->l('Last order synchronization has been done the ').$date.$this->ebay->l(' at ').$time,
                    );
                } else {
                    $this->infos[] = array(
                        'type' => 'info',
                        'message' => $this->ebay->l('Last order synchronization has been done the ').$date.$this->ebay->l(' at ').$time,
                    );
                }

            } else {
                $this->errors[] = array(
                    'type' => 'error',
                    'message' => $this->ebay->l('Order cron job has never been run.'),
                );
            }

        }

        // Returns
        if ((int) Configuration::get('EBAY_SYNC_ORDERS_RETURNS_BY_CRON') == 1) {
            if ($this->ebay_profile->getConfiguration('EBAY_ORDER_RETURNS_LAST_UPDATE') != null) {
                $datetime = new DateTime($this->ebay_profile->getConfiguration('EBAY_ORDER_RETURNS_LAST_UPDATE'));

                $date = date('Y-m-d', strtotime($datetime->format('Y-m-d H:i:s')));
                $time = date('H:i:s', strtotime($datetime->format('Y-m-d H:i:s')));

                $datetime2 = new DateTime();

                $interval = round(($datetime2->format('U') - $datetime->format('U')) / (60 * 60 * 24));

                if ($interval >= 1) {
                    $this->errors[] = array(
                        'type' => 'error',
                        'message' => $this->ebay->l('Last order returns synchronization has been done the ').$date.$this->ebay->l(' at ').$time,
                    );
                } else {
                    $this->infos[] = array(
                        'type' => 'info',
                        'message' => $this->ebay->l('Last order returns synchronization has been done the ').$date.$this->ebay->l(' at ').$time,
                    );
                }

            } else {
                $this->errors[] = array(
                    'type' => 'error',
                    'message' => $this->ebay->l('Order returns cron job has never been run.'),
                );
            }

        }

    }
}
