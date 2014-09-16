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
    				<td><a href="{$product.link|escape:'urlencode':'UTF-8'}" target="_blank">{$product.prestashop_title|escape:'htmlall':'UTF-8'}</a></td>
    				<td><a href="{$product.link_ebay|escape:'urlencode':'UTF-8'}"  target="_blank">{$product.ebay_title|escape:'htmlall':'UTF-8'} ({$product.reference_ebay|escape:'htmlall':'UTF-8'})</a></td>
    			</tr>
    		{/foreach}
    </table>
{else}
    <p class="center"><b>{l s='No listing with this profile' mod='ebay'}</b></p>
{/if}
