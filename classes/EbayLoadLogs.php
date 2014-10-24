<?php

abstract class EbayLoadLogs {

	abstract protected $file;

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

		$logs = $this->getDatas($logs);


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

	protected abstract function getDatas($logs);

}