{*
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
*}

{if $categoryList === false || sizeof($categoryList) === 0}
	<tr>
		<td colspan="3" class="center">{$noCatFound|escape:'htmlall':'UTF-8'}</td>
	</tr>
{else}

    {if $nbCategories > 20}
        <div id="cat-pagination" style="display:none">
        	<p id="textPagination">{l s='Page' mod='ebay'} <span>1</span> {l s='of %s' sprintf=(($nbCategories / 20)|round:"0") mod='ebay'}</p>
        	<ul id="pagination" class="pagination">
        		<li class="prev"><</li>
        		{math equation="floor(x/20)" x=$nbCategories assign=nb_pages} 
        		{for $i=1 to ($nb_pages)}
        			<li{if $i == $p} class="current"{/if}>{$i}</li>
        		{/for}
        		<li class="next">></li>
        	</ul>
        </div>
    {/if}

	{foreach from=$categoryList key=k  item=c}
		<tr{if $k % 2 !== 0} class="alt_row"{/if} id="category-{$c.id_category|escape:'htmlall':'UTF-8'}">
			<td><a id="show-products-switch-{$c.id_category|escape:'htmlall':'UTF-8'}" showing="0" class="show-products" href="javascript:showProducts({$c.id_category|escape:'htmlall':'UTF-8'})">&#9654;</a> {$c.name|escape:'htmlall':'UTF-8'}
			</td>
			<td id="categoryPath{$c.id_category|escape:'htmlall':'UTF-8'}">
				{if isset($categoryConfigList[$c.id_category]) && isset($categoryConfigList[$c.id_category].var)}
					{$categoryConfigList[$c.id_category].var}
				{else}
					<select name="category[{$c.id_category|escape:'htmlall':'UTF-8'}]" id="categoryLevel1-{$c.id_category|escape:'htmlall':'UTF-8'}" rel="{$c.id_category|escape:'htmlall':'UTF-8'}" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch(1, {$c.id_category|escape:'htmlall':'UTF-8'});" class="ebay_select">
						<option value="0">{$noCatSelected|escape:'htmlall':'UTF-8'}</option>
						{foreach from=$eBayCategoryList item=ec}
							<option value="{$ec.id_ebay_category|escape:'htmlall':'UTF-8'}">{$ec.name|escape:'htmlall':'UTF-8'}{if $ec.is_multi_sku == 1} *{/if}</option>
						{/foreach}
					</select>
				{/if}
			</td>
            <td class="center">{if isset($getNbProducts[$c.id_category])}
				{$getNbProducts[$c.id_category]|escape:'htmlall':'UTF-8'}
				{else}0{/if}</td>
			<td>
				<select name="percent[{$c.id_category|escape:'htmlall':'UTF-8'}][sign]" class="ebay_select">
					<option{if isset($categoryConfigList[$c.id_category].percent.sign) && $categoryConfigList[$c.id_category].percent.sign == ''} selected{/if}>+</option>
					<option{if isset($categoryConfigList[$c.id_category].percent.sign) && $categoryConfigList[$c.id_category].percent.sign == '-'} selected{/if}>-</option>
				</select>
				<input type="text" size="3" maxlength="3" name="percent[{$c.id_category|escape:'htmlall':'UTF-8'}][value]" id="percent{$c.id_category|escape:'htmlall':'UTF-8'}" rel="{$c.id_category|escape:'htmlall':'UTF-8'}" style="font-size: 12px;" value="{if isset($categoryConfigList[$c.id_category]) && isset($categoryConfigList[$c.id_category].var)}{$categoryConfigList[$c.id_category].percent.value|escape:'htmlall':'UTF-8'}{/if}" />
				<select name="percent[{$c.id_category|escape:'htmlall':'UTF-8'}][type]" class="ebay_select">
					<option value="currency"{if isset($categoryConfigList[$c.id_category].percent.type) && $categoryConfigList[$c.id_category].percent.type == ''} selected{/if}>{$currencySign|html_entity_decode:2:"UTF-8"}</option>
					<option value="percent"{if isset($categoryConfigList[$c.id_category].percent.type) && $categoryConfigList[$c.id_category].percent.type == '%'} selected{/if}>%</option>
				</select>
			</td>
			<td colspan="2" class="show-products" style="text-align:center"><a  id="show-products-switch-string{$c.id_category|escape:'htmlall':'UTF-8'}" href="javascript:showProducts({$c.id_category|escape:'htmlall':'UTF-8'})">{l s='Choose products' mod='ebay'}</a></td>
            <td class="cat-nb-products center" category="{$c.id_category|escape:'htmlall':'UTF-8'}">{if isset($getNbSyncProducts[$c.id_category])}
				{$getNbSyncProducts[$c.id_category]|escape:'htmlall':'UTF-8'}
				{else}0{/if}</td>			
		</tr>
	{/foreach}
{/if}