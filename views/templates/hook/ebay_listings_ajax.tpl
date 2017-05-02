{*
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $products_ebay_listings}
    <table class="table" cellpadding="0" cellspacing="0">
    	<tr>
    		<th>{l s='Id product' mod='ebay'}</th>
    		<th>{l s='Quantity' mod='ebay'}</th>
    		<th>{l s='Product on Prestashop' mod='ebay'}</th>
    		<th>{l s='Product on eBay (reference)' mod='ebay'}</th>
    	</tr>
    		{foreach from=$products_ebay_listings item=product name=loop}
    			<tr class="row_hover{if $smarty.foreach.loop.index % 2} alt_row{/if}">
    				<td style="text-align:center">{$product.id_product|escape:'htmlall':'UTF-8'}</td>
    				<td style="text-align:center">{$product.quantity|escape:'htmlall':'UTF-8'}</td>
    				<td><a href="{$product.link|escape:'htmlall':'UTF-8'}" target="_blank">{$product.prestashop_title|escape:'htmlall':'UTF-8'}</a></td>
    				<td><a href="{$product.link_ebay|escape:'htmlall':'UTF-8'}"  target="_blank">{$product.ebay_title|escape:'htmlall':'UTF-8'} ({$product.reference_ebay|escape:'htmlall':'UTF-8'})</a></td>
    			</tr>
    		{/foreach}
    </table>
{else}
    <p class="center"><b>{l s='No listing with this profile' mod='ebay'}</b></p>
{/if}
