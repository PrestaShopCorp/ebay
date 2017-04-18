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
 * International Registered Trademark & Property of PrestaShop SA
 */

include_once('EbayConfiguration.php');

class EbaySynchronizer
{
    /** @var $ebay_categories EbayCategory[] */
    private static $ebay_categories = array();

    public static function getIdProduct($product)
    {
        return $product['id_product'];
    }

    /**
     * @param array        $products
     * @param Context      $context
     * @param int          $id_lang
     * @param Context|null $request_context
     * @param bool         $log_type
     */
    public static function syncProducts($products, $context, $id_lang, $request_context = null, $log_type = false)
    {
        $logger = new EbayLogger('DEBUG');

        //Fix for orders that are passed in a country without taxes
        $context = Context::getContext();
        if ($context->cart) {
            $id_address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
            if ($id_address != 0) {
                $address             = new Address($id_address);
                $country_address     = $address->id_country;
                $address->id_country = Configuration::get('PS_COUNTRY_DEFAULT');
                $address->save();
            }
            //Fix for orders that are passed in a country without taxes
        } else {
            $logger->error("syncProducts: No cart in context", $context);
        }

        if (!$products) {
            return;
        }

        $date = date('Y-m-d H:i:s');

        // Get errors back
        if (file_exists(dirname(__FILE__).'/../log/syncError.php')) {
            include dirname(__FILE__).'/../log/syncError.php';
        }

        $tab_error = array();

        // Up the time limit
        @set_time_limit(3600);

        $product_ids = array_map(array('EbaySynchronizer', 'getIdProduct'), $products);

        if (method_exists('Cache', 'clean')) {
            Cache::clean('StockAvailable::getQuantityAvailableByProduct_*');
        }

        foreach ($products as $p) {
            if (method_exists('Hook', 'exec')) {
                Hook::exec('actionEbaySynch', array('id_product' => (int)$p['id_product'], 'id_ebay_profile' => (int)$p['id_ebay_profile']));
            } else {
                Module::hookExec('actionEbaySynch', array('id_product' => (int)$p['id_product'], 'id_ebay_profile' => (int)$p['id_ebay_profile']));
            }
            $ebay_profile = new EbayProfile((int)$p['id_ebay_profile']);
            if ($ebay_profile->id_lang == 0) {
                $ebay_profile->id_lang = Configuration::get('PS_LANG_DEFAULT');
            }
            $id_lang = ($id_lang != 0) ? $id_lang : $ebay_profile->id_lang;

            $product = new Product((int)$p['id_product'], true, $id_lang, $ebay_profile->id_shop);

            $product_configuration = EbayProductConfiguration::getByProductIdAndProfile($p['id_product'], $p['id_ebay_profile']);

            // make sure that product exists in the db and has a default category
            if (!Validate::isLoadedObject($product) || !$product->id_category_default) {
                continue;
            }

            $quantity_product = EbaySynchronizer::__getProductQuantity($product, (int)$p['id_product'], $ebay_profile);


            if (!$ebay_profile->getConfiguration('EBAY_HAS_SYNCED_PRODUCTS')) {
                $ebay_profile->setConfiguration('EBAY_HAS_SYNCED_PRODUCTS', 1);
            }

            /** @var EbayCategory $ebay_category */
            $ebay_category             = EbaySynchronizer::__getEbayCategory($product->id_category_default, $ebay_profile);
            $variations                = array();
            $prodAttributeCombinations = $product->getAttributeCombinations($id_lang);
            if (!empty($prodAttributeCombinations)) {
                $variations = EbaySynchronizer::__loadVariations($product, $ebay_profile, $context, $ebay_category);
            }
            $ebay = new EbayRequest((int)$p['id_ebay_profile'], $request_context);

            if (!$product->active || ($product_configuration && $product_configuration['blacklisted'])) {
                // try to stop sale on eBay

                $ebay = EbaySynchronizer::endProductOnEbay($ebay, $ebay_profile, $context, $id_lang, null, $product->id);

                if (!empty($ebay->error)) {
                    $tab_error = EbaySynchronizer::__updateTabError($ebay, str_replace('&', '&amp;', $product->name));
                    if ($log_type) {
                        EbayLog::write('Error: '.$ebay->error, $log_type);
                    }
                }
                continue;
            }
            $pictures = EbaySynchronizer::__getPictures($product, $ebay_profile, $id_lang, $context, $variations);

            // Load basic price
            list($price, $price_original) = EbaySynchronizer::__getPrices($product->id, $ebay_category->getPercent(), $ebay_profile);
            $conditions = $ebay_category->getConditionsValues($p['id_ebay_profile']);

            $ebay_store_category_id = pSQL(EbayStoreCategoryConfiguration::getEbayStoreCategoryIdByIdProfileAndIdCategory($ebay_profile->id, $product->id_category_default));

            // Generate array and try insert in database
            $data = array(
                'price'                  => $price,
                'quantity'               => $quantity_product,
                'categoryId'             => $ebay_category->getIdCategoryRef(),
                'variations'             => $variations,
                'pictures'               => $pictures['general'],
                'picturesMedium'         => $pictures['medium'],
                'picturesLarge'          => $pictures['large'],
                'condition'              => $conditions[$product->condition],
                'shipping'               => EbaySynchronizer::__getShippingDetailsForProduct($product, $ebay_profile),
                'id_lang'                => $id_lang,
                'real_id_product'        => (int)$p['id_product'],
                'ebay_store_category_id' => $ebay_store_category_id,
                'ean_not_applicable'     => (int)$ebay_profile->getConfiguration('EBAY_EAN_NOT_APPLICABLE'),
                'synchronize_ean'        => (string)$ebay_profile->getConfiguration('EBAY_SYNCHRONIZE_EAN'),
                'synchronize_mpn'        => (string)$ebay_profile->getConfiguration('EBAY_SYNCHRONIZE_MPN'),
                'synchronize_upc'        => (string)$ebay_profile->getConfiguration('EBAY_SYNCHRONIZE_UPC'),
                'synchronize_isbn'       => (string)$ebay_profile->getConfiguration('EBAY_SYNCHRONIZE_ISBN'),
            );
            unset($variations);
            $data = array_merge($data, EbaySynchronizer::__getProductData($product, $ebay_profile));

            // Fix hook update product
            if (Tools::getValue('id_product_attribute')) {
                $id_product_attribute_fix = (int)Tools::getValue('id_product_attribute');
                $key                      = $product->id.'-'.$id_product_attribute_fix.'_'.$ebay_profile->id;
                if (isset($data['variations'][$key]['quantity'])) {
                    $data['variations'][$key]['quantity'] = EbaySynchronizer::__fixHookUpdateProduct($context, $product->id, $data['variations'][$key]['quantity']);
                }
            }

            // Price Update
            if (isset($p['noPriceUpdate'])) {
                $data['noPriceUpdate'] = $p['noPriceUpdate'];
            }

            $clean_percent = $ebay_category->getCleanPercent();

            // Save percent and price discount
            if ($clean_percent < 0) {
                $data['price_original'] = round($price_original, 2);
//                $data['price_percent'] = round($clean_percent);
            } elseif ($price_original > $price) {
                $data['price_original'] = round($price_original, 2);
            }

            if (isset($data['price_original'])) {
                $data['price_percent'] = round(($price_original - $price) / $price_original * 100.0);
            }

            $data['description'] = EbaySynchronizer::__getEbayDescription($product, $ebay_profile, $id_lang);

            // Export to eBay
            $ebay = EbaySynchronizer::__exportProductToEbay($product, $data, $p['id_ebay_profile'], $ebay_category, $ebay, $date, $context, $id_lang);

            $handle = fopen(dirname(__FILE__).'/log-error.txt', 'w');

            if (!empty($ebay->error)) {
                // Check for errors
                $tab_error = EbaySynchronizer::__updateTabError($ebay, $data['name']);

                if ($log_type) {
                    EbayLog::write('Error: '.$ebay->error, $log_type);
                }
            } elseif ($log_type) {
                EbayLog::write('Success', $log_type);
            }

            if ($request_context == 'CRON') {
                Configuration::updateValue('NB_PRODUCTS_LAST', (int)Configuration::get('NB_PRODUCTS_LAST') + 1);
            }
            $product = null;
        }
        unset($data);
        $context = Context::getContext();
        if (count($tab_error)) {
            if (isset($context->all_error)) {
                foreach ($context->all_error as $key => $value) {
                    if (isset($tab_error[$key])) {
                        $tab_error[$key]['products'] = array_merge($context->all_error[$key]['products'], $tab_error[$key]['products']);
                    } else {
                        $tab_error[$key] = $context->all_error[$key];
                    }
                }
            }
            file_put_contents(dirname(__FILE__).'/../log/syncError.php', '<?php $context->all_error = '.var_export($tab_error, true).'; '.($ebay->itemConditionError ? '$itemConditionError = true; ' : '$itemConditionError = false;').' ?>');
        }
        unset($ebay->error);
        //Fix for orders that are passed in a country without taxes
        if ($context->cart && isset($id_address) && $id_address != 0) {
            $address->id_country = $country_address;
            $address->save();
        }
        //Fix for orders that are passed in a country without taxes
    }

