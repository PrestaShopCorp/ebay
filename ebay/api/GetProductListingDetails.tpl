{*
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
*}
{if (isset($ean_not_applicable) && $ean_not_applicable == 1)}
    {assign var="does_not_apply" value="Does not apply"}
{else}
    {assign var="does_not_apply" value=""}
{/if}

{assign var="synch_product_listing_details" value=($synchronize_ean || $synchronize_isbn || $synchronize_upc || $synchronize_mpn)}

{if $synch_product_listing_details}
<ProductListingDetails>
    <BrandMPN>
        <Brand><![CDATA[{if (isset($manufacturer_name) && $manufacturer_name != "")}{$manufacturer_name|escape:'htmlall':'UTF-8'}{else}{if ($does_not_apply != "")}Unbranded{/if}{/if}]]></Brand>
        <MPN>{if (isset($mpn) && $mpn != "")}{$mpn|escape:'htmlall':'UTF-8'}{else}{$does_not_apply|escape:'htmlall':'UTF-8'}{/if}</MPN>
    </BrandMPN>
    {if ($synchronize_ean != "")}<EAN>{if (isset($ean) && $ean != "")}{$ean|escape:'htmlall':'UTF-8'}{else}{$does_not_apply|escape:'htmlall':'UTF-8'}{/if}</EAN>{/if}
    {if ($synchronize_isbn != "")}<ISBN>{if (isset($isbn) && $isbn != "")}{$isbn|escape:'htmlall':'UTF-8'}{else}{$does_not_apply|escape:'htmlall':'UTF-8'}{/if}</ISBN>{/if}
    {if ($synchronize_upc != "")}<UPC>{if (isset($upc) && $upc != "")}{$upc|escape:'htmlall':'UTF-8'}{else}{$does_not_apply|escape:'htmlall':'UTF-8'}{/if}</UPC>{/if}
</ProductListingDetails>
{/if}
