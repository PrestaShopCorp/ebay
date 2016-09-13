<?php
/**
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
 */

class EbayKb extends ObjectModel
{

    public $error_code;
    public $ps_version;
    public $language;
    public $module_version;
    public $link;
    public $date_add;
    public $date_upd;

    private $domain = 'http://redirect.202-ecommerce.com/';

    private $module = 'ebay';

    private $url;

    public static $definition = array(
        'table' => 'ebay_kb',
        'primary' => 'id_ebay_kb',
        'fields' => array(
            'error_code' => array('type' => 'TYPE_STRING', 'validate' => 'isString'),
            'language' => array('type' => 'TYPE_STRING', 'validate' => 'isString'),
            'ps_version' => array('type' => 'TYPE_STRING', 'validate' => 'isString'),
            'module_version' => array('type' => 'TYPE_STRING', 'validate' => 'isString'),
            'link' => array('type' => 'TYPE_STRING', 'validate' => 'isString'),
            'date_add' => array('type' => 'TYPE_DATE', 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => 'TYPE_DATE', 'validate' => 'isDateFormat'),
        ),
    );

    //PS 1.4
    protected $table;
    protected $identifier;
    protected $fieldsRequired = array();
    protected $fieldsValidate = array();
    protected $fieldsValidateLang = array();

    public function __construct($id = null)
    {
        if (version_compare(_PS_VERSION_, 1.5, '<')) {
            $this->table = self::$definition['table'];
            $this->identifier = self::$definition['primary'];

            foreach (self::$definition['fields'] as $key => $field) {
                if (isset($field['required']) && $field['required']) {
                    $this->fieldsRequired[] = $key;
                }

                $this->fieldsValidate[$key] = $field['validate'];
            }
        }

        $module = Module::getInstanceByName($this->module);

        $this->module_version = $module->version;

        $this->ps_version = _PS_VERSION_;

        parent::__construct($id);
    }

    public function getFields()
    {
        $fields = array();
        $fields['error_code'] = $this->error_code;
        $fields['language'] = $this->language;
        $fields['ps_version'] = $this->ps_version;
        $fields['module_version'] = $this->module_version;
        $fields['link'] = $this->link;
        $fields['date_add'] = $this->date_add;
        $fields['date_upd'] = $this->date_upd;

        return $fields;
    }

    public static function getIds()
    {
        $sql = "SELECT `".self::$definition['primary']."` FROM "._DB_PREFIX_.self::$definition['table']."";
        $objsIDs = Db::getInstance()->ExecuteS($sql);
        return $objsIDs;
    }

    public static function install()
    {
        $sql= array();
        // Create Category Table in Database
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'` (
				  	`'.self::$definition['primary'].'` int(16) NOT NULL AUTO_INCREMENT,
				 	`error_code` varchar(255) NOT NULL,
				 	`language` varchar(255) NOT NULL,
				 	`ps_version` varchar(255) NOT NULL,
				 	`module_version` varchar(255) NOT NULL,
				 	`link` varchar(255) NOT NULL,
				 	date_add datetime NOT NULL,
					date_upd datetime NOT NULL,
					UNIQUE(`'.self::$definition['primary'].'`),
				  	PRIMARY KEY  ('.self::$definition['primary'].')
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        foreach ($sql as $q) {
            Db::getInstance()->Execute($q);
        }

    }

    public static function uninstall()
    {
        // Create Category Table in Database
        $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::$definition['table'].'`';

        foreach ($sql as $q) {
            Db::getInstance()->Execute($q);
        }

    }

    public function call()
    {
        if ($this->build()) {
            $result = Tools::file_get_contents($this->url);
            if (!Tools::isEmpty($result)) {
                $this->link = pSQL($result);
                return $this->save();
            } else {
                return false;
            }

        } else {
            return false;
        }

    }

    public function build()
    {
        if (Tools::isEmpty($this->language)) {
            $this->setLanguage();
        }

        $this->url = $this->domain;
        $this->url .= '?module='.$this->module;
        $this->url .= '&module_version='.$this->module_version;
        $this->url .= '&prestashop_version='.$this->ps_version;
        $this->url .= '&language='.$this->language;
        $this->url .= '&error_code='.$this->error_code;
        return true;
    }

    public function setErrorCode($code)
    {
        $this->error_code = $code;
    }

    public function setLanguage($iso_lang = 'en')
    {
        $this->language = $iso_lang;
    }

    public function getLink()
    {
        if (Tools::isEmpty($this->error_code)) {
            return false;
        }

        if ($this->exist()) {
            $now = new DateTime(date('Y-m-d'));
            $expire = new DateTime($this->date_upd);
            $interval = round(($now->format('U') - $expire->format('U')) / (60 * 60 * 24));

            if ($interval > 4) {
                if ($this->call()) {
                    if ($this->link == 'false' || $this->link === false) {
                        return false;
                    } else {
                        return $this->link;
                    }

                }
                return false;
            } else {
                if ($this->link == 'false' || $this->link === false) {
                    return false;
                } else {
                    return $this->link;
                }

            }
        } else {
            if ($this->call()) {
                if ($this->link == 'false' || $this->link === false) {
                    return false;
                } else {
                    return $this->link;
                }

            }
            return false;
        }
    }

    public function exist()
    {
        if (Tools::isEmpty($this->language)) {
            $this->setLanguage();
        }

        $sql = 'SELECT `'.$this->identifier.'`
		FROM '._DB_PREFIX_.pSQL($this->table).'
		WHERE error_code="'.pSQL($this->error_code).' "
		AND module_version="'.pSQL($this->module_version).' "
		AND ps_version="'.pSQL($this->ps_version).' "
		AND language="'.pSQL($this->language).'"';

        if ($id_kb = Db::getInstance()->getValue($sql)) {
            if (Validate::isInt($id_kb)) {
                parent::__construct((int) $id_kb);
                return true;
            }
        }

        return false;
    }
}