    /**
     * @param Product     $product
     * @param EbayProfile $ebay_profile
     * @return array
     */
    private static function __getProductData($product, $ebay_profile)
    {
        return array(
            'id_product'         => $product->id,
            'reference'          => $product->reference,
            'name'               => str_replace('&', '&amp;', $product->name),
            'description'        => $product->description,
            'description_short'  => $product->description_short,
            'manufacturer_name'  => $product->manufacturer_name,
            'ean13'              => $product->ean13 != 0 ? (string)$product->ean13 : null,
            'upc'                => (string)$product->upc,
            'supplier_reference' => (string)$product->supplier_reference,
            'titleTemplate'      => $ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE_TITLE'),
        );
    }

    /**
     * Exports the product to eBay and updates the ebay_product table
     *
     * @param Product      $product
     * @param array        $data
     * @param int          $id_ebay_profile
     * @param EbayCategory $ebay_category
     * @param EbayRequest  $ebay
     * @param string       $date
     * @param Context      $context
     * @param int          $id_lang
     * @return EbayRequest
     */
    private static function __exportProductToEbay($product, $data, $id_ebay_profile, $ebay_category, $ebay, $date, $context, $id_lang)
    {

        $ebay_profile = new EbayProfile($id_ebay_profile);
        $id_currency  = (int)$ebay_profile->getConfiguration('EBAY_CURRENCY');

        if (count($data['variations'])) {
            // the product is multivariation
            if (EbaySynchronizer::__isProductMultiSku($ebay_category, $product->id, $id_lang, $ebay_profile->ebay_site_id)) {
                // the category accepts multisku products and there is variables matching
                $data['item_specifics'] = EbaySynchronizer::__getProductItemSpecifics($ebay_category, $product, $id_lang);
                if (isset($data['item_specifics']['K-type'])) {
                    $str = str_replace(';', ',', $data['item_specifics']['K-type']);
                    $str = str_replace(' ', '', $str);
                    $value = explode(",", $str);
                    $data['ktype'] = $value;
                    unset($data['item_specifics']['K-type']);
                }
                $data['description'] = EbaySynchronizer::__getMultiSkuItemDescription($data, $id_currency);

                if ($item_id = EbayProduct::getIdProductRef($product->id, $ebay_profile->ebay_user_identifier, $ebay_profile->ebay_site_id)) {
                    //if product already exists on eBay
                    $data['itemID'] = $item_id;
                    if (!EbaySynchronizer::__hasVariationProducts($data['variations'], $id_ebay_profile)) {
                        $ebay = EbaySynchronizer::endProductOnEbay($ebay, $ebay_profile, $context, $id_lang, $item_id);
                    } else {
                        $ebay = EbaySynchronizer::__updateMultiSkuItem($product->id, $data, $id_ebay_profile, $ebay, $date);
                    }
                } else {
                    $ebay = EbaySynchronizer::__addMultiSkuItem($product->id, $data, $id_ebay_profile, $ebay, $date);
                }
            } else {
                // No Multi Sku case so we do multiple products from a multivariation product
                $data['item_specifics'] = EbaySynchronizer::__getProductItemSpecifics($ebay_category, $product, $id_lang);

                if (isset($data['item_specifics']['K-type'])) {
                    $value         = explode(" ", $data['item_specifics']['K-type']);
                    $data['ktype'] = $value;
                    unset($data['item_specifics']['K-type']);
                }

                foreach ($data['variations'] as $variation) {
                    $data_variation = EbaySynchronizer::__getVariationData($data, $variation, $id_currency);

                    // Check if product exists on eBay
                    if ($itemID = EbayProduct::getIdProductRef($product->id, $ebay_profile->ebay_user_identifier, $ebay_profile->ebay_site_id, $data_variation['id_attribute'])) {
                        $data_variation['itemID'] = $itemID;

                        if ($data_variation['quantity'] < 1 && !EbayConfiguration::get($id_ebay_profile, 'EBAY_OUT_OF_STOCK')) {
                            // no more products
                            $ebay = EbaySynchronizer::endProductOnEbay($ebay, $ebay_profile, $context, $id_lang, $itemID);
                        } else {
                            $ebay = EbaySynchronizer::__updateItem($product->id, $data_variation, $id_ebay_profile, $ebay, $date, $data_variation['id_attribute']);
                        }
                    } else {
                        $ebay = EbaySynchronizer::__addItem($product->id, $data_variation, $id_ebay_profile, $ebay, $date, $data_variation['id_attribute']);
                    }
                }
            }
        } else {
            // the product is not a multivariation product
            $data['item_specifics'] = EbaySynchronizer::__getProductItemSpecifics($ebay_category, $product, $id_lang);
            if (isset($data['item_specifics']['K-type'])) {
                $value         = explode(" ", $data['item_specifics']['K-type']);
                $data['ktype'] = $value;
                unset($data['item_specifics']['K-type']);
            }
            $data['description'] = EbaySynchronizer::__getItemDescription($data, $id_currency);
            // Check if product exists on eBay
            if ($itemID = EbayProduct::getIdProductRef($product->id, $ebay_profile->ebay_user_identifier, $ebay_profile->ebay_site_id)) {
                $data['itemID'] = $itemID;

                // Delete or Update
                if ($data['quantity'] < 1 && !EbayConfiguration::get($id_ebay_profile, 'EBAY_OUT_OF_STOCK')) {
                    $ebay = EbaySynchronizer::endProductOnEbay($ebay, $ebay_profile, $context, $id_lang, $itemID);
                } else {
                    $ebay = EbaySynchronizer::__updateItem($product->id, $data, $id_ebay_profile, $ebay, $date);
                }
            } else {
                $ebay = EbaySynchronizer::__addItem($product->id, $data, $id_ebay_profile, $ebay, $date);
            }
        }
        unset($data);

        return $ebay;
    }

    /**
     * Returns true if the product can be sent as a multisku product on eBay, false otherwise
     * (this doesn't test if the product has variations)
     * @param EbayCategory $ebay_category
     * @param int          $product_id
     * @param int          $id_lang
     * @param int          $ebay_site_id
     * @return bool
     */
    private static function __isProductMultiSku($ebay_category, $product_id, $id_lang, $ebay_site_id)
    {
        return $ebay_category->isMultiSku() && EbaySynchronizer::__hasVariationsMatching($product_id, $id_lang, $ebay_category, $ebay_site_id);
    }

