{*
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
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $ads === false || sizeof($ads) === 0}

	<tr>
		<td colspan="3" class="center">{l s='No ad found' mod='ebay'}</td>
	</tr>

{else}

	{foreach from=$ads key=k  item=a}

		<tr{if $k % 2 !== 0} class="alt_row"{/if} id="ad-{$a.id_ebay_product|escape:'htmlall'}">

			<td>
                {if $a.id_product_ref}
                    <a style="display: block;width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis" href="{$a.link|escape:'htmlall'}" target="_blank">{$a.link|escape:'htmlall'}</a>
                {/if} 
			</td>

            <td>{if $a.exists}{$a.psProductName|escape:'htmlall'}{else}{l s='Product deleted. Id: ' mod='ebay'} {$a.exists}{/if}</td>
            
            <td class="center">{if $a.exists}{if $a.active}{l s='Yes' mod='ebay'}{else}{l s='No' mod='ebay'}{/if}{/if}</td>
            
            <td class="center">{if $a.exists}{if $a.isMultiSku}{l s='Yes' mod='ebay'}{else}{l s='No' mod='ebay'}{/if}{/if}</td>
            
            <td>{if $a.exists}{$a.category_full_name}{/if}</td>

            <td>{if $a.exists}{$a.ebay_category_full_name}{/if}</td>
            
            <td class="center">{if $a.exists}{if $a.sync}{l s='Yes' mod='ebay'}{else}{l s='No' mod='ebay'}{/if}{/if}</td>

            <td class="center">{if $a.exists}{if $a.EbayCategoryIsMultiSku}{l s='Yes' mod='ebay'}{else}{l s='No' mod='ebay'}{/if}{/if}</td>
            
            <td class="center">
                <a href="#" class="delete-orphan" ref="{$a.id_product_ref}"><img src="../img/admin/delete.gif" /></a>
            </td>
            
            <td>
                {if !$a.exists}
                    {l s='Non existing PrestaShop Product' mod='ebay'}
                {/if}
            </td>
            
	{/foreach}

{/if}