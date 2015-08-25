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

class EbayCategoryConfiguration
{

	/**
	 * Returns the query to retrieve the PrestaShop categories ready to be synchornized
	 * Will only retrieve categories that have an Ebay equivalent
	 * Depends on the sync mode
	 *
	 * @param array $params hook parameters
	 **/
	public static function getCategoriesQuery($ebay_profile)
	{
		$sync_mode = $ebay_profile->getConfiguration('EBAY_SYNC_PRODUCTS_MODE');
		
		$sql = 'SELECT `id_category`
				FROM `'._DB_PREFIX_.'ebay_category_configuration`
				WHERE `id_category` > 0
				AND `id_ebay_category` > 0
				AND `id_ebay_profile` = '.(int)$ebay_profile->id;

		if ($sync_mode == 'B')
			$sql .= ' AND `sync` = 1';

		return $sql;
	}

	/**
	 * Returns the product ids of all product for which the category is matched with an eBay category
	 *
	 */
	public static function getAllProductIds($id_ebay_profile)
	{
		$res = Db::getInstance()->executeS('SELECT `id_product`
			FROM `'._DB_PREFIX_.'category_product` c
			WHERE c.`id_category`
			IN (
				SELECT e.`id_category`
				FROM `'._DB_PREFIX_.'ebay_category_configuration` e
				WHERE e.`id_ebay_profile` = '.(int)$id_ebay_profile.'
			)');

		return array_map(array('EbayCategoryConfiguration', 'getAllProductIdsMap'), $res);
	}

	public static function getAllProductIdsMap($row)
	{
		return $row['id_product'];
	}

	/**
	 * Returns the eBay category ids
	 *
	 **/
	public static function getEbayCategoryIds($id_ebay_profile)
	{
		$sql = 'SELECT
			DISTINCT(ec.`id_category_ref`) as id
			FROM `'._DB_PREFIX_.'ebay_category_configuration` e
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec
			ON e.`id_ebay_category` = ec.`id_ebay_category`
			WHERE e.`id_ebay_profile` = '.(int)$id_ebay_profile.' 
			AND ec.`id_category_ref` is not null';

		$res = Db::getInstance()->executeS($sql);

		return array_map(array('EbayCategoryConfiguration', 'getEbayCategoryIdsMap'), $res);
	}

	public static function getEbayCategoryIdsMap($row)
	{
		return $row['id'];
	}

	/**
	 * Returns the eBay category id and the full name including the name of the parent and the grandparent category
	 *
	 **/
	public static function getEbayCategories($id_ebay_profile)
	{
		$ebay_profile = new EbayProfile($id_ebay_profile);
		$ebay_site_id = $ebay_profile->ebay_site_id;
			
		$sql = 'SELECT
			DISTINCT(ec1.`id_category_ref`) as id,
			CONCAT(
				IFNULL(ec3.`name`, \'\'),
				IF (ec3.`name` is not null, \' > \', \'\'),
				IFNULL(ec2.`name`, \'\'),
				IF (ec2.`name` is not null, \' > \', \'\'),
				ec1.`name`
			) as name
			FROM `'._DB_PREFIX_.'ebay_category_configuration` e
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec1
			ON e.`id_ebay_category` = ec1.`id_ebay_category`
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec2
			ON ec1.`id_category_ref_parent` = ec2.`id_category_ref`
			AND ec1.`id_category_ref_parent` <> \'1\'
			AND ec1.level <> 1
			AND ec2.`id_country` = '.(int)$ebay_site_id.'
			LEFT JOIN `'._DB_PREFIX_.'ebay_category` ec3
			ON ec2.`id_category_ref_parent` = ec3.`id_category_ref`
			AND ec2.`id_category_ref_parent` <> \'1\'
			AND ec2.level <> 1
			AND ec3.`id_country` = '.(int)$ebay_site_id.'            
			WHERE e.`id_ebay_profile` = '.(int)$id_ebay_profile.'
			AND ec1.`id_category_ref` is not null';
			
		return Db::getInstance()->executeS($sql);
	}
	
	/*
	 *
	 * get categories for which some multi variation product on PS were added to a non multi sku categorie on ebay
	 *
	 **/
	public static function getMultiVarToNonMultiSku($ebay_profile, $context)
	{
		$cat_with_problem = array();

		$sql_get_cat_non_multi_sku = 'SELECT * FROM '._DB_PREFIX_.'ebay_category_configuration AS ecc
			INNER JOIN '._DB_PREFIX_.'ebay_category AS ec ON ecc.id_ebay_category = ec.id_ebay_category
			WHERE ecc.id_ebay_profile = '.(int)$ebay_profile->id.' GROUP BY name';

		foreach (Db::getInstance()->ExecuteS($sql_get_cat_non_multi_sku) as $cat)
		{
			if ($cat['is_multi_sku'] != 1 && EbayCategory::getInheritedIsMultiSku($cat['id_category_ref'], $ebay_profile->ebay_site_id) != 1)
			{
				$catProblem = 0;
				$category = new Category($cat['id_category']);
				$ebay_country = EbayCountrySpec::getInstanceByKey($ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'));
				$products = $category->getProductsWs($ebay_country->getIdLang(), 0, 300);

				foreach ($products as $product_ar)
				{
					$product = new Product($product_ar['id']);
					$combinations = version_compare(_PS_VERSION_, '1.5', '>') ? $product->getAttributeCombinations($context->cookie->id_lang) : $product->getAttributeCombinaisons($context->cookie->id_lang);

					if (count($combinations) > 0 && !$catProblem)
					{
						$cat_with_problem[] = $cat['name'];
						$catProblem = 1;
					}
				}
			}
		}
		
		return $cat_with_problem;
		
	}


	public static function add($data)
	{
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', $data, 'INSERT');
	}

	public static function updateByIdProfile($id_profile, $data)
	{
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', $data, 'UPDATE', '`id_ebay_profile` = '.(int)$id_profile);
	}

	public static function updateByIdProfileAndIdCategory($id_profile, $id_category, $data)
	{
		Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category_configuration', $data, 'UPDATE', '`id_ebay_profile` = '.(int)$id_profile.' AND `id_category` = '.(int)$id_category);
	}

	public static function deleteByIdCategory($id_ebay_profile, $id_category)
	{
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.' AND `id_category` = '.(int)$id_category);
	}

	public static function getEbayCategoryConfigurations($id_ebay_profile)
	{
		return Db::getInstance()->executeS('SELECT *
			FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile);
	}

	public static function getTotalCategoryConfigurations($id_ebay_profile)
	{
		return Db::getInstance()->getValue('SELECT COUNT(`id_ebay_category_configuration`)
			FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile);
	}

	public static function getIdByCategoryId($id_ebay_profile, $id_category)
	{
		return Db::getInstance()->getValue('SELECT `id_ebay_category_configuration`
			FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.'
			AND `id_category` = '.(int)$id_category);
	}
	
	public static function getNbPrestashopCategories($id_ebay_profile)
	{
		return Db::getInstance()->getValue('SELECT count(*)
			FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile);
	}    

	public static function getNbEbayCategories($id_ebay_profile)
	{
		return Db::getInstance()->getValue('SELECT count( DISTINCT(`id_ebay_category`))
			FROM `'._DB_PREFIX_.'ebay_category_configuration`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile);
	}    

	public static function getImpactPrices($id_ebay_profile)
	{
		return array(
			'positive_impact' => Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ebay_category_configuration WHERE percent NOT LIKE "-%" AND percent IS NOT NULL AND percent != "" AND id_ebay_profile = '.(int)$id_ebay_profile),
			'negative_impact' =>  Db::getInstance()->getValue('SELECT COUNT(*) FROM '._DB_PREFIX_.'ebay_category_configuration WHERE percent LIKE "-%" AND id_ebay_profile = '.(int)$id_ebay_profile),
			'impacts' => Db::getInstance()->ExecuteS('SELECT percent FROM '._DB_PREFIX_.'ebay_category_configuration WHERE percent IS NOT NULL AND percent != "" AND id_ebay_profile = '.(int)$id_ebay_profile),
		);
	}

}