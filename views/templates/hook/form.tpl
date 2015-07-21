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


<script type="text/javascript">
	regenerate_token_show = false;
	{if $regenerate_token != false}
	regenerate_token_show = true;
	{/if}
</script>

<link rel="stylesheet" href="{$css_file|escape:'urlencode'}" />
<link rel="stylesheet" href="{$font_awesome_css_file|escape:'urlencode'}" />
<script>
	var $j = $;
</script>
{if substr($smarty.const._PS_VERSION_, 0, 3) == "1.4" || substr($smarty.const._PS_VERSION_, 0, 5) == "1.5.2"}
	<link rel="stylesheet" href="{$fancyboxCss|escape:'urlencode'}" />
	<script src="{$ebayjquery|escape:'urlencode'}"></script>
	<script src="{$noConflicts|escape:'urlencode'}"></script>
	<script>
		if(typeof($j172) != 'undefined')
			$j = $j172;
		else 
			$j = $;
	</script>
	<script src="{$fancybox|escape:'urlencode'}"></script>
{/if}
<script src="{$tooltip|escape:'urlencode'}" type="text/javascript"></script>
<script src="{$tips202|escape:'urlencode'}" type="text/javascript"></script>

{literal}

{/literal}

{if $show_welcome || $show_welcome_stats}
<div class="ebay-welcome">
    <img id="ebay-logo" src="{$path|escape:'htmlall':'UTF-8'}views/img/ebay.png" />
    <div id="ebay-welcome-top" class="ebay-boxes-2-col-table">
        <div class="ebay-boxes-2-col-cell right">
            <div class="ebay-boxes-2-col-cell-content">
                <div id="ebay-welcome-left-content">
                    <p class="title ebay-title">{l s='A PERFECT PARTNER FOR YOUR BUSINESS' mod='ebay'}</p>
                    <p>{{l s='eBay is one of the |b|largest marketplaces in the world that connects buyers and sellers of all sizes around the world|/b|.' mod='ebay'}|replace:'|b|':'<b>'|replace:'|/b|':'</b>'}</p>

                    <p>{l s='eBay represents a great opportunity for you to reach millions of new customers and help you to  grow your business.' mod='ebay'}</p>

                    <p><b>{l s='With the eBay add-on for PrestaShop you can:' mod='ebay'}</b></p>
                    <ul class="ebay-bullet-points">
                        <li>{l s='Export your products from your PrestaShop shop to eBay.' mod='ebay'}</li>
                        <li>{l s='Manage both channels from your back-office.' mod='ebay'}</li>
                        <li>{l s='Develop a profitable additional sales channel.' mod='ebay'}</li>
                    </ul>
                    <p><b>{l s='Start selling now with the PrestaShop FREE add-on for eBay!' mod='ebay'}</b></p>
                </div>
            </div>
        </div>
        <div id="ebay-welcome-right" class="ebay-boxes-2-col-cell">
            <div class="ebay-boxes-2-col-cell-content right">
                <div class="ebay-light-gray-box">
                    <div class="ebay-light-gray-box-content-outer">
                        <div class="ebay-light-gray-box-content">
                            <p class="ebay-light-gray-box-title">{l s='Start selling on eBay with PrestaShop is easy:' mod='ebay'}</p>
                            <ul class="ebay-light-gray-box-ul">
                                <li><span class="ebay-light-gray-box-number">1</span> {l s='Create an eBay business account' mod='ebay'}</li>
                                <li><span class="ebay-light-gray-box-number">2</span> {l s='Open your eBay shop' mod='ebay'}</li>
                                <li><span class="ebay-light-gray-box-number">3</span> {l s='Link your eBay account to the eBay add-on' mod='ebay'}</li>
                                <li><span class="ebay-light-gray-box-number">4</span> {l s='Configure the eBay add-on' mod='ebay'}</li>
                            </ul>
                        </div>
                    </div>
                    {if $free_shop_for_90_days}
                    <div class="ebay-dark-gray-box-container">
                        <p class="ebay-dark-gray-box-title">
                            {l s='Open your shop for free' mod='ebay'}
                        </p>
                        <div class="ebay-dark-gray-box-content">
                            {l s='Begin or boost your activity on eBay, enjoying ' mod='ebay'}<b> {l s='90 free days of subscription' mod='ebay'}</b> {l s='to eBay Shops' mod='ebay'}<br/>
                            <a href="{l s='http://pages.ebay.fr/promos/termsfrstoretrial3months/' mod='ebay'}" target="_blank">{l s='More informations' mod='ebay'}</a>
                        </div>
                        <img src="../modules/ebay/views/img/open_your_shop.jpg" alt="" id="open_your_shop">
                        <div class="clear clearfix"></div>
                    </div>
                    {/if}
                </div>
            </div>
        </div>
        <div style="clear:both"></div>
    </div>
