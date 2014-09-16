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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $relogin}

	<script>
		$(document).ready(function() {ldelim}
				win = window.location.href = '{$redirect_url|escape:'urlencode'}';
		{rdelim});
	</script>
{/if}

<fieldset class="new">
	<legend>{l s='Register the module on eBay' mod='ebay'}</legend>

{if $logged}
{$check_token_tpl}
{else}
	<style>
		{literal}
		.ebay_dl {margin: 0 0 10px 40px}
		.ebay_dl > * {float: left; margin: 10px 0 0 10px}
		.ebay_dl > dt {min-width: 100px; display: block; clear: both; text-align: left}
		#ebay_label {font-weight: normal; float: none}
		#button_ebay{background-image:url({/literal}{$path|escape:'htmlall'}{literal}views/img/ebay.png);background-repeat:no-repeat;background-position:center 90px;width:385px;height:191px;cursor:pointer;padding-bottom:70px;font-weight:bold;font-size:22px}
	input.primary {
		text-shadow: none;
		background: -webkit-gradient(linear, center top ,center bottom, from(#0055FF), to(#0055AA)) repeat scroll 0 0 transparent;
		background: -moz-gradient(linear, center top ,center bottom, from(#0055FF), to(#0055AA)) repeat scroll 0 0 transparent;
		color: white;
	}
	</style>
	<script>
        {/literal}
        var ebay_profiles = [
        {foreach from=$ebay_profiles item='profile'}
          {literal}{identifier: {/literal}"{$profile.ebay_user_identifier}", country: "{$profile.site_extension}"{literal}}{/literal}{if not $smarty.foreach.foo.last},{/if}
        {/foreach}
        ];
        {literal}
    
		$(document).ready(function() {
			$('#ebayRegisterButton').click(function(event) {
				if ($('#eBayUsername').val() == '')
				{
					alert("{/literal}{l s='Please enter your eBay user ID' mod='ebay'}{literal}");
					return false;
				}
				else{
                    
					var country = $("#ebay_countries").val();
					var link = $("option[value=" + country + "]").data("signin");
                    
                    var username = $('#eBayUsernamesList').val();
                    if (username == -1)
                        username = $('#eBayUsername').val();
                    
                        console.log(ebay_profiles);
                        
                    var exists = false;
                    for (var i in ebay_profiles) {
                        var ebay_profile = ebay_profiles[i];
                        if ((country == ebay_profile.country) &&
                        (username == ebay_profile.identifier)) {
                            exists = true;
                            break;
                        }
                    }
                    
                    if (exists) {
                        alert("{/literal}{l s='An account with this identifier and this eBay site already exists' mod='ebay'}{literal}");
                        return false;
                    }
                    
                    window.open(link + "{/literal}{$window_open_url|escape:'urlencode'}{literal}");
				}
			});
		});
		{/literal}
	</script>
	<form action="{$action_url|escape:'urlencode'}" method="post">
        <div id="ebay-register-content">
            <div id="ebay-register-left-col">
                <div id="ebay-register-left-col-content">
            		<strong id="ebay-register-title">{l s='Link your eBay account to the eBay add-on' mod='ebay'}</strong>
                    <p id="ebay-register-subtitle"><strong>{l s='I have a professional eBay account:' mod='ebay'}</strong></p>
                
                    <div class="ebay-register-p">               
                		<label class="ebay-label" for="eBayUsername">{l s='eBay User ID' mod='ebay'}</label>
                        {if $ebay_user_identifiers|count}
                            <select id="eBayUsernamesList" name="eBayUsernamesList" class="ebay_select ebay-float-right">
                                {foreach from=$ebay_user_identifiers item='profile'}
                                    <option value="{$profile.identifier|escape:'htmlall'}">{$profile.identifier|escape:'htmlall'}</option>
                                {/foreach}
                                <option value="-1">New eBay user</option>
                            </select>
                			<input id="eBayUsernameInput" type="text" name="eBayUsername" value="" />
                        {else}
            			    <input id="eBayUsernameInput" type="text" name="eBayUsername" class="ebay-float-right" value="" />
                        {/if}
                        <div style="clear:both"></div>                             
                    </div>
                
                    <div class="ebay-register-p">
                        <label class="ebay-label" for="ebay_countries">{l s='Choose ebay site you want to listen' mod='ebay'}</label>
            			<select name="ebay_country" id="ebay_countries" class="ebay_select ebay-float-right">
            				{if isset($ebay_countries) && $ebay_countries && sizeof($ebay_countries)}
            					{foreach from=$ebay_countries item='country' key='key'}
            						<option value="{$key|escape:'htmlall'}" data-signin="{$country.signin|escape:'htmlall'}" {if $key == $default_country} selected{/if}>{if $country.subdomain}{$country.subdomain|escape:'htmlall'}.{/if}ebay.{$country.site_extension|escape:'htmlall'}</option>
            					{/foreach}
            				{/if}
            			</select>
                        <div style="clear:both"></div>
                    </div>

                    <div class="ebay-register-p">
                        <label class="ebay-label" for="ebay_languages">{l s='Choose the language you want to list with:' mod='ebay'}</label>
            			<select name="ebay_language" id="ebay_languages" class="ebay_select ebay-float-right">
            				{if isset($languages) && $languages && sizeof($languages)}
            					{foreach from=$languages item='language' key='key'}
            						<option value="{$language.id_lang|escape:'htmlall'}">{$language.name|escape:'htmlall'}</option>
            					{/foreach}
            				{/if}
            			</select>
                        <div style="clear:both"></div>                        
                    </div>

            		<div class="margin-form">
            			<input type="submit" id="ebayRegisterButton" name="ebayRegisterButton" class="button ebay-float-right" value="{l s='Link your ebay account' mod='ebay'}" />
            		</div>
            		<div class="clear both"></div>
                </div>
            </div>
            <div id="ebay-register-right-col">
                <div id="ebay-register-right-col-content">
                    <div id="ebay-register-div">
                        <a id="ebay-register-link" href="{l s='https://scgi.ebay.com/ws/eBayISAPI.dll?RegisterEnterInfo' mod='ebay'}" class="ebay-primary button">{l s='Register' mod='ebay'}</a>
                		<strong>{l s='New to eBay?' mod='ebay'}</strong><br />
                        Get started now, Its fast and easy.
                    </div>
                    <p id="ebay-register-p">Once you have registered on eBay you will obtain the eBay ID 
                    required to configure the eBay add-on.</p>
                    
                    <!--
            		<br /><br />
            		<br /><u><a href="{l s='http://pages.ebay.com/help/sell/businessfees.html' mod='ebay'}" target="_blank">{l s='Review the eBay business seller fees page' mod='ebay'}</a></u>
            		<br />{l s='Consult our "Help" section for more information' mod='ebay'}
                    -->
                </div>
            </div>
        </div>
	</form>
{/if}
</fieldset>
<script type="text/javascript">
    function checkeBayUsernameSelect() {
        var val = $('#eBayUsernamesList').val();
        if ((val == undefined) || (val == -1)) {
            $('#eBayUsernameInput').show();
        } else {
            $('#eBayUsernameInput').hide();
        }        
    }

    $(document).ready(function() {
        $('#eBayUsernamesList').change(function() {
            checkeBayUsernameSelect();
        });
        checkeBayUsernameSelect();
    })
</script>
