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

{if $activation_bussines == 1}

    <div>
        <p>
            <span style="font-weight: bold;"> {l s='Business Policies are activated for your account ' mod='ebay'} {$profile_name}</span></br></br>
            {l s='For more information on Business Policies, please read ' mod='ebay'} <a href="{$url_help}" target="_blank">{l s='this page on eBay site.' mod='ebay'}</a></br>
                {l s='Business Policies are composed of 3 policies : payment, return & shipping.' mod='ebay'}</br>
            {l s='- Payment and return policies should be created manually in the Sell section of My eBay, then associated to each category below.' mod='ebay'}</br>
            {l s='- Shipping policies are created automatically by this module on eBay based on shipping tab configuration : you cannot use a manually created shipping policy. You should not modify or delete automatically created shipping policy on eBay.' mod='ebay'}</br>
        </p>
        <form action="index.php?{if $isOneDotFive}controller={$controller|escape:'htmlall':'UTF-8'}{else}tab={$tab|escape:'htmlall':'UTF-8'}{/if}&configure={$configure|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&tab_module={$tab_module|escape:'htmlall':'UTF-8'}&module_name={$module_name|escape:'htmlall':'UTF-8'}&id_tab=77&section=bussinespolicies" method="post" class="form" id="configForm77">
            <input type="hidden" name="refresh_bp" value="1"/>
            <input class="button" name="submitSave" type="submit" value="{l s='Reload Business Policies' mod='ebay'}" data-inlinehelp="{l s='Click here if a Business Policy exists on eBay and is missing on PrestaShop : Business Policies list will be reloaded with list available on eBay.' mod='ebay'}" />
        </form>
    </div></br></br>

<form action="index.php?{if $isOneDotFive}controller={$controller|escape:'htmlall':'UTF-8'}{else}tab={$tab|escape:'htmlall':'UTF-8'}{/if}&configure={$configure|escape:'htmlall':'UTF-8'}&token={$token|escape:'htmlall':'UTF-8'}&tab_module={$tab_module|escape:'htmlall':'UTF-8'}&module_name={$module_name|escape:'htmlall':'UTF-8'}&id_tab=77&section=bussinespolicies" method="post" class="form" id="configForm77">
    <table class="table tableDnD" cellpadding="0" cellspacing="0" style="width: 100%;">
        <thead>
        <tr class="nodrag nodrop">
            <th style="width:30%">
                {l s='eBay category' mod='ebay'}
            </th>
            <th style="width:20%" >
                <span data-inlinehelp="{l s='Please select a return policy from payment policies available on your eBay account.' mod='ebay'}">{l s='Return policy' mod='ebay'}</span>
            </th>
            <th style="width:40%" >
                <span data-inlinehelp="{l s='Please select a payment policy from payment policies available on your eBay account.' mod='ebay'}">{l s='Payment policy' mod='ebay'}</span>
            </th>
        </tr>
        </thead>
        <tbody id="tb_business">
        {foreach from=$ebay_categories item=categories}
        <tr name="{$categories.id}">
            <td >{$categories.name}</td>
            <td style="vertical-align: top; display: none">
                <select name="return_policies[{$categories.id}]" style="width: 200px;">
                    {if empty($RETURN_POLICY)}
                    <option disabled="disabled"  value="">{l s='Please create a return policy in the Sell section of My eBay' mod='ebay'}</option>
                    {else}
                        <option  value=""></option>
                    {/if}
                    {foreach from=$RETURN_POLICY item=RETURN}
            <option value="{$RETURN.id_bussines_Policie}" >{$RETURN.name}</option>
        {/foreach}
                </select>
            </td>
            <td style="vertical-align: top;display: none">
                <select name="payement[{$categories.id}]" style="width: 200px;">
                    {if empty($PAYEMENTS)}
                    <option disabled="disabled" value="">{l s='Please create a payment policy in the Sell section of My eBay' mod='ebay'}</option>
                    {else}
                    <option  value=""></option>
                    {/if}

                    {foreach from=$PAYEMENTS item=PAYEMENT}
                        <option  value="{$PAYEMENT.id_bussines_Policie}">{$PAYEMENT.name}</option>
                    {/foreach}
                </select>
            </td>

        </tr>
        {/foreach}
        </tbody>
    </table>
    <div id="buttonEbayShipping" style="margin-top:5px;">
        <input class="primary button" name="submitSave" type="submit" id="save_ebay_shipping" value="{l s='Save and continue' mod='ebay'}"/>
    </div>

</form>
{else}

    <div class="warning big tips">
        <p>
            <span style="font-weight: bold;">{l s='Business Policies are not activated for your account ' mod='ebay'} {$profile_name}</span></br></br>
            <span style="font-weight: bold;    text-decoration: underline;">{l s='Business Policies are optional and should only be activated if you know what you are doing !' mod='ebay'}</span></br>
            {l s='For more information on Business Policies, please read ' mod='ebay'} <a href="{$url_help}" target="_blank">{l s='this page on eBay site.' mod='ebay'}</a></br></br>

            {l s='Using Business Policies step by step :' mod='ebay'}</br>

            {l s='1 - Activate Business Policies in the Sell section of My eBay,' mod='ebay'}</br>
            {l s='2 - Create payment and return policy in the Sell section of My eBay,' mod='ebay'}</br>
            {l s='3 - Setup payment and return policy in this tab,' mod='ebay'}</br>
            {l s='4 - Launch a full synchronisation.' mod='ebay'}</br>
        </p>

    </div>

{/if}

<script>

    {literal}
    $(function() {
        $('#tb_business tr').each(function (e) {

           getConfig($(this).attr('name'));

        })
    });

    function getConfig(id_category) {


    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: module_dir + 'ebay/ajax/loadTableBusinessPolicies.php',
        data: "token={/literal}{$ebay_token|escape:'urlencode'}{literal}&id_profile_ebay={/literal}{$id_profile_ebay|escape:'urlencode'}{literal}&id_category=" + id_category,
        beforeSend: function() {
        var tr ='';

        },
        success: function( data ) {
	
            $.each(data, function (key, value) {

                $('select[name="payement[' + id_category + ']"]').children('option[value="' + value["id_payment"] + '"]').attr('selected', 'selected');
                $('select[name="return_policies[' + id_category + ']"]').children('option[value="' + value["id_return"] + '"]').attr('selected', 'selected');
                $('tr[name="'+id_category+'"] td').each(function (e) {
                    $(this).show();
                });
            })
	    
	    $('tr[name="'+id_category+'"] td').show();
            }});
    }

    {/literal}
</script>