    /**
     * @param array $variations
     * @return bool
     */
    private static function __hasVariationProducts($variations, $id_ebay_profile)
    {
        foreach ($variations as $variation) {
            if ($variation['quantity'] >= 1 || EbayConfiguration::get($id_ebay_profile, 'EBAY_OUT_OF_STOCK')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int         $product_id
     * @param array       $data
     * @param int         $id_ebay_profile
     * @param EbayRequest $ebay
     * @param string      $date
     * @param int         $id_attribute
     * @return mixed
     */
    private static function __addItem($product_id, $data, $id_ebay_profile, $ebay, $date, $id_attribute = 0)
    {
        $ebay_profile = new EbayProfile($id_ebay_profile);
        $item_id      = EbayProduct::getIdProductRef($product_id, $ebay_profile->ebay_user_identifier, $ebay_profile->ebay_site_id, $id_attribute);
        if (empty($item_id)) {
            EbaySynchronizer::__insertEbayProduct($product_id, $id_ebay_profile, 0, $date, $id_attribute);
            $ebay->addFixedPriceItem($data);
            if ($ebay->itemID > 0) {
                EbayProduct::updateByIdProduct($product_id, array('id_product_ref' => pSQL($ebay->itemID)), $id_ebay_profile, $id_attribute);
            } else {
                EbayProduct::deleteByIdProduct($product_id, $id_ebay_profile, $id_attribute);
            }
        }

        return $ebay;
    }

    /**
     * @param int         $product_id
     * @param array       $data
     * @param int         $id_ebay_profile
     * @param EbayRequest $ebay
     * @param string      $date
     * @param int         $id_attribute
     * @return mixed
     */
    private static function __updateItem($product_id, $data, $id_ebay_profile, $ebay, $date, $id_attribute = 0)
    {
        if ($ebay->reviseFixedPriceItem($data)) {
            EbayProduct::updateByIdProductRef($data['itemID'], array('date_upd' => pSQL($date)));
        }

        // if product not on eBay as we expected we add it
        if ($ebay->errorCode == 291 || $ebay->errorCode == 17) {
            // We delete from DB and Add it on eBay
            EbayProduct::deleteByIdProductRef($data['itemID']);
            EbaySynchronizer::__addItem($product_id, $data, $id_ebay_profile, $ebay, $date, $id_attribute);
        }

        return $ebay;
    }

    /**
     * @param int         $product_id
     * @param array       $data
     * @param int         $id_ebay_profile
     * @param EbayRequest $ebay
     * @param string      $date
     * @return mixed
     */
    private static function __addMultiSkuItem($product_id, $data, $id_ebay_profile, $ebay, $date)
    {
        $ebay_profile = new EbayProfile($id_ebay_profile);
        $item_id      = EbayProduct::getIdProductRef($product_id, $ebay_profile->ebay_user_identifier, $ebay_profile->ebay_site_id);
        if (empty($item_id)) {
            EbaySynchronizer::__insertEbayProduct($product_id, $id_ebay_profile, 0, $date);
            $ebay->addFixedPriceItemMultiSku($data);

            if ($ebay->itemID > 0) {
                EbayProduct::updateByIdProduct($product_id, array('id_product_ref' => pSQL($ebay->itemID)), $id_ebay_profile);
            } else {
                EbayProduct::deleteByIdProduct($product_id, $id_ebay_profile, 0);
            }
        }

        return $ebay;
    }

    /**
     * @param int         $product_id
     * @param array       $data
     * @param int         $id_ebay_profile
     * @param EbayRequest $ebay
     * @param string      $date
     * @return mixed
     */
    private static function __updateMultiSkuItem($product_id, $data, $id_ebay_profile, $ebay, $date)
    {
        if ($ebay->reviseFixedPriceItemMultiSku($data)) {
            EbayProduct::updateByIdProductRef($data['itemID'], array('date_upd' => pSQL($date)));
        }

        // if product not on eBay as we expected we add it
        if ($ebay->errorCode == 291 || $ebay->errorCode == 17) {
            // We delete from DB and Add it on eBay
            EbayProduct::deleteByIdProductRef($data['itemID']);
            $ebay = EbaySynchronizer::__addMultiSkuItem($product_id, $data, $id_ebay_profile, $ebay, $date);
        }

        return $ebay;
    }

    private static function __updateTabError($ebay_error, $name)
    {
        $tab_error                    = array();
        $error_key                    = md5($ebay_error->error);
        $tab_error[$error_key]['msg'] = '<hr/>'.$ebay_error->error;

        if (!isset($tab_error[$error_key]['products'])) {
            $tab_error[$error_key]['products'] = array();
        }

        if (count($tab_error[$error_key]['products']) < 10) {
            $tab_error[$error_key]['products'][] = $name;
        }

        if (count($tab_error[$error_key]['products']) == 10) {
            $tab_error[$error_key]['products'][] = '...';
        }

        return $tab_error;
    }

    /**
     * @param Product     $product
     * @param EbayProfile $ebay_profile
     * @param int         $id_lang
     * @param Context     $context
     * @param array       $variations
     * @return array
     */
    public static function __getPictures($product, $ebay_profile, $id_lang, $context, $variations)
    {
        $pictures        = array();
        $pictures_medium = array();
        $pictures_large  = array();
        $nb_pictures     = 1 + (int)$ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING');

        $large   = new ImageType((int)$ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG'));
        $small   = new ImageType((int)$ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_SMALL'));
        $default = new ImageType((int)$ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_DEFAULT'));

        foreach (EbaySynchronizer::orderImages($product->getImages($id_lang)) as $image) {
            $pictures_default = EbaySynchronizer::__getPictureLink($product->id, $image['id_image'], $context->link, $default->name);

            if (((count($pictures) == 0) && ($nb_pictures == 1)) || self::__hasVariationProducts($variations, $ebay_profile->id)) {
                // no extra picture, we don't upload the image
                $pictures[] = $pictures_default;
            } elseif (count($pictures) < $nb_pictures) {
                // we upload every image if there are extra pictures
                $pictures[] = EbayProductImage::getEbayUrl($pictures_default, $product->name.'_'.(count($pictures) + 1));
            }

            $pictures_medium[] = EbaySynchronizer::__getPictureLink($product->id, $image['id_image'], $context->link, $small->name);
            $pictures_large[]  = EbaySynchronizer::__getPictureLink($product->id, $image['id_image'], $context->link, $large->name);

            if (count($pictures) >= 12) {
                break;
            }
        }
        // limit the numbers of picture send to ebay with the EBAY_PICTURE_PER_LISTING parameter.
        $pictures = array_slice($pictures, 0, $nb_pictures);

        return array(
            'general' => $pictures,
            'medium'  => $pictures_medium,
            'large'   => $pictures_large,
        );
    }

    /**
     * @param Product $product
     * @param int     $id_product
     * @return int
     */
    private static function __getProductQuantity(Product $product, $id_product, $ebay_profile)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $product          = new Product($id_product);
            $quantity_product = $product->quantity;
        } else {
            $quantity_product = StockAvailable::getQuantityAvailableByProduct($id_product, null, $ebay_profile->id_shop);
        }

        return $quantity_product;
    }

    /**
     * Returns the eBay category object. Check if that has been loaded before
     *
     * @param int         $category_id
     * @param EbayProfile $ebay_profile
     * @return EbayCategory
     */
    public static function __getEbayCategory($category_id, $ebay_profile)
    {
        if (!isset(EbaySynchronizer::$ebay_categories[$category_id.'_'.$ebay_profile->id])) {
            EbaySynchronizer::$ebay_categories[$category_id.'_'.$ebay_profile->id] = new EbayCategory($ebay_profile, null, $category_id);
        }

        return EbaySynchronizer::$ebay_categories[$category_id.'_'.$ebay_profile->id];
    }

    /**
     * @param Product      $product
     * @param EbayProfile  $ebay_profile
     * @param Context      $context
     * @param EbayCategory $ebay_category
     * @return array
     */
    private static function __loadVariations($product, $ebay_profile, $context, $ebay_category)
    {
        $variations   = array();
        $combinations = array();
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $combinations = $product->getAttributeCombinations($ebay_profile->id_lang);
        } else {
            $combinations = $product->getAttributeCombinaisons($ebay_profile->id_lang);
        }

        foreach ($combinations as $combinaison) {
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $context_correct_shop       = $context->cloneContext();
                $context_correct_shop->shop = new Shop($ebay_profile->id_shop);
                $specific_price_output      = null;
                $price                      = Product::getPriceStatic((int)$combinaison['id_product'], true, (int)$combinaison['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $specific_price_output, true, true, $context_correct_shop);

                $price_original = Product::getPriceStatic((int)$combinaison['id_product'], true, (int)$combinaison['id_product_attribute'], 6, null, false, false, 1, false, null, null, null, $specific_price_output, true, true, $context_correct_shop);
            } else {
                $price          = Product::getPriceStatic((int)$combinaison['id_product'], true, (int)$combinaison['id_product_attribute']);
                $price_original = Product::getPriceStatic((int)$combinaison['id_product'], true, (int)$combinaison['id_product_attribute'], 6, null, false, false);
            }

            // convert price to destination currency
            $currency = new Currency((int)$ebay_profile->getConfiguration('EBAY_CURRENCY'));
            $price *= $currency->conversion_rate;
            $price_original *= $currency->conversion_rate;

            $variation = array(
                'id_attribute'        => $combinaison['id_product_attribute'],
                'reference'           => $combinaison['reference'],
                'ean13'               => $combinaison['ean13'],
                'upc'                 => $combinaison['upc'],
                'quantity'            => $combinaison['quantity'],
                'price_static'        => $price,
                'variation_specifics' => EbaySynchronizer::__getVariationSpecifics($combinaison['id_product'], $combinaison['id_product_attribute'], $ebay_profile->id_lang, $ebay_profile->ebay_site_id, $ebay_category),
                'variations'          => array(
                    array(
                        'name'  => $combinaison['group_name'],
                        'value' => $combinaison['attribute_name'],
                    )),
            );

            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $variation['quantity'] = StockAvailable::getQuantityAvailableByProduct($product->id, $variation['id_attribute'], $ebay_profile->id_shop);
            }

            if (preg_match('#[-]{0,1}[0-9]{1,2}%$#is', $ebay_category->getPercent())) {
                $price *= (1 + ($ebay_category->getPercent() / 100));
            } else {
                $price += $ebay_category->getPercent();
            }

            $variation['price'] = round($price, 2);

            if ($ebay_category->getPercent() < 0) {
                $variation['price_original'] = round($price_original, 2);
            } elseif ($price_original > $price) {
                $variation['price_original'] = round($price_original, 2);
            }

            if (isset($variation['price_original'])) {
                $variation['price_percent'] = round(($price_original - $price) / $price_original * 100.0);
            }

            $variation_key              = $combinaison['id_product'].'-'.$combinaison['id_product_attribute'].'_'.$ebay_profile->id;
            $variations[$variation_key] = $variation;
        }

        // Load Variations Pictures
        $combination_images = $product->getCombinationImages($ebay_profile->id_lang);

        $large                   = new ImageType((int)$ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG'));
        $picture_skip_variations = (bool)$ebay_profile->getConfiguration('EBAY_PICTURE_SKIP_VARIATIONS');

        if (!$picture_skip_variations && !empty($combination_images)) {
            foreach ($combination_images as $combination_image) {
                foreach ($combination_image as $image) {
                    // If issue, it's because of https/http in the url
                    $link                                                                                            = EbaySynchronizer::__getPictureLink($product->id, $image['id_image'], $context->link, $large->name);
                    $variations[$product->id.'-'.$image['id_product_attribute'].'_'.$ebay_profile->id]['pictures'][] = $link;
                }
            }
        }
        unset($combinations);

        return $variations;
    }

    public static function getIdAttributeGroup($row)
    {
        return $row['id_attribute_group'];
    }

    /**
     * @param int          $product_id
     * @param int          $id_lang
     * @param EbayCategory $ebay_category
     * @param int          $ebay_site_id
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    private static function __hasVariationsMatching($product_id, $id_lang, $ebay_category, $ebay_site_id)
    {
        $product             = new Product($product_id);
        $attribute_groups    = $product->getAttributesGroups($id_lang);
        $attribute_group_ids = array_unique(array_map(array('EbaySynchronizer', 'getIdAttributeGroup'), $attribute_groups));

        // test if has attribute that are not can_variation, in that case => no multisku
        $nb_no_variation_attribute_groups = Db::getInstance()->getValue('SELECT COUNT(*)
            FROM `'._DB_PREFIX_.'ebay_category_specific`
            WHERE `can_variation` = 0
            AND `ebay_site_id` = '.(int)$ebay_site_id.'
            AND `id_attribute_group` IN ('.implode(', ', $attribute_group_ids).')');

        if ($nb_no_variation_attribute_groups) {
            return false;
        }

        // test if all the attribute_groups without matching are not conflicting with an item_specific name
        $category_specifics = Db::getInstance()->executeS('SELECT `id_attribute_group`
            FROM `'._DB_PREFIX_.'ebay_category_specific`
            WHERE `id_attribute_group` IN ('.implode(', ', $attribute_group_ids).')
            AND `ebay_site_id` = '.(int)$ebay_site_id);

        $with_settings_attribute_group_ids    = array_map(array('EbaySynchronizer', 'getIdAttributeGroup'), $category_specifics);
        $without_settings_attribute_group_ids = array_diff($attribute_group_ids, $with_settings_attribute_group_ids);

        foreach ($attribute_groups as $attribute_group) {
            if (!in_array($attribute_group['id_attribute_group'], $without_settings_attribute_group_ids)) {
                continue;
            }
            // Check if items specifics no variation has the same name as the attribute => multi product
            foreach ($ebay_category->getItemsSpecificValues() as $item_specific) {
                if ($item_specific['name'] === $attribute_group['group_name'] && $item_specific['can_variation'] == 0) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param int         $product_id
     * @param float       $percent
     * @param EbayProfile $ebay_profile
     * @return array
     */
    private static function __getPrices($product_id, $percent, $ebay_profile)
    {
        $context       = Context::getContext()->cloneContext();
        $context->shop = new Shop($ebay_profile->id_shop);
        // Use currency of ebay_profile
        $context->currency = new Currency($ebay_profile->getConfiguration('EBAY_CURRENCY'));

        $ebay_country = EbayCountrySpec::getInstanceByKey($ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'));
        $id_country   = Country::getByIso($ebay_country->getIsoCode());

        $context->country = new Country($id_country);

        $specific_price_output = null;
        $price                 = null;
        $price                 = Product::getPriceStatic((int)$product_id, true, null, 6, null, false, true, 1, false, null, null, null, $specific_price_output, true, true, $context);

        $specific_price_output = null;
        $price_original        = null;
        $price_original        = Product::getPriceStatic((int)$product_id, true, null, 6, null, false, false, 1, false, null, null, null, $specific_price_output, true, true, $context);

        if (preg_match('#[-]{0,1}[0-9]{1,2}%$#is', $percent)) {
            $price *= (1 + ($percent / 100));
        } else {
            $price += $percent;
        }

        $price = round($price, 2);

        return array($price, $price_original);
    }

    /**
     * @param Product     $product
     * @param EbayProfile $ebay_profile
     * @param int         $id_lang
     * @return mixed
     */
    private static function __getEbayDescription($product, $ebay_profile, $id_lang)
    {
        $features_html = '';

        foreach ($product->getFrontFeatures((int)$id_lang) as $feature) {
            $features_html .= '<b>'.$feature['name'].'</b> : '.$feature['value'].'<br/>';
        }

        return str_replace(
            array(
                '{DESCRIPTION_SHORT}',
                '{DESCRIPTION}',
                '{FEATURES}',
                '{EBAY_IDENTIFIER}',
                '{EBAY_SHOP}',
                '{SLOGAN}',
                '{PRODUCT_NAME}',
                '{REFERENCE}',
                '{BRAND}',
                '{BRAND_ID}',
            ),
            array(
                $product->description_short,
                $product->description,
                $features_html,
                $ebay_profile->ebay_user_identifier,
                $ebay_profile->getConfiguration('EBAY_SHOP'),
                '',
                $product->name,
                $product->reference,
                Manufacturer::getNameById($product->id_manufacturer),
                $product->id_manufacturer,
            ),
            $ebay_profile->getConfiguration('EBAY_PRODUCT_TEMPLATE')
        );
    }

    /**
     * @param EbayRequest $ebay
     * @param EbayProfile $ebay_profile
     * @param Context     $context
     * @param int         $id_lang
     * @param int         $ebay_item_id
     * @param int|null    $product_id
     * @return EbayRequest
     */
    public static function endProductOnEbay($ebay, $ebay_profile, $context, $id_lang, $ebay_item_id, $product_id = null)
    {
        if ($product_id) {
            $product       = new Product((int)$product_id, true, $id_lang, $ebay_profile->id_shop);
            $ebay_category = EbaySynchronizer::__getEbayCategory($product->id_category_default, $ebay_profile);
            $variations    = EbaySynchronizer::__loadVariations($product, $ebay_profile, $context, $ebay_category);

            //case where the product is multisku and could have been sent a several products
            if (count($variations) && !EbaySynchronizer::__isProductMultiSku($ebay_category, $product->id, $id_lang, $ebay_profile->ebay_site_id)) {
                foreach ($variations as $variation) {
                    if ($itemID = EbayProduct::getIdProductRef($product->id, $ebay_profile->ebay_user_identifier, $ebay_profile->ebay_site_id, $variation['id_attribute'])) {
                        $ebay->endFixedPriceItem($itemID);
                        EbayProduct::deleteByIdProductRef($itemID);
                    }
                }
            }

            return $ebay;
        }

        if (!$ebay_item_id && $product_id) {
            $ebay_item_id = EbayProduct::getIdProductRef($product_id, $ebay_profile->ebay_user_identifier, $ebay_profile->ebay_site_id);
        }

        if ($ebay_item_id) {
            $ebay->endFixedPriceItem($ebay_item_id);
            EbayProduct::deleteByIdProductRef($ebay_item_id);
        }

        return $ebay;
    }

    private static function __getPriceDescriptionStr($price, $price_percent, $id_currency)
    {
        $ebay      = new Ebay();
        $price_str = $ebay->l('instead of', 'ebay').' <del> %price_original% </del> ('.$ebay->l('promotion of', 'ebay').' %percent%%)';

        return str_replace(
            array('%price_original%', '%percent%'),
            array(Tools::displayPrice($price, $id_currency), round($price_percent)),
            $price_str
        );
    }

    private static function __getItemDescription($data, $id_currency)
    {
        $price_str = (isset($data['price_original']) ? EbaySynchronizer::__getPriceDescriptionStr($data['price_original'], $data['price_percent'], $id_currency) : '');

        return EbaySynchronizer::__fillDescription($data['description'], $data['picturesMedium'], $data['picturesLarge'], Tools::displayPrice($data['price'], $id_currency), $price_str);
    }

    private static function __getMultiSkuItemDescription($data, $id_currency)
    {
        return EbaySynchronizer::__getItemDescription($data, $id_currency);
    }

    private static function __fillDescription($description, $medium_pictures, $large_pictures, $product_price = '', $product_price_discount = '')
    {
        return str_replace(
            array('{MAIN_IMAGE}', '{MEDIUM_IMAGE_1}', '{MEDIUM_IMAGE_2}', '{MEDIUM_IMAGE_3}', '{PRODUCT_PRICE}', '{PRODUCT_PRICE_DISCOUNT}'),
            array(
                (isset($large_pictures[0]) ? '<img src="'.Tools::safeOutput($large_pictures[0]).'" class="bodyMainImageProductPrestashop" />' : ''),
                (isset($medium_pictures[1]) ? '<img src="'.Tools::safeOutput($medium_pictures[1]).'" class="bodyFirstMediumImageProductPrestashop" />' : ''),
                (isset($medium_pictures[2]) ? '<img src="'.Tools::safeOutput($medium_pictures[2]).'" class="bodyMediumImageProductPrestashop" />' : ''),
                (isset($medium_pictures[3]) ? '<img src="'.Tools::safeOutput($medium_pictures[3]).'" class="bodyMediumImageProductPrestashop" />' : ''),
                $product_price,
                $product_price_discount,
            ),
            $description
        );
    }

    private static function __insertEbayProduct($id_product, $id_ebay_profile, $ebay_item_id, $date, $id_attribute = 0)
    {
        EbayProduct::insert(array(
            'id_country'      => 8, // NOTE RArbuz: why is this hardcoded?
            'id_product'      => (int)$id_product,
            'id_attribute'    => (int)$id_attribute,
            'id_product_ref'  => pSQL($ebay_item_id),
            'date_add'        => pSQL($date),
            'date_upd'        => pSQL($date),
            'id_ebay_profile' => (int)$id_ebay_profile,
        ));

        //If eBay Product has been inserted then the configuration of eBay is OK
        Configuration::updateValue('EBAY_CONFIGURATION_OK', true);
    }

    private static function __getVariationData($data, $variation, $id_currency)
    {
        if (!empty($variation['pictures'])) {
            $data['pictures'] = $variation['pictures'];
        }

        if (!empty($variation['picturesMedium'])) {
            $data['picturesMedium'] = $variation['picturesMedium'];
        }

        if (!empty($variation['picturesLarge'])) {
            $data['picturesLarge'] = $variation['picturesLarge'];
        }

        foreach ($variation['variation_specifics'] as $variation_specific) {
            $data['name'] .= ' '.$variation_specific;
        }

        $data['price'] = $variation['price'];

        if (isset($variation['price_original'])) {
            $data['price_original'] = $variation['price_original'];
            $data['price_percent']  = $variation['price_percent'];
        }

        $data['quantity']     = $variation['quantity'];
        $data['id_attribute'] = $variation['id_attribute'];
        unset($data['variations']);
        //unset($data['variationsList']);

        // Load eBay Description
        $data['description'] = EbaySynchronizer::__fillDescription(
            $data['description'],
            $data['picturesMedium'],
            $data['picturesLarge'],
            Tools::displayPrice($data['price'], $id_currency),
            isset($data['price_original']) ? EbaySynchronizer::__getPriceDescriptionStr($data['price_original'], $data['price_percent'], $id_currency) : ''
        );

        $data['id_product'] .= '-'.(int)$data['id_attribute'];
        $data['item_specifics'] = array_merge($data['item_specifics'], $variation['variation_specifics']);

        $data['ean13']     = Tools::getIsset($variation['ean13']) ? $variation['ean13'] : null;
        $data['upc']       = Tools::getIsset($variation['ean13']) ? $variation['upc'] : null;
        $data['isbn']      = Tools::getIsset($variation['ean13']) ? $variation['isbn'] : null;
        $data['reference'] = Tools::getIsset($variation['ean13']) ? $variation['reference'] : null;

        return $data;
    }

    private static function __getShippingDetailsForProduct($product, $ebay_profile)
    {
        $national_ship      = array();
        $international_ship = array();

        //Get National Informations : service, costs, additional costs, priority
        $service_priority = 1;

        foreach (EbayShipping::getNationalShippings($ebay_profile->id, $product->id) as $carrier) {
            if (!isset($national_ship[$carrier['ebay_carrier']])) {
                $national_ship[$carrier['ebay_carrier']] = array();
            }

            $national_ship[$carrier['ebay_carrier']][] = array(
                'servicePriority'        => $service_priority,
                'serviceAdditionalCosts' => $carrier['extra_fee'],
                'serviceCosts'           => EbaySynchronizer::__getShippingPriceForProduct($product, $carrier['id_zone'], $carrier['ps_carrier']),
            );

            $service_priority++;
        }

        //Get International Informations
        $service_priority = 1;

        foreach (EbayShipping::getInternationalShippings($ebay_profile->id, $product->id) as $carrier) {
            if (!isset($international_ship[$carrier['ebay_carrier']])) {
                $international_ship[$carrier['ebay_carrier']] = array();
            }

            $international_ship[$carrier['ebay_carrier']][] = array(
                'servicePriority'        => $service_priority,
                'serviceAdditionalCosts' => $carrier['extra_fee'],
                'serviceCosts'           => EbaySynchronizer::__getShippingPriceForProduct($product, $carrier['id_zone'], $carrier['ps_carrier']),
                'locationsToShip'        => EbayShippingInternationalZone::getIdEbayZonesByIdEbayShipping($ebay_profile->id, $carrier['id_ebay_shipping']),
            );

            $service_priority++;
        }

        return array(
            'excludedZone'      => EbayShippingZoneExcluded::getExcluded($ebay_profile->id),
            'nationalShip'      => $national_ship,
            'internationalShip' => $international_ship,
        );
    }

    private static function __getPictureLink($id_product, $id_image, $context_link, $size)
    {
        //Fix for payment modules validating orders out of context, $link will not  generate fatal error.
        $link   = is_object($context_link) ? $context_link : new Link();
        $prefix = (Tools::substr(_PS_VERSION_, 0, 3) == '1.3' ? Tools::getShopDomain(true).'/' : '');

        return str_replace('https://', 'http://', $prefix.$link->getImageLink('ebay', $id_product.'-'.$id_image, $size));
    }

    private static function __getShippingPriceForProduct($product, $zone, $carrier_id)
    {
        $carrier = new Carrier($carrier_id);

        if ($carrier->shipping_method == 0) {
            // Default
            if (Configuration::get('PS_SHIPPING_METHOD') == 1) {
                // Shipping by weight
                $price = $carrier->getDeliveryPriceByWeight($product->weight, $zone);
            } else {
                // Shipping by price
                $price = $carrier->getDeliveryPriceByPrice($product->price, $zone);
            }
        } elseif ($carrier->shipping_method == 1) {
            // Shipping by weight
            $price = $carrier->getDeliveryPriceByWeight($product->weight, $zone);
        } elseif ($carrier->shipping_method == 2) {
            // Shipping by price
            $price = $carrier->getDeliveryPriceByPrice($product->price, $zone);
        } else {
            // return 0 if is an other shipping method
            return 0;
        }

        if ($carrier->shipping_handling) {
            //Add shipping handling fee
            $price += (float)Configuration::get('PS_SHIPPING_HANDLING');
        }

        $price += $price * Tax::getCarrierTaxRate($carrier_id) / 100;

        return $price;
    }

    /**
     * @param EbayProfile $ebay_profile
     * @return false|null|string
     */
    public static function getNbSynchronizableProducts($ebay_profile)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            // Retrieve total nb products for eBay (which have matched categories)
            $sql = '
                SELECT COUNT( * ) FROM (
                    SELECT COUNT(p.id_product) AS nb
                        FROM  `'._DB_PREFIX_.'product` AS p
                        INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s
                        ON p.id_product = s.id_product';
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                        ON p.id_product = ps.id_product
                        AND ps.id_shop = '.(int)$ebay_profile->id_shop;
            }
            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE s.`quantity` > 0
                AND ';
            }
            $sql .= ' ps.`id_category_default`
                    IN (
                        SELECT  `id_category`
                        FROM  `'._DB_PREFIX_.'ebay_category_configuration`
                        WHERE  `id_ebay_category` > 0
                        AND `id_ebay_category` > 0
                        AND `id_ebay_profile` = '.(int)$ebay_profile->id.
                ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') != 'A' ? ' AND `sync` = 1' : '').
                ')
                        AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($ebay_profile->id).')'.
                EbaySynchronizer::__addSqlRestrictionOnLang('s').'
                        GROUP BY p.id_product
                )TableReponse';
            $nb_products = Db::getInstance()->getValue($sql);
        } else {
            // Retrieve total nb products for eBay (which have matched categories)
            $sql = '
                SELECT COUNT(`id_product`)
                FROM `'._DB_PREFIX_.'product` AS p';
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                ON p.id_product = ps.id_product
                AND ps.id_shop = '.(int)$ebay_profile->id_shop;
            }

            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE p.`quantity` > 0
                AND ';
            }
            $sql .= ' p.`id_category_default` IN (
                    SELECT `id_category`
                    FROM `'._DB_PREFIX_.'ebay_category_configuration`
                    WHERE `id_category` > 0
                    AND `id_ebay_category` > 0
                    AND `id_ebay_profile` = '.(int)$ebay_profile->id.
                ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') != 'A' ? ' AND `sync` = 1' : '').'
                )
                AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($ebay_profile->id).')';
            $nb_products = Db::getInstance()->getValue($sql);
        }

