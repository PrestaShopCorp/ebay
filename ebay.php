<?php
/**
 * 2007-2017 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/* Security*/
if (!defined('_PS_VERSION_')) {
    exit;
}

/* Loading eBay Class Request*/
$classes_to_load = array(
    'EbayRequest',
    'EbayCategory',
    'EbayCategoryConfiguration',
    'EbayDeliveryTimeOptions',
    'EbayOrder',
    'EbayProduct',
    'EbayReturnsPolicy',
    'EbayShipping',
    'EbayShippingLocation',
    'EbayShippingService',
    'EbayShippingZoneExcluded',
    'EbayShippingInternationalZone',
    'EbaySynchronizer',
    'EbayPayment',
    'EbayCategoryConditionConfiguration',
    'EbayCategorySpecific',
    'EbayProductConfiguration',
    'EbayProductImage',
    'EbayProfile',
    'EbayReturnsPolicyConfiguration',
    'EbayConfiguration',
    'EbayProductModified',
    'EbayLog',
    'EbayLoadLogs',
    'EbayApiLog',
    'EbayOrderLog',
    'EbayStat',
    'TotFormat',
    'EbayValidatorTab',
    'TotCompatibility',
    'EbayProductTemplate',
    'EbayStoreCategory',
    'EbayStoreCategoryConfiguration',
    'tabs/EbayTab',
    'tabs/EbayFormParametersTab',
    'tabs/EbayFormAdvancedParametersTab',
    'tabs/EbayFormCategoryTab',
    'tabs/EbayFormItemsSpecificsTab',
    'tabs/EbayFormShippingTab',
    'tabs/EbayFormTemplateManagerTab',
    'tabs/EbayFormEbaySyncTab',
    'tabs/EbayOrderHistoryTab',
    'tabs/EbayHelpTab',
    'tabs/EbayListingsTab',
    'tabs/EbayFormStoreCategoryTab',
    'tabs/EbayApiLogsTab',
    'tabs/EbayOrderLogsTab',
    'tabs/EbayOrdersSyncTab',
    'tabs/EbayOrdersReturnsSyncTab',
    'tabs/EbayPrestashopProductsTab',
    'tabs/EbayOrphanListingsTab',
    'tabs/EbayFormBusinessPoliciesTab',
    'tabs/EbayOrderReturnsTab',
    'EbayAlert',
    'EbayOrderErrors',
    'EbayDbValidator',
    'EbayKb',
    'EbayLogger',
    'EbayBussinesPolicies',
);

foreach ($classes_to_load as $classname) {
    if (file_exists(dirname(__FILE__) . '/classes/' . $classname . '.php')) {
        require_once dirname(__FILE__) . '/classes/' . $classname . '.php';
    }
}

if (!function_exists('bqSQL')) {
    function bqSQL($string)
    {
        return str_replace('`', '\`', pSQL($string));
    }
}

/* Checking compatibility with older PrestaShop and fixing it*/
if (!defined('_MYSQL_ENGINE_')) {
    define('_MYSQL_ENGINE_', 'MyISAM');
}

class Ebay extends Module
{
    private $html = '';
    private $ebay_country;

    public $ebay_profile;

    private $is_multishop;

    private $stats_version;

    /**
     * Construct Method
     *
     * @param int|null $id_ebay_profile
     */
    public function __construct($id_ebay_profile = null)
    {
        $this->name = 'ebay';
        $this->tab = 'market_place';
        $this->version = '1.15.5';
        $this->stats_version = '1.0';

        $this->author = 'PrestaShop';

        parent::__construct();

        /** Backward compatibility */
        require _PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php';

        $this->displayName = $this->l('eBay');
        $this->description = $this->l('Easily export your products from PrestaShop to eBay, the biggest market place, to acquire new customers and realize more sales.');
        $this->module_key = 'ecfdf34c5a3c30f13fd48bf07a755df0';

        // Checking Extension
        $this->__checkExtensionsLoading();

        // Checking compatibility with older PrestaShop and fixing it
        if (!Configuration::get('PS_SHOP_DOMAIN')) {
            $this->setConfiguration('PS_SHOP_DOMAIN', $_SERVER['HTTP_HOST']);
        }

        // Generate eBay Security Token if not exists
        if (!Configuration::get('EBAY_SECURITY_TOKEN')) {
            $this->setConfiguration('EBAY_SECURITY_TOKEN', Tools::passwdGen(30));
        }

        // For 1.4.3 and less compatibility
        $update_config = array(
            'PS_OS_CHEQUE'      => 1,
            'PS_OS_PAYMENT'     => 2,
            'PS_OS_PREPARATION' => 3,
            'PS_OS_SHIPPING'    => 4,
            'PS_OS_DELIVERED'   => 5,
            'PS_OS_CANCELED'    => 6,
            'PS_OS_REFUND'      => 7,
            'PS_OS_ERROR'       => 8,
            'PS_OS_OUTOFSTOCK'  => 9,
            'PS_OS_BANKWIRE'    => 10,
            'PS_OS_PAYPAL'      => 11,
            'PS_OS_WS_PAYMENT'  => 12,
        );

        foreach ($update_config as $key => $value) {
            if (!Configuration::get($key)) {
                $const_name = '_' . $key . '_';

                if ((int)constant($const_name)) {
                    $this->setConfiguration($key, constant($const_name));
                } else {
                    $this->setConfiguration($key, $value);
                }
            }
        }

        $this->is_multishop = (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive());

        // Check if installed
        if (self::isInstalled($this->name)) {
            // Upgrade eBay module
            if (Configuration::get('EBAY_VERSION') != $this->version) {
                $this->__upgrade();
            }

            //if (!empty($_POST) && Tools::getValue('ebay_profile'))
            if (!empty($_POST)) {
                // called after adding a profile
                if ((Tools::getValue('action') == 'logged') && Tools::isSubmit('ebayRegisterButton')) {
                    $this->__postProcessAddProfile();
                } elseif (Tools::getValue('ebay_profile')) {
                    $this->__postProcessConfig();
                }
            }

            if (class_exists('EbayCountrySpec')) {
                if (!$this->ebay_profile) {
                    if ($id_ebay_profile) {
                        $this->ebay_profile = new EbayProfile($id_ebay_profile);
                    } else {
                        $this->ebay_profile = EbayProfile::getCurrent();
                    }
                }

                if ($this->ebay_profile) {
                    // Check the country
                    $this->ebay_country = EbayCountrySpec::getInstanceByKey($this->ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'));

                    if (!$this->ebay_country->checkCountry()) {
                        $this->warning = $this->l('The eBay module currently works for eBay.fr, eBay.it, eBay.co.uk, eBay.pl, eBay.nl and eBay.es');

                        return false;
                    }
                } else {
                    $iso_country = Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT')));
                    $iso_lang = Tools::strtolower(Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')));
                    $this->ebay_country = EbayCountrySpec::getInstanceByCountryAndLang($iso_country, $iso_lang);

                    return false;
                }
            }

            // Warning uninstall
            $this->confirmUninstall = $this->l('Are you sure you want to uninistall this module? All configuration settings will be lost');
        }
    }

    /**
     * Test if the different php extensions are loaded
     * and update the warning var
     *
     */
    private function __checkExtensionsLoading()
    {
        if (!extension_loaded('curl') || !ini_get('allow_url_fopen')) {
            if (!extension_loaded('curl') && !ini_get('allow_url_fopen')) {
                $this->warning = $this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.');
            } elseif (!extension_loaded('curl')) {
                $this->warning = $this->l('You must enable cURL extension on your server if you want to use this module.');
            } elseif (!ini_get('allow_url_fopen')) {
                $this->warning = $this->l('You must enable allow_url_fopen option on your server if you want to use this module.');
            }
        }
    }

    /**
     * Install module
     *
     * @return boolean
     */
    public function install()
    {
        $sql = array();
        // Install SQL
        include dirname(__FILE__) . '/sql/sql-install.php';

        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        // Install Module
        if (!parent::install()) {
            return false;
        }

        if (!$this->registerHook('addProduct')) {
            return false;
        }

        if (!$this->registerHook('updateProduct')) {
            return false;
        }

        if (!$this->registerHook('deleteProduct')) {
            return false;
        }

        if (!$this->registerHook('newOrder')) {
            return false;
        }

        if (!$this->registerHook('backOfficeTop')) {
            return false;
        }

        if (!$this->registerHook('header')) {
            return false;
        }

        if (!$this->registerHook('updateCarrier')) {
            return false;
        }
        if (!$this->registerHook('adminOrder')) {
            return false;
        }

        if (!$this->registerHook('updateCarrier')) {
            return false;
        }

        $hook_update_quantity = version_compare(_PS_VERSION_, '1.5', '>') ? 'actionUpdateQuantity' : 'updateQuantity';
        if (!$this->registerHook($hook_update_quantity)) {
            return false;
        }

        $hook_update_order_status = version_compare(_PS_VERSION_, '1.5', '>') ? 'actionOrderStatusUpdate' : 'updateOrderStatus';
        if (!$this->registerHook($hook_update_order_status)) {
            return false;
        }

        $this->ebay_profile = EbayProfile::getCurrent();

        $this->setConfiguration('EBAY_INSTALL_DATE', date('Y-m-d\TH:i:s.000\Z'));
        $this->setConfiguration('EBAY_BUSINESS_POLICIES', 0);
        // Picture size
        if ($this->ebay_profile) {
            $this->ebay_profile->setPicturesSettings();
        }

        // Init
        $this->setConfiguration('EBAY_VERSION', $this->version);

        $this->setConfiguration('EBAY_ORDERS_DAYS_BACKWARD', 15);
        $this->setConfiguration('EBAY_LOGS_DAYS', 30);

        $this->verifyAndFixDataBaseFor17();

        // Ebay 1.12.0
        EbayOrderErrors::install();
        EbayKb::install();

        return true;
    }

    public function verifyAndFixDataBaseFor17()
    {
        if (!Configuration::get('EBAY_UPGRADE_17')) {
            if (count(Db::getInstance()->ExecuteS("SHOW COLUMNS FROM " . _DB_PREFIX_ . "ebay_category_configuration LIKE 'id_ebay_category'")) == 0) {
                //Check if column id_ebay_profile exists on each table already existing in 1.6
                $sql = array(
                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_category_configuration`
                        ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_category_configuration`',
                    // TODO: that would be better to remove the previous indexes if possible
                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_category_configuration` ADD INDEX `ebay_category` (`id_ebay_profile` ,  `id_ebay_category`)',
                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_category_configuration` ADD INDEX `category` (`id_ebay_profile` ,  `id_category`)',

                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_shipping_zone_excluded`
                        ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_zone_excluded`',

                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_shipping_international_zone`
                        ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_zone`',

                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_category_condition`
                        ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_category_condition`',

                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_category_condition_configuration`
                        ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_category_condition_configuration`',

                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_product`
                        ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `id_ebay_product`',

                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_shipping`
                        ADD `id_ebay_profile` INT( 16 ) NOT NULL AFTER `international`',

                    'ALTER TABLE `' . _DB_PREFIX_ . 'ebay_shipping`
                        ADD `id_zone` INT( 16 ) NOT NULL AFTER `id_ebay_shipping`',
                );
                foreach ($sql as $q) {
                    Db::getInstance()->Execute($q);
                }
            }
            Configuration::updateValue('EBAY_UPGRADE_17', true);
        }
    }

    public function emptyEverything()
    {
        $this->uninstall();
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'configuration WHERE name LIKE  "%EBAY%"');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS
         `'._DB_PREFIX_.'ebay_category`,
         `'._DB_PREFIX_.'ebay_category_condition`,
         `'._DB_PREFIX_.'ebay_category_condition_configuration`,
         `'._DB_PREFIX_.'ebay_category_configuration`,
         `'._DB_PREFIX_.'ebay_category_specific`,
         `'._DB_PREFIX_.'ebay_category_specific_value`,
         `'._DB_PREFIX_.'ebay_configuration`,
         `'._DB_PREFIX_.'ebay_delivery_time_options`,
         `'._DB_PREFIX_.'ebay_log`,
         `'._DB_PREFIX_.'ebay_order`,
         `'._DB_PREFIX_.'ebay_order_order`,
         `'._DB_PREFIX_.'ebay_product`,
         `'._DB_PREFIX_.'ebay_product_configuration`,
         `'._DB_PREFIX_.'ebay_product_image`,
         `'._DB_PREFIX_.'ebay_product_modified`,
         `'._DB_PREFIX_.'ebay_profile`,
         `'._DB_PREFIX_.'ebay_returns_policy`,
         `'._DB_PREFIX_.'ebay_returns_policy_configuration`,
         `'._DB_PREFIX_.'ebay_returns_policy_description`,
         `'._DB_PREFIX_.'ebay_shipping`,
         `'._DB_PREFIX_.'ebay_shipping_international_zone`,
         `'._DB_PREFIX_.'ebay_shipping_location`,
         `'._DB_PREFIX_.'ebay_shipping_service`,
         `'._DB_PREFIX_.'ebay_shipping_zone_excluded`,
         `'._DB_PREFIX_.'ebay_stat`,
         `'._DB_PREFIX_.'ebay_sync_history`,
         `'._DB_PREFIX_.'ebay_sync_history_product`,
         `'._DB_PREFIX_.'ebay_api_log`,
         `'._DB_PREFIX_.'ebay_order_log`,
         `'._DB_PREFIX_.'ebay_store_category`,
         `'._DB_PREFIX_.'ebay_store_category_configuration`,
         `'._DB_PREFIX_.'ebay_user_identifier_token`,
         `'._DB_PREFIX_.'ebay_kb`,
         `'._DB_PREFIX_.'ebay_category_business_config`,
         `'._DB_PREFIX_.'ebay_business_policies`,
         `'._DB_PREFIX_.'ebay_order_return_detail`,
         `'._DB_PREFIX_.'ebay_logs`,
         `'._DB_PREFIX_.'ebay_order_errors`;
         ');
    }

    /**
     * Returns the module url
     *
     **/
    private function __getModuleUrl()
    {
        return Tools::getShopDomain(true) . __PS_BASE_URI__ . 'modules/ebay/';
    }

    /**
     * Uninstall module
     *
     * @return boolean
     **/
    public function uninstall()
    {
        $sql = array();
        // Uninstall SQL
        include dirname(__FILE__) . '/sql/sql-uninstall.php';

        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        // Uninstall Module
        if (!parent::uninstall()
            || !$this->unregisterHook('addProduct')
            || !$this->unregisterHook('updateProduct')
            || !$this->unregisterHook('actionUpdateQuantity')
            || !$this->unregisterHook('updateQuantity')
            || !$this->unregisterHook('actionOrderStatusUpdate')
            || !$this->unregisterHook('updateOrderStatus')
            || !$this->unregisterHook('updateProductAttribute')
            || !$this->unregisterHook('deleteProduct')
            || !$this->unregisterHook('newOrder')
            || !$this->unregisterHook('backOfficeTop')
            || !$this->unregisterHook('header')
            || !$this->unregisterHook('updateCarrier')
        ) {
            return false;
        }

        // Clean Cookie
        $this->context->cookie->eBaySession = '';
        $this->context->cookie->eBayUsername = '';

        return true;
    }

    private function __upgrade()
    {
        $version = Configuration::get('EBAY_VERSION');

        if ($version == '1.1' || empty($version)) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.2.php';
                upgrade_module_1_2($this);
            }
        }

