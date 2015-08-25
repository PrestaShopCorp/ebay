<?php
/**
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
 */

abstract class EbayLoadLogs {

	protected $file;

	public function getLogs()
	{
		if (!Configuration::get('EBAY_SECURITY_TOKEN') 
			|| Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
			return Tools::safeOutput(Tools::getValue('not_logged_str'));

		$ebay = new Ebay();
		$ebay_profile = new EbayProfile((int)Tools::getValue('profile'));

		$page = (int)Tools::getValue('p', 0);
		$nb_results = 20;
		if ($page < 2)
			$page = 1;
		$offset = $nb_results * ($page - 1);

		$smarty =  Context::getContext()->smarty;

		$logs = $this->getDatas($offset, $nb_results);


		/* Smarty datas */
		$template_vars = array(
			'logs' => $logs,
			'p' => $page,
			'noLogFound' => Tools::getValue('no_logs_str'),
			'showStr' =>  Tools::getValue('show_str'),
		);

		$smarty->assign($template_vars);
		return $ebay->display(realpath(dirname(__FILE__).'/../'), $this->file);
	}

	protected abstract function getDatas($offset, $nb_results);

}