</div>
{/if}

<div class="ebay-boxes-2-col-table">
    <div class="ebay-boxes-2-col-cell">
        <div class="ebay-boxes-2-col-cell-content left">
            <fieldset class="new" style="min-height: 165px">
                {if $show_welcome}
                	<legend><img src="{$path|escape:'htmlall':'UTF-8'}logo.gif" alt="" />{l s='eBay Module Status' mod='ebay'}</legend>
                	<div style="float: left">
                    	{if empty($alert)}
                    		<p id="ebay-no-profile">{l s='You don\'t have any profile setup yet' mod='ebay'}</p>
                    		{if $is_version_one_dot_five}
                    			{if $is_version_one_dot_five_dot_one}
                    				<br/><img src="../modules/ebay/views/img/warn.png" /><strong>{l s='You\'re using version 1.5.1 of PrestaShop. We invite you to upgrade to version 1.5.2  so you can use the eBay module properly.' mod='ebay'}</strong>
                    				<br/><strong>{l s='Please synchronize your eBay sales in your Prestashop front office' mod='ebay'}</strong>
                    			{/if}
                    		{/if}
                    	{else}
                    		<img src="../modules/ebay/views/img/warn.png" /><strong>{l s='Please complete the following settings to configure the module' mod='ebay'}</strong>
                    		<br />{if in_array('registration', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 1) {l s='Register the module on eBay' mod='ebay'}
                    		<br />{if in_array('allowurlfopen', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 2) {l s='Allow url fopen' mod='ebay'}
                    		<br />{if in_array('curl', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 3) {l s='Enable cURL' mod='ebay'}
                    		<br />{if in_array('SellerBusinessType', $alert)}<img src="../modules/ebay/views/img/warn.png" />{else}<img src="../modules/ebay/views/img/valid.png" />{/if} 4) {l s='Please register an eBay business seller account to configure the application' mod='ebay'}
                    	{/if}
                    </div>
                {else}
                    	<legend>{l s='eBay Profiles' mod='ebay'}</legend>
                        {if $profiles && count($profiles)}
                            <table class="table tableDnD" cellpadding="0" cellspacing="0">
                        		<thead>
                        			<tr class="nodrag nodrop">
                                        <th>#</th>
                                        <th>{l s='eBay User Id' mod='ebay'}</th>
                                        <th>{l s='eBay Site' mod='ebay'}</th>
                                        {if version_compare(_PS_VERSION_, '1.5', '>')}<th>{l s='Prestashop Shop' mod='ebay'}</th>{/if}
                                        <th class="center">{l s='Language' mod='ebay'}</th>
                                        <th class="center">{l s='Nb Current Listings' mod='ebay'}</th>
                                        <th class="center">{l s='Action' mod='ebay'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                        			{foreach from=$profiles item=profile}            
                                        <tr onclick="document.getElementById('ebay_profile_form_{$profile.id_ebay_profile|escape:'htmlall':'UTF-8'}').submit();" style="cursor:pointer{if $current_profile->id == $profile.id_ebay_profile};font-weight:bold{/if}">
                                                <td><form id="ebay_profile_form_{$profile.id_ebay_profile|escape:'htmlall':'UTF-8'}" method="post"><input type="hidden" name="ebay_profile" value="{$profile.id_ebay_profile|escape:'htmlall':'UTF-8'}" /><input type="hidden" name="action" value="logged" /></form>{$profile.id_ebay_profile|escape:'htmlall':'UTF-8'}</td>                                                
                                            <td>{$profile.ebay_user_identifier|escape:'htmlall':'UTF-8'}</td>
                                            <td>eBay {$profile.site_name|escape:'htmlall':'UTF-8'}</td>
                                            {if version_compare(_PS_VERSION_, '1.5', '>')}<td>{$profile.name|escape:'htmlall':'UTF-8'}</td>{/if}
                                            <td align="center"><img src="../img/l/{$profile.id_lang|escape:'htmlall':'UTF-8'}.jpg" alt="{$profile.language_name|escape:'htmlall':'UTF-8'}" title="{$profile.language_name|escape:'htmlall':'UTF-8'}"></td>
                                            <td align="center">{$profile.nb_products|escape:'htmlall':'UTF-8'}</td>
                                            <td align="center"><img src="../img/admin/edit.gif" /><a href class="delete-profile" data-profile="{$profile.id_ebay_profile|escape:'htmlall':'UTF-8'}"><img src="../img/admin/delete.gif" /></a></td>     
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                            <br>
                            {l s='The bold profile is your current profile. To change the profile you are currently working with, click on the desired profile' mod='ebay'}
                            <br><br>
                            <a href="{$add_profile_url|escape:'urlencode'}"><img src="../img/admin/add.gif">{l s='Add a New Profile' mod='ebay'}</a>
                        {else}
                            <legend>{l s='Status of your eBay Add-on' mod='ebay'}</legend>
                            <p id="ebay-no-profile">{l s='You don\'t have any profile setup yet' mod='ebay'}</p>
                        {/if}
                    {/if}
                </fieldset>
            </div>
        </div>
    
        <div class="ebay-boxes-2-col-cell">
            <div class="ebay-boxes-2-col-cell-content right absolute">
                <fieldset class="new">
                    <legend>{l s='How to install the module' mod='ebay'}</legend>
                    {if $show_seller_tips}
                        <a id="ebay-seller-tips-link" href>{l s='Show seller tips' mod='ebay'}</a>
                    {/if}
                    <a id="" href="{l s='http://www.202-ecommerce.com/ebay/tuto-en' mod='ebay'}" target="_blank"><img id="ebay-install-pict" src="../modules/ebay/views/img/{l s='ebay_video_en' mod='ebay'}.png" /></a>
                    <p id="ebay-install-title">{l s='Resources' mod='ebay'}</p>
                    <ul id="ebay-install-ul">
                        <li><a href="http://202-ecommerce.com/d/eBay-doc_{$documentation_lang}.pdf" target="_blank">{l s='Download the add-on installation guide' mod='ebay'}</a></li>
                        <li><a href="{$pro_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='eBay Seller center' mod='ebay'}</a></li>
                        <li><a href="{$fee_url|escape:'htmlall':'UTF-8'}" target="_blank">{l s='eBay fees for professional sellers' mod='ebay'}</a></li>
                        <li><b><a href="{if $site_extension == 'fr'}http://202-ecommerce.com/ebay/{else}http://en.202-ecommerce.com/ebay-en/{/if}"  target="_blank">{l s='Contact us' mod='ebay'}</a></b></li>
                    </ul>
                </fieldset>
            </div>
        </div>
    </div>
<div style="clear:both"></div>

<!-- seller tips -->
{if $show_seller_tips}
    <div id="seller-tips" class="ebay-welcome" style="display:none">
        <div id="ebay-tips" class="ebay-boxes-2-col-table">
            <div class="ebay-boxes-2-col-cell right">
                <div class="ebay-boxes-2-col-cell-content">
                    <div id="ebay-welcome-left-content" style="padding-bottom: 3em">
                        <img id="ebay-logo" src="../modules/ebay/views/img/ebay.png" />
                        <p class="title ebay-title">{l s='A PERFECT PARTNER FOR YOUR BUSINESS' mod='ebay'}</p>
                        <p>{{l s='eBay is one of the |b|largest marketplaces in the world that connects buyers and sellers of all sizes around the world|/b|.' mod='ebay'}|replace:'|b|':'<b>'|replace:'|/b|':'</b>'}</p>

                        <p>{l s='eBay represents a great opportunity for you to reach millions of new customers and help you to  grow your business.' mod='ebay'}</p>
                    </div>
                </div>
            </div>
            <div id="ebay-welcome-right" class="ebay-boxes-2-col-cell">
                <div class="ebay-boxes-2-col-cell-content right">
                    <div class="ebay-light-gray-box">
                        <div class="ebay-light-gray-box-content-outer">
                            <div class="ebay-light-gray-box-content">
                                <p class="ebay-light-gray-box-title">{l s='Tips to sell more on eBay:' mod='ebay'}</p>
                                <ul class="ebay-light-gray-box-ul">
                                    <li>
                                        {assign var="link" value="<a href=\"{$title_desc_url|escape:'htmlall':'UTF-8'}\" target=\"_blank\">"}
                                        <span class="ebay-light-gray-box-number">1</span> <b>{l s='Help buyers to find your product:' mod='ebay'}</b> {{l s='Write good |a|titles and descriptions|/a|' mod='ebay'}|replace:'|a|':$link|replace:'|/a|':'</a>'}</a>
                                    </li>
                                    <li>
                                        {assign var="link" value="<a href=\"{$similar_items_url|escape:'htmlall':'UTF-8'}\" target=\"_blank\">"}
                                        <span class="ebay-light-gray-box-number">2</span> <b>{l s='Make your products competitive:' mod='ebay'}</b> {{l s='|a|research on eBay for similar products|/a| to yours and compare with your prices.' mod='ebay'}|replace:'|a|':$link|replace:'|/a|':'</a>'}
    </li>
                                    <li>
                                        {assign var="link" value="<a href=\"{$picture_url|escape:'htmlall':'UTF-8'}\" target=\"_blank\">"}
                                        <span class="ebay-light-gray-box-number">3</span> {{l s='|b|Take special care of your product pictures,|/b| |a|this will help buyers to buy from you.|/a|' mod='ebay'}|replace:'|b|':'</b>'|replace:'|/b|':'</b>'|replace:'|a|':$link|replace:'|/a|':'</a>'}
                                    </li>    
                                    <li>
                                        {assign var="link" value="<a href=\"{$top_rated_url|escape:'htmlall':'UTF-8'}\" target=\"_blank\">"}
                                        <span class="ebay-light-gray-box-number">4</span> {{l s='|b|Make buyers to come back|/b| by |a|delivering a great service|/a| and offering free shipping.' mod='ebay'}|replace:'|b|':'</b>'|replace:'|/b|':'</b>'|replace:'|a|':$link|replace:'|/a|':'</a>'}                                                        </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
    </div>
    
    {if isset($warning_url) && $warning_url}
        <div class="warn">
		    <span style="float:right">
			    <a id="hideWarn" href=""><img alt="X" src="../img/admin/close.png" /></a>
		    </span>
            <ul style="margin-top: 3px">
                <li>You are currently connected to the Prestashop Back Office using a different URL <a href="{$warning_url|escape:'urlencode'}">than set up</a>, this module will not work properly. Please login in using URL_Back_Office</li>
            </ul>
        </div>
    {/if}

    {if $current_profile && !$add_profile}
        <div class="ebay-boxes-2-col-table">
            <div class="ebay-boxes-2-col-cell left ebay_gray_title_box">
                {assign var="user_identifier" value=$current_profile->ebay_user_identifier|escape:'htmlall':'UTF-8'}
                {{l s='You are updating the "|profile_identifier| for eBay.|profile_domain|" profile' mod='ebay'}|replace:'|profile_identifier|':$user_identifier|replace:'|profile_domain|':$current_profile_site_extension}
            </div>
            <div class="ebay-boxes-2-col-cell right ebay_gray_title_box">
                <ul id="ebay_main_menu">
                    <li class="selected"><i class="fa fa-cog"></i> <a id="settings-menu-link" class="main-menu-a" href data-sub="settings">{l s='Settings' mod='ebay'}</a></li>
                    <li><i class="fa fa-paper-plane-o"></i> <a id="sync-menu-link" class="main-menu-a" href data-sub="sync">{l s='Synchronization' mod='ebay'}</a></li>
                    <li><i class="fa fa-eye"></i> <a id="visu-menu-link" class="main-menu-a" href data-sub="visu">{l s='Visualization' mod='ebay'}</a></li>
                    <li><i class="fa fa-wrench"></i> <a id="advanced-settings-menu-link" class="main-menu-a" href data-sub="advanced-settings">{l s='Advanced Settings' mod='ebay'}</a></li>
                </ul>
            </div>
        </div>
    {/if}
    
    <script type="text/javascript">
        var header_ebay_l = {
          'Hide seller tips' : "{l s='Hide seller tips' mod='ebay'}",
          'Show seller tips'  : "{l s='Show seller tips' mod='ebay'}",
          'Are you sure you want to delete the profile number %profile_number%?' : "{l s='Are you sure you want to delete the profile number %profile_number%?' mod='ebay'}"
        };
        
        var delete_profile_url = '{$delete_profile_url|escape:'htmlall':'UTF-8'}';
        
        var main_tab = '{$main_tab}';
        var id_tab = '{$id_tab}';
    </script>
    <script type="text/javascript" src="{$_module_dir_|escape:'htmlall':'UTF-8'}ebay/views/js/header.js?date={$date|escape:'htmlall':'UTF-8'}"></script>
{/if}
<!-- after seller tips -->