        return $nb_products;
    }

    /**
     * @param EbayProfile $ebay_profile
     * @param int         $option
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getProductsToSynchronize($ebay_profile, $option)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $sql = '
                SELECT p.`id_product`, '.(int)$ebay_profile->id.' AS `id_ebay_profile`
                FROM  `'._DB_PREFIX_.'product` AS p
                INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s
                ON p.id_product = s.id_product
                INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                ON p.id_product = ps.id_product
                AND ps.id_shop = '.(int)$ebay_profile->id_shop;

            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE s.`quantity` > 0
                AND ';
            }
            $sql .= ' ps.`id_category_default`
                    IN (
                        SELECT  `id_category`
                        FROM  `'._DB_PREFIX_.'ebay_category_configuration`
                        WHERE  `id_category` > 0
                        AND  `id_ebay_category` > 0
                        AND  `id_ebay_profile` = '.(int)$ebay_profile->id.
                ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') != 'A' ? ' AND `sync` = 1' : '').
                ')
				'.($option == 1 ? EbaySynchronizer::__addSqlCheckProductInexistence('p') : '').'
					AND p.`id_product` > '.(int)$ebay_profile->getConfiguration('EBAY_SYNC_LAST_PRODUCT').'
					'.EbaySynchronizer::__addSqlRestrictionOnLang('s').'
				ORDER BY  p.`id_product`
				LIMIT 1';
        } else {
            $sql = '
                SELECT `id_product`, '.(int)$ebay_profile->id.' AS `id_ebay_profile`
                FROM `'._DB_PREFIX_.'product` AS p';
            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE p.`quantity` > 0
                AND ';
            }
            $sql .= ' p.`id_category_default` IN (
                    SELECT `id_category`
                    FROM `'._DB_PREFIX_.'ebay_category_configuration`
                    WHERE `id_category` > 0
                    AND `id_ebay_category` > 0
                    AND `id_ebay_profile` = '.(int)$ebay_profile->id.
                ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') != 'A' ? ' AND `sync` = 1' : '').'
                )
                '.($option == 1 ? EbaySynchronizer::__addSqlCheckProductInexistence('p') : '').'
                AND `id_product` > '.(int)$ebay_profile->getConfiguration('EBAY_SYNC_LAST_PRODUCT').'
                ORDER BY `id_product`
                LIMIT 1';
        }

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param EbayProfile $ebay_profile
     * @param int         $option
     * @param             $ebay_sync_last_product
     * @return false|null|string
     */
    public static function getNbProductsLess($ebay_profile, $option, $ebay_sync_last_product)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $sql = '
                SELECT COUNT(id_supplier) FROM(
                    SELECT id_supplier
                        FROM  `'._DB_PREFIX_.'product` AS p
                        INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s
                        ON p.id_product = s.id_product';
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $sql .= '
                        INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                        ON p.id_product = ps.id_product
                        AND ps.id_shop = '.(int)$ebay_profile->id_shop;
            }

            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE s.`quantity` > 0
                AND ';
            }
            $sql .= '   p.`active` =1
                        AND  ps.`id_category_default`
                        IN (
                            SELECT  `id_category`
                            FROM  `'._DB_PREFIX_.'ebay_category_configuration`
                            WHERE  `id_category` >0
                            AND  `id_ebay_category` >0
                            AND  `id_ebay_profile` = '.(int)$ebay_profile->id.
                ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') != 'A' ? ' AND `sync` = 1' : '').
                ')
                        '.(Tools::getValue('option') == 1 ? EbaySynchronizer::__addSqlCheckProductInexistence('p') : '').'
                        AND p.`id_product` >'.(int)$ebay_sync_last_product.'
                        AND p.`id_product` NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($ebay_profile->id).')
                        '.EbaySynchronizer::__addSqlRestrictionOnLang('s').'
                        GROUP BY p.id_product
                )TableRequete';
        } else {
            $sql = '
                SELECT COUNT(`id_product`)
                FROM `'._DB_PREFIX_.'product` AS p';
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $sql .= '
                    INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                    ON p.id_product = ps.id_product
                    AND ps.id_shop = '.(int)$ebay_profile->id_shop;
            }

            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE p.`quantity` > 0
                AND ';
            }
            $sql .= ' p.`active` = 1
                AND p.`id_category_default` IN (
                    SELECT `id_category`
                    FROM `'._DB_PREFIX_.'ebay_category_configuration`
                    WHERE `id_category` > 0
                    AND `id_ebay_category` > 0
                    AND `id_ebay_profile` = '.(int)$ebay_profile->id.
                ($ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') != 'A' ? ' AND `sync` = 1' : '').'
                )
                '.(Tools::getValue('option') == 1 ? EbaySynchronizer::__addSqlCheckProductInexistence('p') : '').'
                AND p.`id_product` > '.(int)$ebay_sync_last_product.'
                AND p.`id_product` NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($ebay_profile->id).')';
        }

        return Db::getInstance()->getValue($sql);
    }

    /**
     * @param EbayCategory $ebay_category
     * @param Product      $product
     * @param int          $id_lang
     * @return array
     */
    private static function __getProductItemSpecifics($ebay_category, $product, $id_lang)
    {
        $item_specifics       = $ebay_category->getItemsSpecificValues();
        $item_specifics_pairs = array();

        foreach ($item_specifics as $item_specific) {
            $value = null;
            if ($item_specific['id_feature']) {
                $value = EbaySynchronizer::__getFeatureValue($product->id, $item_specific['id_feature'], $id_lang);
            } elseif ($item_specific['is_brand']) {
                $value = $product->manufacturer_name;
            } elseif ($item_specific['is_reference']) {
                $value = $product->reference;
            } elseif ($item_specific['is_ean']) {
                $value = $product->ean13;
            } elseif ($item_specific['is_upc']) {
                $value = $product->upc;
            } else {
                $value = $item_specific['specific_value'];
            }

            if ($value) {
                $item_specifics_pairs[$item_specific['name']] = $value;
            }
        }

        return $item_specifics_pairs;
    }

    /**
     * @param int $id_product
     * @param int $id_attribute_group
     * @param int $id_lang
     * @return false|null|string
     */
    private static function __getAttributeValue($id_product, $id_attribute_group, $id_lang)
    {
        return Db::getInstance()->getValue('SELECT al.`name`
            FROM `'._DB_PREFIX_.'attribute_lang` al
            INNER JOIN `'._DB_PREFIX_.'attribute` a
            ON al.`id_attribute` = a.`id_attribute`
            AND a.`id_attribute_group` = '.(int)$id_attribute_group.'
            INNER JOIN `'._DB_PREFIX_.'product_attribute_combination` pac
            ON a.`id_attribute` = pac.`id_attribute`
            INNER JOIN `'._DB_PREFIX_.'product_attribute` pa
            ON pac.`id_product_attribute` = pa.`id_product_attribute`
            AND pa.`id_product` = '.(int)$id_product.'
            WHERE al.`id_lang` = '.(int)$id_lang);
    }

    /**
     * @param int $id_product
     * @param int $id_feature
     * @param int $id_lang
     * @return false|null|string
     */
    private static function __getFeatureValue($id_product, $id_feature, $id_lang)
    {
        return Db::getInstance()->getValue('SELECT fvl.`value`
            FROM `'._DB_PREFIX_.'feature_value_lang` fvl
            INNER JOIN `'._DB_PREFIX_.'feature_value` fv
            ON fvl.`id_feature_value` = fv.`id_feature_value`
            INNER JOIN `'._DB_PREFIX_.'feature_product` fp
            ON fv.`id_feature_value` = fp.`id_feature_value`
            AND fp.`id_feature` = '.(int)$id_feature.'
            AND fp.`id_product` = '.(int)$id_product.'
            WHERE fvl.`id_lang` = '.(int)$id_lang);
    }

    /**
     * @param Context $context
     * @param int     $product_id
     * @param int     $quantity
     * @return int
     */
    private static function __fixHookUpdateProduct($context, $product_id, $quantity)
    {
        if (isset($context->employee)
            && (int)$context->employee->id
            && Tools::getValue('submitProductAttribute')
            && Tools::getValue('attribute_mvt_quantity')
            && Tools::getValue('id_mvt_reason')
        ) {
            $id_product_attribute_fix = (int)Tools::getValue('id_product_attribute');
            $key                      = $product_id.'-'.$id_product_attribute_fix;

            if (Tools::substr(_PS_VERSION_, 0, 3) == '1.3') {
                $quantity_fix = (int)Tools::getValue('attribute_quantity');
                if ($id_product_attribute_fix > 0 && $quantity_fix > 0) {
                    $quantity = (int)$quantity_fix;
                }
            } else {
                $action       = Db::getInstance()->getValue('SELECT `sign`
                    FROM `'._DB_PREFIX_.'stock_mvt_reason`
                    WHERE `id_stock_mvt_reason` = '.(int)Tools::getValue('id_mvt_reason'));
                $quantity_fix = (int)Tools::getValue('attribute_mvt_quantity');

                if ($id_product_attribute_fix > 0
                    && $quantity_fix > 0
                    && $action
                ) {
                    $quantity += (int)$action * (int)$quantity_fix;
                }
            }
        }

        return $quantity;
    }

    /**
     * Returns the item specifics that correspond to a variation and not to the product in general
     *
     * @param int               $product_id
     * @param int               $product_attribute_id
     * @param int               $id_lang
     * @param int               $ebay_site_id
     * @param EbayCategory|bool $ebay_category
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function __getVariationSpecifics($product_id, $product_attribute_id, $id_lang, $ebay_site_id, $ebay_category = false)
    {
        $variation_specifics_pairs = array();

        $sql = '
            SELECT agl.name AS name, al.name AS value
            FROM '._DB_PREFIX_.'product_attribute_combination pac
            JOIN '._DB_PREFIX_.'attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang='.(int)$id_lang.')
            JOIN '._DB_PREFIX_.'attribute a
            ON a.id_attribute = al.id_attribute
            JOIN '._DB_PREFIX_.'attribute_group_lang agl
            ON a.id_attribute_group = agl.id_attribute_group
            AND agl.id_lang = '.(int)$id_lang.'
            LEFT JOIN '._DB_PREFIX_.'ebay_category_specific ecs
            ON a.id_attribute_group = ecs.id_attribute_group
            AND ecs.`ebay_site_id` = '.(int)$ebay_site_id.'
            WHERE pac.id_product_attribute='.(int)$product_attribute_id;

        if ($ebay_category !== false) {
            $sql .= '  AND (ecs.id_category_ref = '.(int)$ebay_category->getIdCategoryRef().' OR ecs.id_category_ref IS NULL)';
        }

        $attributes_values         = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $variation_specifics_pairs = array();

        foreach ($attributes_values as $attribute_value) {
            $variation_specifics_pairs[$attribute_value['name']] = $attribute_value['value'];
        }

        return $variation_specifics_pairs;
    }

    private static function __addSqlRestrictionOnLang($alias)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            Shop::addSqlRestrictionOnLang($alias);
        }
    }

    private static function __addSqlCheckProductInexistence($alias = null)
    {
        return 'AND '.($alias ? $alias.'.' : '').'`id_product` NOT IN (
			SELECT `id_product`
			FROM `'._DB_PREFIX_.'ebay_product`
		)';
    }

    /**
     * If there is a cover puts it at the top of the list
     * otherwise returns the images in their position order
     *
     * @param array $images
     * @return array
     */
    private static function orderImages($images)
    {
        $covers = array();

        foreach ($images as $key => $image) {
            if ($image['cover']) {
                $covers[] = $image;
                unset($images[$key]);
            }
        }

        return array_merge($covers, $images);
    }

    public static function fillAllTemplate($data, $description)
    {
        return str_replace(
            array(
                '{MAIN_IMAGE}',
                '{MEDIUM_IMAGE_1}',
                '{MEDIUM_IMAGE_2}',
                '{MEDIUM_IMAGE_3}',
                '{PRODUCT_PRICE}',
                '{PRODUCT_PRICE_DISCOUNT}',
                '{DESCRIPTION_SHORT}',
                '{DESCRIPTION}',
                '{FEATURES}',
                '{EBAY_IDENTIFIER}',
                '{EBAY_SHOP}',
                '{SLOGAN}',
                '{PRODUCT_NAME}',
            ),
            array(
                (isset($data['large_pictures'][0]) ? '<img src="'.Tools::safeOutput($data['large_pictures'][0]).'" class="bodyMainImageProductPrestashop" />' : ''),
                (isset($data['medium_pictures'][1]) ? '<img src="'.Tools::safeOutput($data['medium_pictures'][1]).'" class="bodyFirstMediumImageProductPrestashop" />' : ''),
                (isset($data['medium_pictures'][2]) ? '<img src="'.Tools::safeOutput($data['medium_pictures'][2]).'" class="bodyMediumImageProductPrestashop" />' : ''),
                (isset($data['medium_pictures'][3]) ? '<img src="'.Tools::safeOutput($data['medium_pictures'][3]).'" class="bodyMediumImageProductPrestashop" />' : ''),
                $data['price'],
                $data['price_without_reduction'],
                $data['description_short'],
                $data['description'],
                $data['features'],
                Configuration::get('EBAY_IDENTIFIER'),
                Configuration::get('EBAY_SHOP'),
                Configuration::get('PS_SHOP_NAME'),
                $data['name'],
            ),
            $description
        );
    }

    /**
     *
     * @param array  $tags
     * @param array  $values
     * @param string $description
     * @return string
     */
    public static function fillTemplateTitle($tags, $values, $description)
    {
        return str_replace($tags, $values, $description);
    }

    public static function getNbSynchronizableEbayCategorie($id_ebay_profile)
    {
        return Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM  `'._DB_PREFIX_.'ebay_category_configuration`
            WHERE  `id_ebay_category` > 0
            AND `id_ebay_category` > 0
            AND `id_ebay_profile` = '.(int)$id_ebay_profile
        );
    }

    public static function getNbSynchronizableEbayShipping()
    {
        return Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM  `'._DB_PREFIX_.'ebay_shipping`'
        );
    }

    public static function getNbSynchronizableEbayShippingInternational()
    {
        return Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM  `'._DB_PREFIX_.'ebay_shipping_international_zone`'
        );
    }

    public static function getNbSynchronizableEbayCategoryCondition()
    {
        return Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM  `'._DB_PREFIX_.'ebay_category_condition_configuration`'
        );
    }

    public static function getNbSynchronizableEbayCategoryConditionMixed()
    {
        return Db::getInstance()->getValue(
            'SELECT COUNT(*)
            FROM  `'._DB_PREFIX_.'ebay_category_condition_configuration`
            WHERE `id_condition_ref` != 1000'
        );
    }

    /**
     * Get Number Products to Sync
     *
     * @param int    $id_ebay_profile
     * @param string $config_ebay (A : all categories | B : chosen categories)
     * @return int
     */
    public static function getNbProductsSync($id_ebay_profile = -1, $config_ebay = "")
    {
        $id_ebay_profile = $id_ebay_profile == -1 ? (int)Tools::getValue('profile') : $id_ebay_profile;
        $ebay_profile    = new EbayProfile($id_ebay_profile);
        $config_ebay     = $config_ebay == "" ? $ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE') : $config_ebay;

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $sql = 'SELECT COUNT(*) AS nb FROM(
                SELECT p.id_product
                FROM '._DB_PREFIX_.'product AS p
                INNER JOIN '._DB_PREFIX_.'stock_available AS s
                ON s.id_product = p.id_product';
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                    ON p.id_product = ps.id_product
                    AND ps.id_shop = '.(int)$ebay_profile->id_shop;
            }
            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE s.`quantity` > 0
                AND ';
            }
            $sql .= ' p.`active` = 1
                AND ps.`id_category_default` IN (
                    SELECT `id_category`
                    FROM `'._DB_PREFIX_.'ebay_category_configuration`
                    WHERE `id_ebay_category` > 0';
            if ($config_ebay == 'B') {
                $sql .= ' AND `sync` = 1 ';
            }
            $sql .= ' AND `id_ebay_profile` = '.(int)$ebay_profile->id.')
                AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($ebay_profile->id).')
                GROUP BY p.id_product) TableRequete';
            $nb_products = Db::getInstance()->getValue($sql);
        } else {
            $sql = 'SELECT COUNT(`id_product`) as nb
                FROM `'._DB_PREFIX_.'product` AS p';
            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $sql .= ' INNER JOIN  `'._DB_PREFIX_.'product_shop` AS ps
                    ON p.id_product = ps.id_product
                    AND ps.id_shop = '.(int)$ebay_profile->id_shop;
            }
            if (EbayConfiguration::get($ebay_profile->id, 'EBAY_OUT_OF_STOCK')) {
                $sql .= ' WHERE ';
            } else {
                $sql .= ' WHERE p.`quantity` > 0
                AND ';
            }
            $sql .= ' p.`active` = 1
                AND p.`id_category_default` IN (
                    SELECT `id_category`
                    FROM `'._DB_PREFIX_.'ebay_category_configuration`
                    WHERE `id_ebay_category` > 0';
            if ($config_ebay == 'B') {
                $sql .= ' AND `sync` = 1 ';
            }
            $sql .= ' AND `id_ebay_profile` = '.(int)$ebay_profile->id.')
                AND p.id_product NOT IN ('.EbayProductConfiguration::getBlacklistedProductIdsQuery($ebay_profile->id).')';
            $nb_products = Db::getInstance()->getValue($sql);
        }

        return (int)$nb_products;
    }
}
