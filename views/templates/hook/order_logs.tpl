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

{if $api_not_configured}
<div class="warning big">
 {l s='Logs are not enabled please go to advanced parameters to enable them' mod='ebay'}
</div>
{/if}

{if $nb_logs > 0}
	<p id="textStoresPagination">{l s='Page' mod='ebay'} <span>1</span> {l s='of %s' sprintf=(($nb_logs / 20)|round:"0" + 1) mod='ebay'}</p>
	<ul id="order_logs_pagination" class="pagination">
		<li class="prev"><</li>
		{for $i=0 to ($nb_logs / 20)|round:"0"}
			<li{if $i == 0} class="current"{/if}>{$i + 1}</li>
		{/for}
		<li class="next">></li>
	</ul>
{/if}

<table id="order_logs_table" class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
		<thead>
			<tr class="nodrag nodrop">
				<th>{l s='Id' mod='ebay'}</th>
				<th>{l s='Profile' mod='ebay'}</th>
				<th>{l s='eBay Order' mod='ebay'}</th>
				<th>{l s='PS Orders' mod='ebay'}</th>
				<th>{l s='Type' mod='ebay'}</th>
				<th>{l s='Success' mod='ebay'}</th>
				<th>{l s='Data' mod='ebay'}</th>
				<th>{l s='Date' mod='ebay'}</th>
				<th>{l s='Update' mod='ebay'}</th>
			</tr>
		</thead>
		<tbody>
			<tr id="removeRow">
				<td class="center" colspan="2">
					<img src="{$_path|escape:'htmlall':'UTF-8'}img/loading-small.gif" alt="" />
				</td>
			</tr>
		</tbody>
	</table>

<script type="text/javascript">
var ebay_token = '{$configs.EBAY_SECURITY_TOKEN|escape:'htmlall':'UTF-8'}';
var load_order_logs = {if $id_tab == 12}true{else}false{/if};
var order_logs_ebay_l = {ldelim}
	'No logs available' : "{l s='No logs available' mod='ebay'}",
    'You are not logged in': "{l s='You are not logged in' mod='ebay'}",
    'show': "{l s='show' mod='ebay'}"
{rdelim};
</script>
<script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/orderLogs.js?date={$date|escape:'htmlall':'UTF-8'}"></script>