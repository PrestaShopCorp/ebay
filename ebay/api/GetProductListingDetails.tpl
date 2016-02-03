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
{if ((isset($ean) && $ean != "")
    || (isset($isbn) && $isbn != "")
    || (isset($upc) && $upc != "")
    || (((isset($mpn) && $mpn != "") || isset($sku)) && isset($brand))
    || (isset($ean) && $ean != ""))
}
<ProductListingDetails>
    {*{if isset($brand) && isset($sku)}*}
        {*<BrandMPN>*}
            {*<Brand>{$brand}</Brand>*}
            {*<MPN>{$sku}</MPN>*}
        {*</BrandMPN>*}
    {*{/if}*}
    {if ((isset($mpn) && $mpn != "") && isset($brand))}
    <BrandMPN>
        {if isset($brand)}<Brand>{$brand}</Brand>{/if}
        {if isset($mpn) && $mpn != ""}<MPN>{$mpn}</MPN>{/if}
    </BrandMPN>
    {/if}
    {if isset($ean) && $ean != ""}<EAN>{$ean}</EAN>{/if}
    {*<IncludeeBayProductDetails> boolean </IncludeeBayProductDetails>*}
    {*<IncludeStockPhotoURL> boolean </IncludeStockPhotoURL>*}
    {if isset($isbn) && $isbn != ""}<ISBN>{$isbn}</ISBN>{/if}
    {*<ProductID> string </ProductID>*}
    {*<ProductReferenceID> string </ProductReferenceID>*}
    {*<ReturnSearchResultOnDuplicates> boolean </ReturnSearchResultOnDuplicates>*}
    {*<TicketListingDetails> TicketListingDetailsType*}
        {*<EventTitle> string </EventTitle>*}
        {*<PrintedDate> string </PrintedDate>*}
        {*<PrintedTime> string </PrintedTime>*}
        {*<Venue> string </Venue>*}
    {*</TicketListingDetails>*}
    {if isset($upc) && $upc != ""}<UPC>{$upc}</UPC>{/if}
    {*<UseFirstProduct> boolean </UseFirstProduct>*}
    {*<UseStockPhotoURLAsGallery> boolean </UseStockPhotoURLAsGallery>*}
</ProductListingDetails>
{elseif isset($ean_not_applicable) && $ean_not_applicable == 1}
<ProductListingDetails>
    <EAN>Does Not Apply</EAN>
</ProductListingDetails>
{/if}