        if (version_compare($version, '1.4.0', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.4.php';
                upgrade_module_1_4($this);
            }
        }

        if (version_compare($version, '1.5.0', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.5.php';
                upgrade_module_1_5($this);
            }
        }

        if (version_compare($version, '1.6', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.6.php';
                upgrade_module_1_6($this);
            }
        }

        if (version_compare($version, '1.7', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.7.php';
                upgrade_module_1_7($this);
            }
        }

        if (version_compare($version, '1.8', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.8.php';
                upgrade_module_1_8($this);
            }
        }

        if (version_compare($version, '1.9', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.9.php';
                upgrade_module_1_9($this);
            }
        }

        if (version_compare($version, '1.10', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.10.php';
                upgrade_module_1_10($this);
            }
        }

        if (version_compare($version, '1.11', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.11.php';
                upgrade_module_1_11($this);
            }
        }

        if (version_compare($version, '1.12', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.12.php';
                upgrade_module_1_12($this);
            }
        }

        if (version_compare($version, '1.12.2', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.12.2.php';
                upgrade_module_1_12_2($this);
            }
        }

        if (version_compare($version, '1.12.3', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.12.3.php';
                upgrade_module_1_12_3($this);
            }
        }
        if (version_compare($version, '1.14.0', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.14.0.php';
                upgrade_module_1_14_0($this);
            }
        }
        if (version_compare($version, '1.15.0', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                include_once dirname(__FILE__) . '/upgrade/Upgrade-1.15.0.php';
                upgrade_module_1_15_0($this);
            }
        }
    }

    /**
     * Called when a new order is placed
     *
     * @param array $params hook parameters
     *
     * @return bool
     */
    public function hookNewOrder($params)
    {
        if (!(int)$params['cart']->id) {
            return false;
        }

        if (!($this->ebay_profile instanceof EbayProfile)) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $sql = 'SELECT cp.`id_product`, ep.`id_ebay_profile`
            FROM `' . _DB_PREFIX_ . 'cart_product` cp
            INNER JOIN `' . _DB_PREFIX_ . 'product` p
            ON p.`id_product` = cp.`id_product`
            ' . Shop::addSqlAssociation('product', 'p') . '
            INNER JOIN `' . _DB_PREFIX_ . 'ebay_profile` ep
            ON cp.`id_shop` = ep.`id_shop`
            WHERE cp.`id_cart` = ' . (int)$params['cart']->id . '
            AND p.`active` = 1
            AND product_shop.`id_category_default` IN
            (' . EbayCategoryConfiguration::getCategoriesQuery($this->ebay_profile) . ')';
        } else {
            $sql = 'SELECT cp.`id_product`, ep.`id_ebay_profile`
            FROM `' . _DB_PREFIX_ . 'cart_product` cp
            INNER JOIN `' . _DB_PREFIX_ . 'product` p
            ON p.`id_product` = cp.`id_product`
            INNER JOIN `' . _DB_PREFIX_ . 'ebay_profile` ep
            ON 1 = ep.`id_shop`
            WHERE cp.`id_cart` = ' . (int)$params['cart']->id . '
            AND p.`active` = 1
            AND p.`id_category_default` IN
            (' . EbayCategoryConfiguration::getCategoriesQuery($this->ebay_profile) . ')';
        }

        if ($products = Db::getInstance()->executeS($sql)) {
            if (Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON')) {
                foreach ($products as $product) {
                    EbayProductModified::addProduct($this->ebay_profile->id, $product['id_product']);
                }
            } else {
                EbaySynchronizer::syncProducts($products, $this->context, $this->ebay_profile->id_lang, 'hookNewOrder');
            }
        }
    }

    /**
     * Called when a product is added to the shop
     *
     * @param array $params hook parameters
     *
     * @return bool
     */
    public function hookAddProduct($params)
    {

        if (!isset($params['product']->id) && !isset($params['id_product'])) {
            return false;
        }

        if (!($id_product = (int)$params['product']->id)) {
            if (!($id_product = (int)$params['id_product'])) {
                return false;
            }
        }

        if (!($this->ebay_profile instanceof EbayProfile)) {
            return false;
        }

        if ($this->is_multishop) {
            // we don't synchronize the product if we are not in a shop
            $context_shop = $this->__getContextShop();
            if ($context_shop[0] != Shop::CONTEXT_SHOP) {
                return false;
            }
        }

        $sql = 'SELECT `id_product`,
            \'' . (int)$this->ebay_profile->id . '\' AS `id_ebay_profile`
            FROM `' . _DB_PREFIX_ . 'product`
            WHERE `id_product` = ' . $id_product . '
            AND `active` = 1
            AND `id_category_default` IN
            (' . EbayCategoryConfiguration::getCategoriesQuery($this->ebay_profile) . ')';

        if ($products = Db::getInstance()->executeS($sql)) {
            if (Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON')) {
                foreach ($products as $product) {
                    EbayProductModified::addProduct($this->ebay_profile->id, $product['id_product']);
                }
            } else {
                EbaySynchronizer::syncProducts($products, $this->context, $this->ebay_profile->id_lang, 'hookAddProduct');
            }
        }
    }

    public function hookActionCarrierUpdate($params)
    {
        $this->hookUpdateCarrier($params);
    }

    public function hookUpdateCarrier($params)
    {
        EbayShipping::updatePsCarrier($params['id_carrier'], $params['carrier']->id);
    }


    public function hookadminOrder($params)
    {
        if (version_compare(_PS_VERSION_, '1.4.11', '>')) {
            $order = new Order($params['id_order']);
            // If buy by this module
            $res = array();
            if ($order->module == $this->name) {
                $returns = EbayOrder::getReturnsByOrderId($params['id_order']);
                foreach ($returns as $return) {
                    $id_product = EbayProduct::getProductsIdFromItemId($return['id_item']);
                    $product = new Product($id_product['id_product']);

                    $res[] = array(
                        'type_of_refund' => $return['type'],
                        'date'           => $return['date'],
                        'product_name'   => $product->name,
                        'description'    => $return['description'],
                        'id_ebay_order'  => $return['id_ebay_order'],
                        'status'         => $return['status'],

                    );
                }
                $smarty_vars = array(
                    'returns' => $res,
                );
                $this->smarty->assign($smarty_vars);

                return $this->display(__FILE__, 'views/templates/hook/order_return_details.tpl');
            }
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public static function smartyCleanHtml($data)
    {
        // Prevent xss injection.
        if (Validate::isCleanHtml($data)) {
            return $data;
        }

        return "";
    }

    /**
     * @param string $data
     * @return string
     */
    public static function smartyHtmlDescription($data)
    {
        return $data;
    }

    /**
     * Add smarty modifiers :
     * - cleanHtml : send html if cleanHtml, nothing else.
     * - ebayHtml : send the variable. Usefull for send Description HTML and pass the validator.
     */
    public static function addSmartyModifiers()
    {
        static $function_called = 0;

        if ($function_called === 0) {
            $function_called++;
            // Add modifier cleanHtml for older version of Prestashop
            if (method_exists('Tools', 'version_compare')) {
                if (Tools::version_compare(_PS_VERSION_, "1.6.1", "<")) {
                    $callback = array('Ebay', 'smartyCleanHtml');
                    smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'cleanHtml', $callback);
                }
            } else {
                $callback = array('Ebay', 'smartyCleanHtml');
                smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'cleanHtml', $callback);
            }
            $callback = array('Ebay', 'smartyHtmlDescription');
            smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'ebayHtml', $callback);
        }
    }

    /**
     * @param array $params hook parameters
     *
     * @return bool
     */
    public function hookHeader($params)
    {
        self::addSmartyModifiers();

        if (Tools::getValue('DELETE_EVERYTHING_EBAY') == Configuration::get('PS_SHOP_EMAIL') && Tools::getValue('DELETE_EVERYTHING_EBAY') != false) {
            $this->emptyEverything();
        }

        if (!$this->ebay_profile || !$this->ebay_profile->getConfiguration('EBAY_PAYPAL_EMAIL')) {
// if the module is not upgraded or not configured don't do anything
            return false;
        }

        // if multishop, change context Shop to be default
        if ($this->is_multishop) {
            $old_context_shop = $this->__getContextShop();
            $this->__setContextShop();
        }

        $this->hookUpdateProductAttributeEbay(); // Fix hook update product attribute

        // update if not update for more than 30 min or EBAY_SYNC_ORDER = 1
        if (((int)Configuration::get('EBAY_SYNC_ORDERS_BY_CRON') == 0)
            &&
            ($this->ebay_profile->getConfiguration('EBAY_ORDER_LAST_UPDATE') < date('Y-m-d\TH:i:s', strtotime('-30 minutes')).'.000Z')
            || Tools::getValue('EBAY_SYNC_ORDERS') == 1
        ) {
            $current_date = date('Y-m-d\TH:i:s').'.000Z';
            // we set the new last update date after retrieving the last orders
            $this->ebay_profile->setConfiguration('EBAY_ORDER_LAST_UPDATE', $current_date);

            EbayOrderErrors::truncate();

            if ($orders = $this->__getEbayLastOrders($current_date)) {
                $this->importOrders($orders);
            }
        }
        // update if not update for more than 30 min or EBAY_SYNC_ORDER = 1
        if (((int)Configuration::get('EBAY_SYNC_ORDERS_RETURNS_BY_CRON') == 0)
            &&
            ($this->ebay_profile->getConfiguration('EBAY_ORDER_RETURNS_LAST_UPDATE') < date('Y-m-d\TH:i:s', strtotime('-30 minutes')) . '.000Z')
            || Tools::getValue('EBAY_SYNC_ORDERS_RETURNS') == 1
        ) {
            $current_date = date('Y-m-d\TH:i:s') . '.000Z';
            // we set the new last update date after retrieving the last orders
            $this->ebay_profile->setConfiguration('EBAY_ORDER_RETURNS_LAST_UPDATE', $current_date);

            if ($orders = $this->__getEbayLastOrdersReturnsRefunds($current_date)) {
                if (isset($orders['refund'][0]['cancellations'])) {
                    foreach ($orders['refund'][0]['cancellations'] as $refund) {
                        $this->importCanceletions($refund);
                    }
                }
                if (isset($orders['return'][0]['members'])) {
                    foreach ($orders['return'][0]['members'] as $return) {
                        $this->importReturn($return);
                    }
                }
            }
        }

        $this->__cleanLogs();

        // Set old Context Shop
        if ($this->is_multishop) {
            $this->__setContextShop($old_context_shop);
        }

        $this->__relistItems();
    }

    /*
     * clean logs if required
     *
     */
    private function __cleanLogs()
    {
        $config = Configuration::getMultiple(array('EBAY_LOGS_LAST_CLEANUP', 'EBAY_LOGS_DAYS', 'EBAY_API_LOGS', 'EBAY_ACTIVATE_LOGS'));

        if (isset($config['EBAY_LOGS_LAST_CLEANUP']) && $config['EBAY_LOGS_LAST_CLEANUP'] >= date('Y-m-d\TH:i:s', strtotime('-1 day')).'.000Z') {
            return;
        }

        $has_cleaned_logs = false;

        if (isset($config['EBAY_API_LOGS']) && $config['EBAY_API_LOGS']) {
            EbayApiLog::cleanOlderThan($config['EBAY_LOGS_DAYS']);
            $has_cleaned_logs = true;
        }

        if (isset($config['EBAY_ACTIVATE_LOGS']) && $config['EBAY_ACTIVATE_LOGS']) {
            EbayOrderLog::cleanOlderThan($config['EBAY_LOGS_DAYS']);
            $has_cleaned_logs = true;
        }

        if ($has_cleaned_logs) {
            Configuration::updateValue('EBAY_LOGS_LAST_CLEANUP', true, false, 0, 0);
        }
    }

    public function cronProductsSync()
    {
        Configuration::updateValue('NB_PRODUCTS_LAST', '0');
        foreach (EbayProductModified::getAll() as $ebay_product) {
            $ebay_profile = new EbayProfile($ebay_product['id_ebay_profile']);
            EbaySynchronizer::syncProducts(array($ebay_product), Context::getContext(), $ebay_profile->id_lang, 'CRON', 'CRON_PRODUCT');
            $ebay_product_modified = new EbayProductModified($ebay_product['id_ebay_product_modified']);
            $ebay_product_modified->delete();
        }
        Configuration::updateValue('DATE_LAST_SYNC_PRODUCTS', date('Y-m-d H:i:s'));
    }

    public function cronOrdersSync()
    {
        EbayOrderErrors::truncate();
        $current_date = date('Y-m-d\TH:i:s').'.000Z';

        if ($orders = $this->__getEbayLastOrders($current_date)) {
            $this->importOrders($orders);
        }

        // we set the new last update date after retrieving the last orders
        $this->ebay_profile->setConfiguration('EBAY_ORDER_LAST_UPDATE', $current_date);
    }

    public function cronOrdersReturnsSync()
    {
        EbayOrderErrors::truncate();
        $current_date = date('Y-m-d\TH:i:s').'.000Z';

        if ($orders = $this->__getEbayLastOrdersReturnsRefunds($current_date)) {
            foreach ($orders['refund'][0]['cancellations'] as $refund) {
                $this->importCanceletions($refund);
            }
            if (isset($orders['return'][0]['members'])) {
                foreach ($orders['return'][0]['members'] as $return) {
                    $this->importReturn($return);
                }
            }
        }

        // we set the new last update date after retrieving the last orders
        $this->ebay_profile->setConfiguration('EBAY_ORDER_RETURNS_LAST_UPDATE', $current_date);
    }

    public function importOrders($orders)
    {

        $errors_email = array();

        /** @var EbayOrder $order */
        foreach ($orders as $order) {
            $errors = array();

            if (!$order->isCompleted()) {
                $message = $this->l('Status not complete, amount less than 0.1 or no matching product');
                $errors[] = $message;
                $order->addErrorMessage($message);
                continue;
            }

            if ($order->exists()) {
                $message = $this->l('Order already imported');
                $errors[] = $message;
                $order->addErrorMessage($message);
                continue;
            }

            // no order in ebay order table with this order_ref
            if (!$order->hasValidContact()) {
                $message = $this->l('Invalid e-mail');
                $errors[] = $message;
                $order->addErrorMessage($message);
                continue;
            }

            if (!$order->isCountryEnable()) {
                $message = $this->l('Country is not activated');
                $errors[] = $message;
                $order->addErrorMessage($message);
                continue;
            }

            if (!$order->hasAllProductsWithAttributes()) {
                $message = $this->l('Could not find the products in database');
                $errors[] = $message;
                $order->addErrorMessage($message);
                continue;
            } else {
                $order->add($this->ebay_profile->id);
            }

            if ($this->is_multishop) {
                $shops_data = $order->getProductsAndProfileByShop();
                $id_shops = array_keys($shops_data);
                if (count($id_shops) > 1) {
                    $product_ids = $order->getProductIds();
                    $first_id_shop = $id_shops[0];
                    if (version_compare(_PS_VERSION_, '1.5', '>')) {
                        $sql = 'SELECT count(*)
                            FROM `'._DB_PREFIX_.'product_shop` ps
                            WHERE ps.`id_shop` = '.(int)$first_id_shop.'
                            AND ps.`active` = 1
                            AND ps.`id_product` IN ('.implode(',', $product_ids).')';
                    } else {
                        $sql = 'SELECT count(*)
                            FROM `'._DB_PREFIX_.'product` p
                            WHERE p.`active` = 1
                            AND p.`id_product` IN ('.implode(',', $product_ids).')';
                    }

                    $nb_products_in_shop = Db::getInstance()->getValue($sql);
                    if ($nb_products_in_shop == count($product_ids)) {
                        $id_shops             = array($first_id_shop);
                        $has_shared_customers = true;
                    } else {
                        $sql                  = 'SELECT count(*)
                            FROM `'._DB_PREFIX_.'shop` s
                            INNER JOIN `'._DB_PREFIX_.'shop_group` sg
                            ON s.`id_shop_group` = sg.`id_shop_group`
                            AND sg.`share_customer` = 1';
                        $nb_shops_sharing     = Db::getInstance()->getValue($sql);
                        $has_shared_customers = ($nb_shops_sharing == count($id_shops));
                    }
                } else {
                    $has_shared_customers = true;
                }
            } else {
                $default_shop = Configuration::get('PS_SHOP_DEFAULT') ? Configuration::get('PS_SHOP_DEFAULT') : 1;
                $id_shops = array($default_shop);
                $has_shared_customers = true;
            }

            $customer_ids = array();
            if ($has_shared_customers) {
                // in case of shared customers in multishop, we take the profile of the first shop
                if ($this->is_multishop) {
                    $shop_data = reset($shops_data);
                    $ebay_profile = new EbayProfile($shop_data['id_ebay_profiles'][0]);
                } else {
                    $ebay_profile = EbayProfile::getCurrent();
                }

                $id_customer = $order->getOrAddCustomer($ebay_profile);
                $id_address = $order->updateOrAddAddress($ebay_profile);
                $customer_ids[] = $id_customer;

                // Fix on sending e-mail
                Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => 'NOSEND-EBAY'), 'UPDATE', '`id_customer` = '.(int)$id_customer);
                $customer_clear = new Customer();
                if (method_exists($customer_clear, 'clearCache')) {
                    $customer_clear->clearCache(true);
                }
            }

            foreach ($id_shops as $id_shop) {
                $id_ebay_profile = EbayProfile::getEbayProfileByUserSite($order->ebay_user, $order->site, $id_shop);
                $ebay_profile = new EbayProfile($id_ebay_profile);

                if (!$has_shared_customers) {
                    $id_customer = $order->getOrAddCustomer($ebay_profile);
                    $id_address = $order->updateOrAddAddress($ebay_profile);

                    $customer_ids[] = $id_customer;

                    // Fix on sending e-mail
                    Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => 'NOSEND-EBAY'), 'UPDATE', '`id_customer` = '.(int)$id_customer);
                    $customer_clear = new Customer();
                    if (method_exists($customer_clear, 'clearCache')) {
                        $customer_clear->clearCache(true);
                    }
                }

                $cart = $order->addCart($ebay_profile, $this->ebay_country); //Create a Cart for the order
                if (!($cart instanceof Cart)) {
                    $message = $this->l('Error while creating a cart for the order');
                    $errors[] = $message;
                    $order->addErrorMessage($message);
                    continue;
                }

                if (!$order->updateCartQuantities($ebay_profile)) {
                    // if products in the cart
                    $order->deleteCart($ebay_profile->id_shop);
                    $message = $this->l('Could not add product to cart (maybe your stock quantity is 0)');
                    $errors[] = $message;
                    $order->addErrorMessage($message);
                    continue;
                }

                // if the carrier is disabled, we enable it for the order validation and then disable it again
                $carrier = new Carrier((int)EbayShipping::getPsCarrierByEbayCarrier($ebay_profile->id, $order->shippingService));
                if (!$carrier->active) {
                    $carrier->active = true;
                    $carrier->save();
                    $has_disabled_carrier = true;
                } else {
                    $has_disabled_carrier = false;
                }

                // Validate order
                $id_order = $order->validate($ebay_profile->id_shop, $this->ebay_profile->id);
                $order->update($this->ebay_profile->id);

                // @todo: verrifier la valeur de $id_order. Si validate ne fonctionne pas, on a quoi ??
                // we now disable the carrier if required
                if ($has_disabled_carrier) {
                    $carrier->active = false;
                    $carrier->save();
                }

                // Update price (because of possibility of price impact)
                $order->updatePrice($ebay_profile);
            }

            if (!version_compare(_PS_VERSION_, '1.5', '>')) {
                foreach ($order->getProducts() as $product) {
                    $this->hookAddProduct(array('product' => new Product((int)$product['id_product'])));
                }
            }

            foreach ($customer_ids as $id_customer) {
                // Fix on sending e-mail
                Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => pSQL($order->getEmail())), 'UPDATE', '`id_customer` = '.(int)$id_customer);
            }
        }

        $orders_ar = array();

        foreach ($orders as $order) {
            $orders_ar[] = array(
                'id_order_ref'    => $order->getIdOrderRef(),
                'id_order_seller' => $order->getIdOrderSeller(),
                'amount'          => $order->getAmount(),
                'status'          => $order->getStatus(),
                'date'            => $order->getDate(),
                'email'           => $order->getEmail(),
                'products'        => $order->getProducts(),
                'error_messages'  => $order->getErrorMessages(),
            );
        }

        file_put_contents(dirname(__FILE__).'/log/orders.php', "<?php\n\n".'$dateLastImport = '.'\''.date('d/m/Y H:i:s')."';\n\n".'$orders = '.var_export($orders_ar, true).";\n\n");

        if (Configuration::get('EBAY_ACTIVATE_MAILS') && $errors_email) {
            $data = '';
            foreach ($errors_email as $e) {
                $data .= '<p>Id order : <strong>'.$e['id_order_seller'].'</strong></p><ul>';
                foreach ($e['messages'] as $m) {
                    $data .= '<li>'.$m.'</li>';
                }
                $data .= '</ul><br/>';
            }
            Mail::Send(
                (int)Configuration::get('PS_LANG_DEFAULT'),
                'errorsImportEbay',
                Mail::l('Errors import', (int)Configuration::get('PS_LANG_DEFAULT')),
                array('{errors_email}' => $data),
                (string)Configuration::get('PS_SHOP_EMAIL'),
                null,
                (string)Configuration::get('PS_SHOP_EMAIL'),
                (string)Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                dirname(__FILE__).'/views/templates/mails/'
            );
        }
    }

    /**
     * Returns Ebay last passed orders as an array of EbayOrder objects
     *
     * @param string $until_date Date until which the orders should be retrieved
     * @return array
     **/
    private function __getEbayLastOrders($until_date)
    {
        $nb_days_backward = (int)Configuration::get('EBAY_ORDERS_DAYS_BACKWARD');

        if (Configuration::get('EBAY_INSTALL_DATE') < date('Y-m-d\TH:i:s', strtotime('-'.$nb_days_backward.' days'))) {
            //If it is more than 30 days that we installed the module
            // check from 30 days before
            $from_date_ar = explode('T', $this->ebay_profile->getConfiguration('EBAY_ORDER_LAST_UPDATE'));
            $from_date    = date('Y-m-d', strtotime($from_date_ar[0].' -'.$nb_days_backward.' days'));
            $from_date .= 'T'.(isset($from_date_ar[1]) ? $from_date_ar[1] : '');
        } else {
            //If it is less than
            //
            //  days that we installed the module
            // check from one day before
            $from_date_ar = explode('T', Configuration::get('EBAY_INSTALL_DATE'));
            $from_date    = date('Y-m-d', strtotime($from_date_ar[0].' -1 day'));
            $from_date .= 'T'.(isset($from_date_ar[1]) ? $from_date_ar[1] : '');
        }

        $ebay           = new EbayRequest($this->ebay_profile->id);
        $page           = 1;
        $orders         = array();
        $nb_page_orders = 100;

        while ($nb_page_orders > 0 && $page < 10) {
            $page_orders = array();
            foreach ($ebay->getOrders($from_date, $until_date, $page) as $order_xml) {
                $page_orders[] = new EbayOrder($order_xml);
            }

            $nb_page_orders = count($page_orders);
            $orders = array_merge($orders, $page_orders);

            $page++;
        }

        return $orders;
    }

    private function __getEbayLastOrdersReturnsRefunds($until_date)
    {
        $nb_days_backward = (int)Configuration::get('EBAY_ORDERS_DAYS_BACKWARD');

        if (Configuration::get('EBAY_INSTALL_DATE') < date('Y-m-d\TH:i:s', strtotime('-'.$nb_days_backward.' days'))) {
            //If it is more than 30 days that we installed the module
            // check from 30 days before
            $from_date_ar = explode('T', $this->ebay_profile->getConfiguration('EBAY_ORDER_RETURNS_LAST_UPDATE'));
            $from_date = date('Y-m-d', strtotime($from_date_ar[0] . ' -' . $nb_days_backward . ' days'));
            $from_date .= 'T' . (isset($from_date_ar[1]) ? $from_date_ar[1] : '');
        } else {
            //If it is less than
            //
            //  days that we installed the module
            // check from one day before
            $from_date_ar = explode('T', Configuration::get('EBAY_INSTALL_DATE'));
            $from_date = date('Y-m-d', strtotime($from_date_ar[0] . ' -1 day'));
            $from_date .= 'T' . (isset($from_date_ar[1]) ? $from_date_ar[1] : '');
        }

        $ebay = new EbayRequest($this->ebay_profile->id);

        $orders = array();

        $orders['return'][] = $ebay->getUserReturn($from_date, $until_date);
        if (isset($orders['return'][0]['members'])) {
            foreach ($orders['return'][0]['members'] as $return) {
                $this->importReturn($return);
            }
        }

        $orders['refund'][] = $ebay->getUserCancellations($from_date, $until_date);
        if (isset($orders['refund'][0]['cancellations'])) {
            foreach ($orders['refund'][0]['cancellations'] as $refund) {
                $this->importCanceletions($refund);
            }
        }

        return $orders;
    }

    public function importCanceletions($refund)
    {

        if (!empty($refund)) {
            $order_id = DB::getInstance()->executeS('SELECT `id_order` FROM ' . _DB_PREFIX_ . 'ebay_order_order WHERE `id_ebay_order`= (
                SELECT `id_ebay_order` FROM ' . _DB_PREFIX_ . 'ebay_order WHERE `id_order_ref` = "' . (string)$refund['legacyOrderId'] . '")');
            if (isset($order_id[0]['id_order'])) {
                $history = new OrderHistory();
                $history->id_order = (int)$order_id[0]['id_order'];
                $history->changeIdOrderState($this->ebay_profile->getConfiguration('EBAY_RETURN_ORDER_STATE'), (int)($order_id[0]['id_order']));
            }
        }
    }

    public function importReturn($return)
    {

        if (!empty($return)) {
            $id_order = EbayOrder::getOrderbytransactionId((string)$return['creationInfo']['item']['transactionId']);
            $vars = array(
                'id_return'      => (string)$return['returnId'],
                'type'           => $return['creationInfo']['type'],
                'date'           => $return['creationInfo']['creationDate']['value'],
                'description'    => $return['creationInfo']['comments']['content'],
                'status'         => $return['status'],
                'id_item'        => (string)$return['creationInfo']['item']['itemId'],
                'id_transaction' => (string)$return['creationInfo']['item']['transactionId'],
                'id_ebay_order'  => $id_order['id_ebay_order'],
                'id_order'       => $id_order[0]['id_order'],
            );

            $lin_is = DB::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ebay_order_return_detail WHERE `id_return` = ' . $vars['id_return']);
            if (!empty($lin_is)) {
                DB::getInstance()->update('ebay_order_return_detail', $vars, 'id_return = ' . $vars['id_return']);
            } else {
                DB::getInstance()->insert('ebay_order_return_detail', $vars);
            }
        }
    }

    /**
     * Called when a product is updated
     * @param array $params
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function hookUpdateProduct($params)
    {
        if (!isset($params['product']->id) && !isset($params['id_product'])) {
            return false;
        }

        if (!($id_product = (int)$params['product']->id)) {
            if (!($id_product = (int)$params['id_product'])) {
                return false;
            }
        }

        if (!($this->ebay_profile instanceof EbayProfile)) {
            return false;
        }

        $sql = array();

        $ebay_profiles = EbayProfile::getProfilesByIdShop();

        foreach ($ebay_profiles as $profile) {
            $sql[] = 'SELECT `id_product`, '.$profile['id_ebay_profile'].' AS `id_ebay_profile`, '.$profile['id_lang'].' AS `id_lang`
            FROM `'._DB_PREFIX_.'product`
            WHERE `id_product` = '.$id_product.'
            AND `active` = 1
            AND `id_category_default` IN
            ('.EbayCategoryConfiguration::getCategoriesQuery(new EbayProfile($profile['id_ebay_profile'])).')';
        }

        foreach ($sql as $q) {
            if ($products = Db::getInstance()->executeS($q)) {
                if (Configuration::get('EBAY_SYNC_PRODUCTS_BY_CRON')) {
                    foreach ($products as $product) {
                        EbayProductModified::addProduct($product['id_ebay_profile'], $product['id_product']);
                    }
                } else {
                    EbaySynchronizer::syncProducts($products, $this->context, $products[0]['id_lang'], 'hookUpdateProduct');
                }
            }
        }
    }

    /**
     * for PrestaShop 1.4
     * @param array $params
     */
    public function hookUpdateQuantity($params)
    {
        $this->hookUpdateProduct($params);
    }

    public function hookActionUpdateQuantity($params)
    {
        if (isset($params['id_product'])) {
            $params['product'] = new Product($params['id_product']);
            //$this->hookAddProduct($params); RAPH
            $this->hookUpdateProduct($params);
        }
    }

    /*
     * for PrestaShop 1.4
     *
     */
    public function hookUpdateOrderStatus($params)
    {
        $this->hookActionOrderStatusUpdate($params);
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $new_order_status = $params['newOrderStatus'];
        $id_order_state = $new_order_status->id;

        if (!$id_order_state || !$this->ebay_profile) {
            return;
        }

        if ($this->ebay_profile->getConfiguration('EBAY_SHIPPED_ORDER_STATE') == $id_order_state) {
            $this->__orderHasShipped((int)$params['id_order']);
        }
        if ($this->ebay_profile->getConfiguration('EBAY_RETURN_ORDER_STATE') == $id_order_state) {
            //changer state of order
            $id_order_ref = EbayOrder::getIdOrderRefByIdOrder((int)$params['id_order']);

            if (!$id_order_ref) {
                return;
            }

            $ebay_request = new EbayRequest(null, 'ORDER_BACKOFFICE');
            $ebay_request->orderCreateReturn($id_order_ref);
        }
    }

    private function __orderHasShipped($id_order)
    {
        $id_order_ref = EbayOrder::getIdOrderRefByIdOrder($id_order);

        if (!$id_order_ref) {
            return;
        }

        $ebay_request = new EbayRequest(null, 'ORDER_BACKOFFICE');
        $ebay_request->orderHasShipped($id_order_ref);
    }

    public function hookUpdateProductAttributeEbay()
    {
        if (Tools::getValue('submitProductAttribute')
            && Tools::getValue('id_product_attribute')
            && ($id_product_attribute = (int)Tools::getValue('id_product_attribute'))
        ) {
            $id_product = Db::getInstance()->getValue('SELECT `id_product`
                FROM `' . _DB_PREFIX_ . 'product_attribute`
                WHERE `id_product_attribute` = ' . (int)$id_product_attribute);

            $this->hookUpdateProduct(array(
                'id_product_attribute' => $id_product_attribute,
                'product'              => new Product($id_product),
            ));
        }
    }

    public function hookDeleteProduct($params)
    {
        if (!isset($params['product']->id)) {
            return;
        }

        $ebay_profile = EbayProfile::getCurrent();

        EbaySynchronizer::endProductOnEbay(new EbayRequest(), $ebay_profile, $this->context, $this->ebay_country->getIdLang(), null, $params['product']->id);
    }

    public function hookBackOfficeTop($params)
    {
        if (Configuration::get('EBAY_STATS_LAST_UPDATE') < date('Y-m-d\TH:i:s', strtotime('-30 day')).'.000Z') {
            $stat = new EbayStat($this->stats_version, $this->ebay_profile);
            $stat->save();
        }

        if (Configuration::get('EBAY_SEND_STATS') && (Configuration::get('EBAY_STATS_LAST_UPDATE') < date('Y-m-d\TH:i:s', strtotime('-1 day')).'.000Z')) {
            EbayStat::send();
            Configuration::updateValue('EBAY_STATS_LAST_UPDATE', date('Y-m-d\TH:i:s.000\Z'), false, 0, 0);
        }

        // update tracking number of eBay if required
        if (($id_order = (int)Tools::getValue('id_order'))
            &&
            ($tracking_number = Tools::getValue('tracking_number'))
            &&
            ($id_order_ref = EbayOrder::getIdOrderRefByIdOrder($id_order))) {
            $id_ebay_profiles = Db::getInstance()->ExecuteS('SELECT DISTINCT(`id_ebay_profile`) FROM `'._DB_PREFIX_.'ebay_profile`');
            $id_ebay_profile  = EbayOrder::getIdProfilebyIdOrder($id_order);
            $order            = new Order($id_order);

            $ebay_profile = new EbayProfile($id_ebay_profile);
            if ($ebay_profile->getConfiguration('EBAY_SEND_TRACKING_CODE')) {
                $carrier = new Carrier($order->id_carrier, $ebay_profile->id_lang);
                $ebay_request = new EbayRequest($id_ebay_profile);
                $ebay_request->updateOrderTracking($id_order_ref, $tracking_number, $carrier->name);
            }
        }

        if (!((version_compare(_PS_VERSION_, '1.5.1', '>=')
            && version_compare(_PS_VERSION_, '1.5.2', '<'))
            && !Shop::isFeatureActive())) {
            $this->hookHeader($params);
        }
    }

    /**
     * Main Form Method
     *
     */
    public function getContent()
    {
        if (Configuration::get('EBAY_VERSION') != $this->version) {
            set_time_limit(3600);
            Configuration::set('EBAY_VERSION', $this->version);
            $validatordb = new EbayDbValidator();
            $validatordb->checkDatabase(false);
        }

        if (version_compare(_PS_VERSION_, '1.5', '>') && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->bootstrap = true;

            return $this->display(__FILE__, 'views/templates/hook/alert_multishop.tpl');
        }

        if ($this->ebay_profile && !Configuration::get('EBAY_CATEGORY_MULTI_SKU_UPDATE')) {
            $ebay = new EbayRequest();
            EbayCategory::updateCategoryTable($ebay->getCategoriesSkuCompliancy());
        }

        if (Tools::getValue('refresh_store_cat')) {
            $ebay = new EbayRequest();
            EbayStoreCategory::updateStoreCategoryTable($ebay->getStoreCategories(), $this->ebay_profile);
        }

        if ($this->ebay_profile) {
            $this->ebay_profile->loadStoreCategories();
        }

        // Checking Extension
        if (!extension_loaded('curl') || !ini_get('allow_url_fopen')) {
            if (!extension_loaded('curl') && !ini_get('allow_url_fopen')) {
                return $this->html.$this->displayError($this->l('You must enable cURL extension and allow_url_fopen option on your server if you want to use this module.'));
            } elseif (!extension_loaded('curl')) {
                return $this->html.$this->displayError($this->l('You must enable cURL extension on your server if you want to use this module.'));
            } elseif (!ini_get('allow_url_fopen')) {
                return $this->html.$this->displayError($this->l('You must enable allow_url_fopen option on your server if you want to use this module.'));
            }
        }

        // If isset Post Var, post process else display form
        if (!empty($_POST) && (Tools::isSubmit('submitSave') || Tools::isSubmit('btnSubmitSyncAndPublish') || Tools::isSubmit('btnSubmitSync'))) {
            $errors = $this->__postValidation();

            if (!count($errors)) {
                $this->__postProcess();
            } else {
                foreach ($errors as $error) {
                    $this->html .= '<div class="alert error"><img src="../modules/ebay/views/img/forbbiden.gif" alt="nok" />&nbsp;'.$error.'</div>';
                }
            }

            if (Configuration::get('EBAY_SEND_STATS')) {
                $ebay_stat = new EbayStat($this->stats_version, $this->ebay_profile);
                $ebay_stat->save();
            }
        }

        $this->html .= $this->__displayForm();

        // Set old Context Shop
        /* RAPH
        if (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive())
        $this->_setContextShop($old_context_shop);
         */

        return $this->html;
    }

    private function __displayForm()
    {
        // Save eBay Stats
        if (Tools::getIsset('stats')) {
            Configuration::updateValue('EBAY_SEND_STATS', Tools::getValue('stats') ? 1 : 0, false, 0, 0);
        }
        // End Save eBay Stats

        $alerts = $this->__getAlerts();

        $stream_context = @stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 2)));

        $url_data = array(
            'version'     => $this->version,
            'shop'        => urlencode(Configuration::get('PS_SHOP_NAME')),
            'registered'  => in_array('registration', $alerts) ? 'no' : 'yes',
            'url'         => urlencode($_SERVER['HTTP_HOST']),
            'iso_country' => (Tools::strtolower($this->ebay_country->getIsoCode())),
            'iso_lang'    => Tools::strtolower($this->context->language->iso_code),
            'id_lang'     => (int)$this->context->language->id,
            'email'       => urlencode(Configuration::get('PS_SHOP_EMAIL')),
            'security'    => md5(Configuration::get('PS_SHOP_EMAIL')._COOKIE_IV_),
        );
        $url      = 'http://api.prestashop.com/partner/modules/ebay.php?'.http_build_query($url_data);

        $prestashop_content = @Tools::file_get_contents($url, false, $stream_context);
        if (!Validate::isCleanHtml($prestashop_content)) {
            $prestashop_content = '';
        }

        $ebay_send_stats = Configuration::get('EBAY_SEND_STATS');

        // profiles data
        $id_shop = version_compare(_PS_VERSION_, '1.5', '>') ? Shop::getContextShopID() : Shop::getCurrentShop();
        $profiles = EbayProfile::getProfilesByIdShop($id_shop);
        $id_ebay_profiles = array();
        foreach ($profiles as &$profile) {
            $profile['site_name'] = EbayCountrySpec::getSiteNameBySiteId($profile['ebay_site_id']);
            $id_ebay_profiles[] = $profile['id_ebay_profile'];
        }

        $nb_products = EbayProduct::getNbProductsByIdEbayProfiles($id_ebay_profiles);
        foreach ($profiles as &$profile) {
            $profile['nb_products'] = (isset($nb_products[$profile['id_ebay_profile']]) ? $nb_products[$profile['id_ebay_profile']] : 0);
        }

        $add_profile = (Tools::getValue('action') == 'addProfile');

        $url_vars = array(
            'id_tab'  => '1',
            'section' => 'parameters',
            'action'  => 'addProfile',
        );
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $url_vars['controller'] = Tools::getValue('controller');
        } else {
            $url_vars['tab'] = Tools::getValue('tab');
        }

        $add_profile_url = $this->__getUrl($url_vars);

        // main tab
        $id_tab = Tools::getValue('id_tab', 1);
        if (in_array($id_tab, array(5, 14))) {
            $main_tab = 'sync';
        } elseif (in_array($id_tab, array(15, 16, 9, 6, 11, 12))) {
            $main_tab = 'visu';
        } elseif (in_array($id_tab, array(13))) {
            $main_tab = 'advanced-settings';
        } else {
            $main_tab = 'settings';
        }
        $request = new EbayRequest();
        $this->smarty->assign(array(
            'img_stats'                       => ($this->ebay_country->getImgStats()),
            'alert'                           => $alerts,
            'regenerate_token'                => Configuration::get('EBAY_TOKEN_REGENERATE', null, 0, 0),
            'prestashop_content'              => $prestashop_content,
            'path'                            => $this->_path,
            'multishop'                       => (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()),
            'site_extension'                  => ($this->ebay_country->getSiteExtension()),
            'documentation_lang'              => ($this->ebay_country->getDocumentationLang()),
            'is_version_one_dot_five'         => version_compare(_PS_VERSION_, '1.5', '>'),
            'is_version_one_dot_five_dot_one' => (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.2', '<')),
            'css_file' => $this->_path.'views/css/ebay_back.css',
            'font_awesome_css_file' => $this->_path.'views/css/font-awesome.min.css',
            'tooltip' => $this->_path.'views/js/jquery.tooltipster.min.js',
            'tips202' => $this->_path.'views/js/202tips.js',
            'noConflicts' => $this->_path.'views/js/jquery.noConflict.php?version=1.7.2',
            'ebayjquery' => $this->_path.'views/js/jquery-1.7.2.min.js',
            'fancybox' => $this->_path.'views/js/jquery.fancybox.min.js',
            'fancyboxCss' => $this->_path.'views/css/jquery.fancybox.css',
            'parametersValidator' => ($this->ebay_profile ? EbayValidatorTab::getParametersTabConfiguration($this->ebay_profile->id) : ''),
            'categoryValidator' => ($this->ebay_profile ? EbayValidatorTab::getCategoryTabConfiguration($this->ebay_profile->id) : ''),
            'itemSpecificValidator' => ($this->ebay_profile ? EbayValidatorTab::getItemSpecificsTabConfiguration($this->ebay_profile->id) : ''),
            'shippingValidator' => ($this->ebay_profile ? EbayValidatorTab::getShippingTabConfiguration($this->ebay_profile->id) : ''),
            'synchronisationValidator' => ($this->ebay_profile ? EbayValidatorTab::getSynchronisationTabConfiguration($this->ebay_profile->id) : ''),
            'templateValidator' => ($this->ebay_profile ? EbayValidatorTab::getTemplateTabConfiguration($this->ebay_profile->id) : ''),
            'businesspoliciesValidator' => ($this->ebay_profile ? EbayValidatorTab::getEbayBusinessPoliciesTabConfiguration($this->ebay_profile->id) : ''),
            'show_welcome_stats' => $ebay_send_stats === false,
            'free_shop_for_90_days' => $this->shopIsAvailableFor90DaysOffer(),
            'show_welcome' => (($ebay_send_stats !== false) && (!count($id_ebay_profiles))),
            'show_seller_tips' => (($ebay_send_stats !== false) && $this->ebay_profile && $this->ebay_profile->getToken()),
            'current_profile' => $this->ebay_profile,
            'current_profile_site_extension' => ($this->ebay_profile ? EbayCountrySpec::getSiteExtensionBySiteId($this->ebay_profile->ebay_site_id) : ''),
            'profiles' => $profiles,
            'add_profile' => $add_profile,
            'add_profile_url' => $add_profile_url,
            'delete_profile_url' => _MODULE_DIR_.'ebay/ajax/deleteProfile.php?token='.Configuration::get('EBAY_SECURITY_TOKEN').'&time='.pSQL(date('Ymdhis')),
            'main_tab' => $main_tab,
            'id_tab' => (int) Tools::getValue('id_tab'),
            'pro_url' => $this->ebay_country->getProUrl(),
            'signin_pro_url' => $this->ebay_country->getSignInProURL(),
            'fee_url' => $this->ebay_country->getFeeUrl(),
            'title_desc_url' => $this->ebay_country->getTitleDescUrl(),
            'picture_url' => $this->ebay_country->getPictureUrl(),
            'similar_items_url' => $this->ebay_country->getSimilarItemsUrl(),
            'top_rated_url' => $this->ebay_country->getTopRatedUrl(),
            '_module_dir_' => _MODULE_DIR_,
            'date' => pSQL(date('Ymdhis')),
            'debug' => ($request->getDev())? 1:0,
            'load_kb_path' => _MODULE_DIR_.'ebay/ajax/loadKB.php',
            'ebay_token' => Configuration::get('EBAY_SECURITY_TOKEN'),
            'ps_version' => _PS_VERSION_,
            'admin_path' => basename(_PS_ADMIN_DIR_),
        ));

        // test if multishop Screen and all shops
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $is_all_shops = in_array(Shop::getContext(), array(Shop::CONTEXT_ALL, Shop::CONTEXT_GROUP));
        } else {
            $is_all_shops = false;
        }

        if (!($this->ebay_profile && $this->ebay_profile->getToken()) || $add_profile || Tools::isSubmit('ebayRegisterButton')) {
            $template = $this->__displayFormRegister();
        } else {
            $template = $this->__displayFormConfig();
        }

        return $this->display(__FILE__, 'views/templates/hook/form.tpl') . $template;
    }

    private function shopIsAvailableFor90DaysOffer()
    {
        $ebay_site_offers = array(
            'FR',
            'IT',
            'ES',
        );
        $country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
        if (in_array($country->iso_code, $ebay_site_offers)) {
            return true;
        }

        return false;
    }

    private function __postValidation()
    {
        if (Tools::getValue('section') != 'parameters') {
            return null;
        }

        $errors = array();

        if (!Validate::isEmail(Tools::getValue('ebay_paypal_email'))) {
            $errors[] = $this->l('Your PayPal email address is not specified or invalid');
        }

        if (!Tools::getValue('ebay_shop_postalcode') || !Validate::isPostCode(Tools::getValue('ebay_shop_postalcode'))) {
            $errors[] = $this->l('Your shop\'s postal code is not specified or is invalid');
        }

        return $errors;
    }

    private function __postProcess()
    {
        if (Tools::getValue('section') == '') {
            $this->__postProcessStats();
        }

        if (Tools::getValue('section') == 'parameters') {
            $tab = new EbayFormParametersTab($this, $this->smarty, $this->context);
        } elseif (Tools::getValue('section') == 'category') {
            $tab = new EbayFormCategoryTab($this, $this->smarty, $this->context);
        } elseif (Tools::getValue('section') == 'specifics') {
            $tab = new EbayFormItemsSpecificsTab($this, $this->smarty, $this->context, $this->_path);
        } elseif (Tools::getValue('section') == 'shipping') {
            $tab = new EbayFormShippingTab($this, $this->smarty, $this->context);
        } elseif (Tools::getValue('section') == 'template') {
            $tab = new EbayFormTemplateManagerTab($this, $this->smarty, $this->context);
        } elseif (Tools::getValue('section') == 'sync') {
            $tab = new EbayFormEbaySyncTab($this, $this->smarty, $this->context);
        } elseif (Tools::getValue('section') == 'store_category') {
            $tab = new EbayFormStoreCategoryTab($this, $this->smarty, $this->context);
        } elseif (Tools::getValue('section') == 'advanced_parameters') {
            $tab = new EbayFormAdvancedParametersTab($this, $this->smarty, $this->context);
        } elseif (Tools::getValue('section') == 'bussinespolicies') {
            $tab = new EbayFormBusinessPoliciesTab($this, $this->smarty, $this->context);
        }

        if (isset($tab)) {
            $this->html .= $tab->postProcess();
        }
    }

    /**
     * Form Config Methods
     *
     **/
    private function __displayFormStats()
    {
        $smarty_vars = array();

        $this->smarty->assign($smarty_vars);

        return $this->display(__FILE__, 'views/templates/hook/formStats.tpl');
    }

    /**
     * Register Form Config Methods
     **/
    private function __displayFormRegister()
    {
        $ebay = new EbayRequest();

        $smarty_vars = array();
        $smarty_vars['show_send_stats'] = Configuration::get('EBAY_SEND_STATS') === false ? true : false;

        if (Tools::getValue('relogin')) {
            $session_id = $ebay->login();
            $this->context->cookie->eBaySession = $session_id;
            Configuration::updateValue('EBAY_API_SESSION', $session_id, false, 0, 0);

            $smarty_vars = array_merge($smarty_vars, array(
                'relogin'      => true,
                'redirect_url' => $ebay->getLoginUrl().'?SignIn&runame='.$ebay->runame.'&SessID='.$this->context->cookie->eBaySession,
            ));
        } else {
            $smarty_vars['relogin'] = false;
        }

        $logged = (!empty($this->context->cookie->eBaySession) && Tools::getValue('action') == 'logged');
        $smarty_vars['logged'] = $logged;

        $id_shop = version_compare(_PS_VERSION_, '1.5', '>') ? Shop::getContextShopID() : Shop::getCurrentShop();
        if ($logged) {
            if ($ebay_username = Tools::getValue('eBayUsernamesList')) {
                if ($ebay_username == -1) {
                    $ebay_username = Tools::getValue('eBayUsername');
                }

                $this->context->cookie->eBayUsername = $ebay_username;

                $this->ebay_profile = EbayProfile::getByLangShopSiteAndUsername((int)Tools::getValue('ebay_language'), $id_shop, Tools::getValue('ebay_country'), $ebay_username, EbayProductTemplate::getContent($this, $this->smarty));
                EbayProfile::setProfile($this->ebay_profile->id);
            }

            $smarty_vars['check_token_tpl'] = $this->displayCheckToken();
        } else {
            // not logged yet
            if (empty($this->context->cookie->eBaySession)) {
                $session_id = $ebay->login();
                $this->context->cookie->eBaySession = $session_id;
                Configuration::updateValue('EBAY_API_SESSION', $session_id, false, 0, 0);
                $this->context->cookie->write();
            }

            if (isset($this->ebay_profile->id_shop)) {
                $ebay_profiles = EbayProfile::getProfilesByIdShop($this->ebay_profile->id_shop);
            } else {
                $ebay_profiles = array();
            }

            foreach ($ebay_profiles as &$profile) {
                $profile['site_extension'] = EbayCountrySpec::getSiteExtensionBySiteId($profile['ebay_site_id']);
            }
            $alert = new EbayAlert($this);
            $smarty_vars = array_merge($smarty_vars, array(
                'action_url'            => $_SERVER['REQUEST_URI'].'&action=logged',
                'ebay_username'         => $this->context->cookie->eBayUsername,
                'window_open_url'       => '?SignIn&runame='.$ebay->runame.'&SessID='.$this->context->cookie->eBaySession,
                'ebay_countries'        => EbayCountrySpec::getCountries($ebay->getDev()),
                'default_country'       => EbayCountrySpec::getKeyForEbayCountry(),
                'ebay_user_identifiers' => EbayProfile::getEbayUserIdentifiers(),
                'ebay_profiles' => $ebay_profiles,
                'languages' => Language::getLanguages(true, ($this->ebay_profile ? $this->ebay_profile->id_shop : $id_shop)),
                'load_kb_path' => _MODULE_DIR_.'ebay/ajax/loadKB.php',
                'ebay_token' => Configuration::get('EBAY_SECURITY_TOKEN'),
                'admin_path' => basename(_PS_ADMIN_DIR_),
                'alerts' => $alert->getAlerts(),
                'ps_version' => _PS_VERSION_,
                'help_country' => array(
                    'lang'           => $this->context->country->iso_code,
                    'module_version' => $this->version,
                    'ps_version'     => _PS_VERSION_,
                    'error_code'     => 'COUNTRY-MISSING',
                    ),
            ));

            if (Configuration::get('EBAY_COUNTRY_OK') == false) {
                $smarty_vars = array_merge($smarty_vars, array(
                    'config_country_ok' => in_array(Tools::strtolower(Country::getIsoById(Configuration::get('PS_SHOP_COUNTRY_ID'))), $this->ebay_country->accepted_isos_block),
                ));
            } else {
                $smarty_vars = array_merge($smarty_vars, array(
                    'config_country_ok' => false,
                ));
            }
        }

        $this->smarty->assign($smarty_vars);

        return $this->display(__FILE__, 'views/templates/hook/formRegister.tpl');
    }

    /**
     *
     * Waiting screen when expecting eBay login to refresh the token
     *
     */

    public function displayCheckToken()
    {
        $url_vars = array(
            'action' => 'validateToken',
            'path'   => $this->_path,
        );

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $url_vars['controller'] = Tools::getValue('controller');
        } else {
            $url_vars['tab'] = Tools::getValue('tab');
        }

        $url = _MODULE_DIR_.'ebay/ajax/checkToken.php?'.http_build_query(array(
                'token' => Configuration::get('EBAY_SECURITY_TOKEN'),
                'time'  => pSQL(date('Ymdhis')),
            ));

        $smarty_vars = array(
            'window_location_href' => $this->__getUrl($url_vars),
            'url'                  => $url,
            'request_uri'          => $_SERVER['REQUEST_URI'],
        );

        $this->smarty->assign($smarty_vars);

        return $this->display(__FILE__, 'views/templates/hook/checkToken.tpl');
    }

    /**
     * Form Config Methods
     *
     **/
    private function __displayFormConfig()
    {
        $form_parameters_tab = new EbayFormParametersTab($this, $this->smarty, $this->context);
        $form_advanced_parameters_tab = new EbayFormAdvancedParametersTab($this, $this->smarty, $this->context);
        $form_category_tab = new EbayFormCategoryTab($this, $this->smarty, $this->context, $this->_path);
        $form_items_specifics_tab = new EbayFormItemsSpecificsTab($this, $this->smarty, $this->context, $this->_path);
        $form_shipping_tab = new EbayFormShippingTab($this, $this->smarty, $this->context);
        $form_template_manager_tab = new EbayFormTemplateManagerTab($this, $this->smarty, $this->context);
        $form_ebay_sync_tab = new EbayFormEbaySyncTab($this, $this->smarty, $this->context);
        $form_ebay_order_history_tab = new EbayOrderHistoryTab($this, $this->smarty, $this->context);
        $help_tab = new EbayHelpTab($this, $this->smarty, $this->context);
        $listings_tab = new EbayListingsTab($this, $this->smarty, $this->context);
        $orders_sync = new EbayOrdersSyncTab($this, $this->smarty, $this->context);
        $ps_products = new EbayPrestashopProductsTab($this, $this->smarty, $this->context);
        $orphan_listings = new EbayOrphanListingsTab($this, $this->smarty, $this->context);
        $form_business_policies = new EbayFormBusinessPoliciesTab($this, $this->smarty, $this->context);

        $form_store_category_tab = new EbayFormStoreCategoryTab($this, $this->smarty, $this->context, $this->_path);

        $api_logs = new EbayApiLogsTab($this, $this->smarty, $this->context, $this->_path);
        $order_logs = new EbayOrderLogsTab($this, $this->smarty, $this->context, $this->_path);
        $order_returns = new EbayOrderReturnsTab($this, $this->smarty, $this->context, $this->_path);
        $orders_returns_sync = new EbayOrdersReturnsSyncTab($this, $this->smarty, $this->context);
        // test if everything is green
        if ($this->ebay_profile && $this->ebay_profile->isAllSettingsConfigured()) {
            if (!$this->ebay_profile->getConfiguration('EBAY_HAS_SYNCED_PRODUCTS')) {
                $green_message = $this->l('Your profile is ready to go, go to Synchronization to list your products');
            } elseif (!empty($_POST) && Tools::isSubmit('submitSave')) {
// config has changed
                $green_message = $this->l('To implement these changes on active listings you need to    resynchronize your items');
            }
        }

        // Get all alerts
        $alert = new EbayAlert($this);

        if ($this->ebay_profile->getConfiguration('EBAY_LAST_ALERT_MAIL') === null
            || $this->ebay_profile->getConfiguration('EBAY_LAST_ALERT_MAIL') < date('Y-m-d\TH:i:s', strtotime('-1 day')).'.000Z'
        ) {
            $alert->sendDailyMail();
            $this->ebay_profile->setConfiguration('EBAY_LAST_ALERT_MAIL', date('Y-m-d\TH:i:s').'.000Z');
        }

        $smarty_vars = array(
            'class_general'                => version_compare(_PS_VERSION_, '1.5', '>') ? 'uncinq' : 'unquatre',
            'form_parameters'              => $form_parameters_tab->getContent(),
            'form_advanced_parameters'     => $form_advanced_parameters_tab->getContent(),
            'form_category'                => $form_category_tab->getContent(),
            'form_items_specifics'         => $form_items_specifics_tab->getContent(),
            'form_shipping'                => $form_shipping_tab->getContent(),
            'form_template_manager'        => $form_template_manager_tab->getContent(),
            'form_ebay_sync'               => $form_ebay_sync_tab->getContent(),
            'orders_history'               => $form_ebay_order_history_tab->getContent(),
            'ebay_listings'                => $listings_tab->getContent(),
            'form_store_category'          => $form_store_category_tab->getContent(),
            'orders_sync'                  => $orders_sync->getContent(),
            'ps_products'                  => $ps_products->getContent(),
            'orphan_listings'              => $orphan_listings->getContent(),
            'green_message'                => isset($green_message) ? $green_message : null,
            'api_logs'                     => $api_logs->getContent(),
            'order_logs'                   => $order_logs->getContent(),
            'id_tab'                       => Tools::getValue('id_tab'),
            'alerts'                       => $alert->getAlerts(),
            'ps_version'                   => _PS_VERSION_,
            'admin_path'                   => basename(_PS_ADMIN_DIR_),
            'load_kb_path'                 => _MODULE_DIR_.'ebay/ajax/loadKB.php',
            'alert_exit_import_categories' => $this->l('Import of eBay category is running.'),
            'form_business_policies'       => $form_business_policies->getContent(),
            'order_returns'                => $order_returns->getContent(),
            'orders_returns_sync'          => $orders_returns_sync->getContent(),
        );

        $this->smarty->assign($smarty_vars);

        return $this->display(__FILE__, 'views/templates/hook/formConfig.tpl');
    }

    public function login()
    {
        $ebay = new EbayRequest();

        $session_id = $ebay->login();
        $this->context->cookie->eBaySession = $session_id;
        Configuration::updateValue('EBAY_API_SESSION', $session_id, false, 0, 0);

        return $session_id;
    }

    private function __postProcessAddProfile()
    {
        $ebay_username = Tools::getValue('eBayUsernamesList');
        if (!$ebay_username || ($ebay_username == -1)) {
            $ebay_username = Tools::getValue('eBayUsername');
        }

        if ($ebay_username) {
            $this->context->cookie->eBayUsername = $ebay_username;

            $id_shop = version_compare(_PS_VERSION_, '1.5', '>') ? Shop::getContextShopID() : Shop::getCurrentShop();

            $this->ebay_profile = EbayProfile::getByLangShopSiteAndUsername((int)Tools::getValue('ebay_language'), $id_shop, Tools::getValue('ebay_country'), $ebay_username, EbayProductTemplate::getContent($this, $this->smarty));
            EbayProfile::setProfile($this->ebay_profile->id);
        }
    }

    private function __postProcessConfig()
    {
        if ($id_ebay_profile = (int)Tools::getValue('ebay_profile')) {
            if (!EbayProfile::setProfile($id_ebay_profile)) {
                $this->html .= $this->displayError($this->l('Profile cannot be changed'));
            }
        }
    }

    private function __postProcessStats()
    {
        if (Configuration::updateValue('EBAY_SEND_STATS', Tools::getValue('stats') ? 1 : 0, false, 0, 0)) {
            $this->html .= $this->displayConfirmation($this->l('Settings updated'));
        } else {
            $this->html .= $this->displayError($this->l('Settings failed'));
        }
    }

    /**
     * Category Form Config Methods
     * @param array  $categories
     * @param int    $id
     * @param array  $path
     * @param string $path_add
     * @param string $search
     * @return array
     */
    public function getChildCategories($categories, $id, $path = array(), $path_add = '', $search = '')
    {
        $category_tab = array();

        if ($path_add != '') {
            $path[] = $path_add;
        }

        if (isset($categories[$id])) {
            $cats = $categories[$id];
        } elseif (!$id) {
            $cats = reset($categories); // fix to deal with the case where the first element of categories has no key
        }
        if (isset($cats) && $cats) {
            foreach ($cats as $idc => $cc) {
                $name = '';
                if ($path) {
                    foreach ($path as $p) {
                        $name .= $p . ' > ';
                    }
                }

                $name .= $cc['infos']['name'];
                $category_tab[] = array(
                    'id_category' => $cc['infos']['id_category'],
                    'name'        => $name,
                    'active'      => $cc['infos']['active'],
                );

                $categoryTmp = $this->getChildCategories($categories, $idc, $path, $cc['infos']['name'], '');

                $category_tab = array_merge($category_tab, $categoryTmp);
            }
        }
        if ($search) {
            $category_tab_filtered = array();
            foreach ($category_tab as $c) {
                if (strpos(Tools::strtolower($c['name']), Tools::strtolower($search)) !== false) {
                    $category_tab_filtered[] = $c;
                }
            }
            $category_tab = $category_tab_filtered;
        }

        return $category_tab;
    }

    public function ajaxProductSync()
    {
        $itemConditionError = false;
        self::addSmartyModifiers();
        $nb_products      = EbaySynchronizer::getNbSynchronizableProducts($this->ebay_profile);
        $products         = EbaySynchronizer::getProductsToSynchronize($this->ebay_profile, Tools::getValue('option'));
        $nb_products_less = EbaySynchronizer::getNbProductsLess($this->ebay_profile, Tools::getValue('option'), (int)$this->ebay_profile->getConfiguration('EBAY_SYNC_LAST_PRODUCT'));
        // Send each product on eBay
        if (count($products)) {
            $this->ebay_profile->setConfiguration('EBAY_SYNC_LAST_PRODUCT', (int)$products[0]['id_product']);
            EbaySynchronizer::syncProducts($products, $this->context, $this->ebay_profile->id_lang, 'SYNC_FROM_MODULE_BACK');

            // we cheat a bit to display a consistent number of products done
            $nb_products_done = min($nb_products - $nb_products_less + 1, $nb_products);

            echo 'KO|<div class="box_sync_ajax"><img src="'.$this->getPath().'/views/img/ajax-loader-small.gif" border="0" style="width:32px;height:32px;vertical-align:middle" /> '.$this->l('Products').' : '.$nb_products_done.' / '.$nb_products.'</div>';
        } else {
            if (file_exists(dirname(__FILE__).'/log/syncError.php')) {
                $context = Context::getContext();
                include dirname(__FILE__).'/log/syncError.php';
                if (count($context->all_error) == 0) {
                    $msg = $this->l('Settings updated').' ('.$this->l('Option').' '.$this->ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE').' : '.($nb_products - $nb_products_less).' / '.$nb_products.' '.$this->l('product(s) sync with eBay').')<br/><br/>';
                } else {
                    $msg = '';
                }

                $msg .= $this->l('Some products have been refused by eBay API due to the following error(s): ').'<br/>';

                foreach ($context->all_error as $error) {
                    $products_details = '<br /><u>'.$this->l('Product(s) concerned').' :</u>';

                    foreach ($error['products'] as $product) {
                        $products_details .= '<br />- '.$product;
                    }

                    $msg .= $error['msg'].'<br />'.$products_details;
                }

                echo 'OK|'.$this->displayError($msg);

                if ($itemConditionError) {
                    //Add a specific message for item condition error
                    $message = $this->l('The item condition value defined in your  configuration is not supported in the eBay category.').'<br/>';
                    $message .= $this->l('You can modify your item condition in the configuration settings (see supported conditions by categories here: http://pages.ebay.co.uk/help/sell/item-condition.html) ');
                    $message .= $this->l('A later version of the module will allow you to specify item conditions by category');
                    echo $this->displayError($message);
                }
                @unlink(dirname(__FILE__).'/log/syncError.php');
            } else {
                echo 'OK|'.$this->displayConfirmation($this->l('Settings updated').' ('.$this->l('Option').' '.$this->ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE').' : '.($nb_products - $nb_products_less).' / '.$nb_products.' '.$this->l('product(s) sync with eBay').')');
            }
        }
    }

    private function __relistItems()
    {
        if ($this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION') != 'GTC'
            && $this->ebay_profile->getConfiguration('EBAY_AUTOMATICALLY_RELIST') == 'on'
        ) {
            //We do relist automatically each day
            $this->ebay_profile->setConfiguration('EBAY_LAST_RELIST', date('Y-m-d'));

            $ebay = new EbayRequest();
            $days = Tools::substr($this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION'), 5);

            foreach (EbayProduct::getProducts($days, 10) as $item) {
                $new_item_id = $ebay->relistFixedPriceItem($item['itemID']);

                if (!$new_item_id) {
                    $new_item_id = $item['id_product_ref'];
                }

                //Update of the product so that we don't take it in the next 10 products to relist !
                EbayProduct::updateByIdProductRef($item['id_product_ref'], array(
                    'id_product_ref' => pSQL($new_item_id),
                    'date_upd'       => date('Y-m-d h:i:s'),
                ));
            }
        }
    }

    private function __getAlerts()
    {
        $alerts = array();

        if ($this->ebay_profile && !$this->ebay_profile->getToken()) {
            $alerts[] = 'registration';
        }

        if (!ini_get('allow_url_fopen')) {
            $alerts[] = 'allowurlfopen';
        }

        if (!extension_loaded('curl')) {
            $alerts[] = 'curl';
        }

        if (!$this->ebay_profile) {
            return $alerts;
        }

        $ebay = new EbayRequest();
        //$user_profile = $ebay->getUserProfile(Configuration::get('EBAY_API_USERNAME', null, 0, 0));
        $user_profile = $ebay->getUserProfile($this->ebay_profile->ebay_user_identifier);

        $this->StoreName = $user_profile['StoreName'];

        if ($user_profile['SellerBusinessType'][0] != 'Commercial') {
            $alerts[] = 'SellerBusinessType';
        }

        return $alerts;
    }

    public function setConfiguration($config_name, $config_value, $html = false)
    {
        return Configuration::updateValue($config_name, $config_value, $html, 0, 0);
    }

    private function __getContextShop()
    {
        switch ($context_type = Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $context_id = Shop::getContextShopID();
                break;
            case Shop::CONTEXT_GROUP:
                $context_id = Shop::getContextShopGroupID();
                break;
        }

        return array(
            $context_type,
            isset($context_id) ? $context_id : null,
        );
    }

    private function __getUrl($extra_vars = array())
    {
        $url_vars = array(
            'configure'   => Tools::getValue('configure'),
            'token'       => Tools::getValue('token'),
            'tab_module'  => Tools::getValue('tab_module'),
            'module_name' => Tools::getValue('module_name'),
        );

        return 'index.php?'.http_build_query(array_merge($url_vars, $extra_vars));
    }

    /**
     * $newContextShop = array
     * @param int $type Shop::CONTEXT_ALL | Shop::CONTEXT_GROUP | Shop::CONTEXT_SHOP
     * @param int $id ID shop if CONTEXT_SHOP or id shop group if CONTEXT_GROUP
     *
     **/
    private function __setContextShop($new_context_shop = null)
    {
        if ($new_context_shop) {
            Shop::setContext($new_context_shop[0], $new_context_shop[1]);
        } else {
            Shop::setContext(Shop::CONTEXT_SHOP, Configuration::get('PS_SHOP_DEFAULT'));
        }
    }

    public function addSqlRestrictionOnLang($alias)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            return Shop::addSqlRestrictionOnLang($alias);
        }

        return '';
    }

    /**
     * used by loadTableCategories
     *
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * used by loadTableCategories & suggestCategories
     *
     */
    public function getContext()
    {
        return $this->context;
    }

    public function ajaxPreviewTemplate($content, $id_lang)
    {
        // work around for the tinyMCE bug deleting the css line
        $css_line = '<link rel="stylesheet" type="text/css" href="'.$this->__getModuleUrl().'views/css/ebay.css" />';
        $content  = $css_line.$content;

        // random product
        $category = Category::getRootCategory($id_lang);
        $product = $category->getProducts($id_lang, 0, 1, null, null, false, true, true, 1, false);
        $product = $product[0];

        // data
        $data = array(
            'price'                   => $product['price'],
            'price_without_reduction' => '',
            'reduction'               => $product['reduction'],
            'name'                    => $product['name'],
            'description'             => $product['description'],
            'description_short'       => $product['description_short'],
        );
        if ($data['reduction'] > 0) {
            $data['price_without_reduction'] = $product['price_without_reduction'];
        }

        // pictures product
        $product = new Product($product['id_product'], false, $id_lang);
        $pictures = EbaySynchronizer::__getPictures($product, $this->ebay_profile, $id_lang, $this->context, array());
        $data['large_pictures'] = $pictures['large'];
        $data['medium_pictures'] = $pictures['medium'];

        // features product
        $features_html = '';
        foreach ($product->getFrontFeatures($id_lang) as $feature) {
            $features_html .= '<b>'.$feature['name'].'</b> : '.$feature['value'].'<br/>';
        }

        $data['features'] = $features_html;

        $content = EbaySynchronizer::fillAllTemplate($data, $content);

        echo $content;
    }

    public function displayEbayListingsAjax($id_employee = null)
    {
        $ebay                    = new EbayRequest();
        $employee                = new Employee($id_employee);
        $this->context->employee = $employee;
        $link                    = $this->context->link;
        $id_lang                 = $this->context->language->id;
        $products_ebay_listings  = array();
        $products                = EbayProduct::getProductsWithoutBlacklisted($id_lang, $this->ebay_profile->id, false);
        $data                    = array(
            'id_lang'       => $id_lang,
            'titleTemplate' => $this->ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE'),
        );

        foreach ($products as $p) {
            $data['real_id_product']   = (int)$p['id_product'];
            $data['name']              = $p['name'];
            $data['manufacturer_name'] = $p['manufacturer_name'];
            $data['reference']         = $p['reference'];
            $data['ean13']             = $p['ean13'];
            $data['upc']               = $p['upc'];
            $reference_ebay            = $p['id_product_ref'];
            $product                   = new Product((int)$p['id_product'], true, $id_lang);
            if ((int)$p['id_attribute'] > 0) {
                // No Multi Sku case so we do multiple products from a multivariation product
                $combinaison = $this->__getAttributeCombinationsById($product, (int)$p['id_attribute'], $id_lang);
                $combinaison = $combinaison[0];

                $data['reference']   = $combinaison['reference'];
                $data['ean13']       = $combinaison['ean13'];
                $data['upc']         = $combinaison['upc'];
                $variation_specifics = EbaySynchronizer::__getVariationSpecifics($combinaison['id_product'], $combinaison['id_product_attribute'], $id_lang, $this->ebay_profile->ebay_site_id);
                foreach ($variation_specifics as $variation_specific) {
                    $data['name'] .= ' ' . $variation_specific;
                }

                $products_ebay_listings[] = array(
                    'id_product'       => $combinaison['id_product'].'-'.$combinaison['id_product_attribute'],
                    'quantity'         => $combinaison['quantity'],
                    'prestashop_title' => $data['name'],
                    'ebay_title'       => EbayRequest::prepareTitle($data),
                    'reference_ebay'   => $reference_ebay,
                    'link'             => method_exists($link, 'getAdminLink') ? $link->getAdminLink('AdminProducts').'&id_product='.(int)$combinaison['id_product'].'&updateproduct' : $link->getProductLink((int)$combinaison['id_product']),
                    'link_ebay'        => EbayProduct::getEbayUrl($reference_ebay, $ebay->getDev()),
                );
            } else {
                $products_ebay_listings[] = array(
                    'id_product'       => $data['real_id_product'],
                    'quantity'         => $product->quantity,
                    'prestashop_title' => $data['name'],
                    'ebay_title'       => EbayRequest::prepareTitle($data),
                    'reference_ebay'   => $reference_ebay,
                    'link'             => method_exists($link, 'getAdminLink') ? $link->getAdminLink('AdminProducts').'&id_product='.(int)$data['real_id_product'].'&updateproduct' : $link->getProductLink((int)$data['real_id_product']),
                    'link_ebay'        => EbayProduct::getEbayUrl($reference_ebay, $ebay->getDev()),
                );
            }
        }

        $this->smarty->assign('products_ebay_listings', $products_ebay_listings);

        echo $this->display(__FILE__, 'views/templates/hook/ebay_listings_ajax.tpl');
    }

    /*
    public function displayWarning($msg)
    {
    if (method_exists($this, 'adminDisplayWarning'))
    return $this->adminDisplayWarning($msg);
    else
    return $this->context->tab->displayWarning($msg);
    }
     */

    private function __getAttributeCombinationsById($product, $id_attribute, $id_lang)
    {
        if (method_exists($product, 'getAttributeCombinationsById')) {
            return $product->getAttributeCombinationsById((int)$id_attribute, $id_lang);
        }

        $sql = 'SELECT pa.*, pa.`quantity`, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name,
                    a.`id_attribute`, pa.`unit_price_impact`
                FROM `'._DB_PREFIX_.'product_attribute` pa
                LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
                WHERE pa.`id_product` = '.(int)$product->id.'
                AND pa.`id_product_attribute` = '.(int)$id_attribute.'
                GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
                ORDER BY pa.`id_product_attribute`';

        return Db::getInstance()->ExecuteS($sql);
    }


    ############################################################################################################
    # Logger // Debug
    ############################################################################################################

    /**
     * Fonction de log
     *
     * Enregistre dans /modules/ebay/log/debug.log
     *
     * @param     $object
     * @param int $error_level
     */
    public static function debug($object, $error_level = 0, $advanced_log = false)
    {
        $error_type  = array(
            0 => "[ALL]",
            1 => "[DEBUG]",
            2 => "[INFO]",
            3 => "[WARN]",
            4 => "[ERROR]",
            5 => "[FATAL]",
        );
        $module_name = "ebay";
        $backtrace   = debug_backtrace();
        $date        = date("<Y-m-d(H:i:s)>");
        $file        = $backtrace[0]['file'].":".$backtrace[0]['line'];
        if ($advanced_log) {
            $file .= ' {';
            if (count($backtrace) > 3) {
                $file .= $backtrace[3]['class'].'::'.$backtrace[3]['function'].' -> '.$backtrace[2]['class'].'::'.$backtrace[2]['function'].' -> '.$backtrace[1]['class'].'::'.$backtrace[1]['function'].' -> '.$backtrace[0]['class'].'::'.$backtrace[0]['function'];
            } elseif (count($backtrace) > 2) {
                $file .= $backtrace[2]['function'].' -> '.$backtrace[1]['class'].'::'.$backtrace[1]['function'].' -> '.$backtrace[0]['class'].'::'.$backtrace[0]['function'];
            } elseif (count($backtrace) > 1) {
                $file .= $backtrace[1]['class'].'::'.$backtrace[1]['function'].' -> '.$backtrace[0]['class'].'::'.$backtrace[0]['function'];
            } else {
                $file .= $backtrace[0]['file'].":".$backtrace[0]['line'];
            }
            $file .= "}";
        }
        $stderr = fopen(_PS_MODULE_DIR_.'/'.$module_name.'/log/debug.log', 'a');
        fwrite($stderr, $error_type[$error_level]." ".$date." ".$file."\n".print_r($object, true)."\n\n");
        fclose($stderr);
    }
}
