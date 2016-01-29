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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if isset($errors) && $errors && sizeof($errors)}
<tr>
	<td style="border:1px solid #d6d4d4;background-color:#f8f8f8;padding:7px 0">
		<table style="width:100%">
			<tbody>
				<tr>
					<td width="10" style="padding:7px 0">&nbsp;</td>
					<td style="padding:7px 0">
						<font size="2" face="Open-sans, sans-serif" color="#555454">
							<p style="border-bottom:1px solid #d6d4d4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px">
								{l s='Error(s)' mod='ebay'}
							</p>
							{foreach from=$errors item='error'}
								<p style="color:#333;padding-bottom:10px;border-bottom:{cycle values="none,1px solid #d6d4d4" reset=true};">
									<strong>
									{if isset($error.link_warn)}
										{assign var="link" value='<a href="'|cat:$error.link_warn|cat:'" target="_blank">'}
										{$error.message|regex_replace:"/@link@/":$link|regex_replace:"/@\/link@/":"</a >"}
									{else}
										{$error.message|escape:'htmlall':'UTF-8'}
									{/if}
									</strong>
								</p>
							{/foreach}
						</font>
					</td>
					<td width="10" style="padding:7px 0">&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>
<tr>
	<td style="padding:0!important">&nbsp;</td>
</tr>
{/if}
{if isset($warnings) && $warnings && sizeof($warnings)}
<tr>
	<td style="border:1px solid #d6d4d4;background-color:#f8f8f8;padding:7px 0">
		<table style="width:100%">
			<tbody>
				<tr>
					<td width="10" style="padding:7px 0">&nbsp;</td>
					<td style="padding:7px 0">
						<font size="2" face="Open-sans, sans-serif" color="#555454">
							<p style="border-bottom:1px solid #d6d4d4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px">
								{l s='Warning(s)' mod='ebay'}
							</p>
							{foreach from=$warnings item='warning'}
								<p style="color:#333;padding-bottom:10px;border-bottom:{cycle values="none,1px solid #d6d4d4" reset=true};">
									<strong>
									{if isset($warning.link_warn)}
										{assign var="link" value='<a href="'|cat:$warning.link_warn|cat:'" target="_blank">'}
										{$warning.message|regex_replace:"/@link@/":$link|regex_replace:"/@\/link@/":"</a >"}
									{else}
										{$warning.message|escape:'htmlall':'UTF-8'}
									{/if}
									</strong>
								</p>
							{/foreach}
						</font>
					</td>
					<td width="10" style="padding:7px 0">&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>
<tr>
	<td style="padding:0!important">&nbsp;</td>
</tr>
{/if}
{if isset($infos) && $infos && sizeof($infos)}
<tr>
	<td style="border:1px solid #d6d4d4;background-color:#f8f8f8;padding:7px 0">
		<table style="width:100%">
			<tbody>
				<tr>
					<td width="10" style="padding:7px 0">&nbsp;</td>
					<td style="padding:7px 0">
						<font size="2" face="Open-sans, sans-serif" color="#555454">
							<p style="border-bottom:1px solid #d6d4d4;margin:3px 0 7px;text-transform:uppercase;font-weight:500;font-size:18px;padding-bottom:10px">
								{l s='Information(s)' mod='ebay'}
							</p>
							{foreach from=$infos item='info'}
								<p style="color:#333;padding-bottom:10px;border-bottom:{cycle values="none,1px solid #d6d4d4" reset=true};">
									<strong>
									{if isset($info.link_warn)}
										{assign var="link" value='<a href="'|cat:$info.link_warn|cat:'" target="_blank">'}
										{$info.message|regex_replace:"/@link@/":$link|regex_replace:"/@\/link@/":"</a >"}
									{else}
										{$info.message|escape:'htmlall':'UTF-8'}
									{/if}
									</strong>
								</p>
							{/foreach}
						</font>
					</td>
					<td width="10" style="padding:7px 0">&nbsp;</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>
<tr>
	<td style="padding:0!important">&nbsp;</td>
</tr>
{/if}
