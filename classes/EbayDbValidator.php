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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class EbayDbValidator
{
    private $logs;
    // LOG : array(
    // 'table' => array('status' => 'success', 'action' => 'ALTER TABLE', 'result' => 'GOOD')
    // );

    private $database = array(
        'ebay_api_log' => array(
            'id_ebay_api_log' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11, 'null' => true, 'unsigned' => true, 'default' => null),
            'type' => array('type' => 'varchar', 'length' => 40),
            'context' => array('type' => 'varchar', 'length' => 40),
            'data_sent' => array('type' => 'text', 'length' => null),
            'response' => array('type' => 'text', 'length' => null),
            'id_product' => array('type' => 'int', 'length' => 11),
            'id_order' => array('type' => 'int', 'length' => 11),
            'date_add' => array('type' => 'datetime', 'length' => 11),
        ),

        'ebay_category' => array(
            'id_category_ref' => array('type' => 'int', 'length' => 11),
            'id_ebay_category' => array('type' => 'int', 'length' => 11),
            'id_category_ref_parent' => array('type' => 'int', 'length' => 11),
            'id_country' => array('type' => 'int', 'length' => 11),
            'level' => array('type' => 'tinyint', 'length' => 1),
            'is_multi_sku' => array('type' => 'tinyint', 'length' => 1, 'null' => true),
            'name' => array('type' => 'varchar', 'length' => 255),
        ),

        'ebay_category_condition' => array(
            'id_ebay_category_condition' => array('type' => 'int', 'length' => 11),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'id_category_ref' => array('type' => 'int', 'length' => 11),
            'id_condition_ref' => array('type' => 'int', 'length' => 11),
            'name' => array('type' => 'varchar', 'length' => 256),
        ),

        'ebay_category_condition_configuration' => array(
            'id_ebay_category_condition_configuration' => array('type' => 'int', 'length' => 11),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'id_category_ref' => array('type' => 'int', 'length' => 11),
            'condition_type' => array('type' => 'int', 'length' => 11),
            'id_condition_ref' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_category_configuration' => array(
            'id_ebay_category_configuration' => array('type' => 'int', 'length' => 11),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'id_country' => array('type' => 'int', 'length' => 11),
            'id_ebay_category' => array('type' => 'int', 'length' => 11),
            'id_category' => array('type' => 'int', 'length' => 11),
            'percent' => array('type' => 'varchar', 'length' => 4),
            'sync' => array('type' => 'tinyint', 'length' => 1),
            'date_add' => array('type' => 'datetime', 'length' => null),
            'date_upd' => array('type' => 'datetime', 'length' => null),
        ),

        'ebay_category_specific' => array(
            'id_ebay_category_specific' => array('type' => 'int', 'length' => 11),
            'id_category_ref' => array('type' => 'int', 'length' => 11),
            'name' => array('type' => 'varchar', 'length' => 40),
            'required' => array('type' => 'tinyint', 'length' => 1),
            'can_variation' => array('type' => 'tinyint', 'length' => 1),
            'selection_mode' => array('type' => 'tinyint', 'length' => 1),
            'id_attribute_group' => array('type' => 'int', 'length' => 11),
            'id_feature' => array('type' => 'int', 'length' => 11),
            'id_ebay_category_specific_value' => array('type' => 'int', 'length' => 11),
            'is_brand' => array('type' => 'tinyint', 'length' => 1),
            'ebay_site_id' => array('type' => 'int', 'length' => 11),
            'is_reference' => array('type' => 'tinyint', 'length' => 1),
            'is_ean' => array('type' => 'tinyint', 'length' => 1),
            'is_upc' => array('type' => 'tinyint', 'length' => 1),
        ),

        'ebay_category_specific_value' => array(
            'id_ebay_category_specific_value' => array('type' => 'int', 'length' => 11),
            'id_ebay_category_specific' => array('type' => 'int', 'length' => 11),
            'value' => array('type' => 'varchar', 'length' => 50),
        ),

        'ebay_configuration' => array(
            'id_configuration' => array('type' => 'int', 'length' => 11),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'name' => array('type' => 'varchar', 'length' => 32),
            'value' => array('type' => 'text', 'length' => null),
        ),

        'ebay_delivery_time_options' => array(
            'id_delivery_time_option' => array('type' => 'int', 'length' => 11),
            'DispatchTimeMax' => array('type' => 'varchar', 'length' => 256),
            'description' => array('type' => 'varchar', 'length' => 256),
        ),

        'ebay_log' => array(
            'id_ebay_log' => array('type' => 'int', 'length' => 11, 'primary' => true),
            'text' => array('type' => 'text', 'length' => 11),
            'type' => array('type' => 'varchar', 'length' => 32),
            'date_add' => array('type' => 'datetime', 'length' => null),
        ),

        'ebay_order' => array(
            'id_ebay_order' => array('type' => 'int', 'length' => 11, 'primary' => true),
            'id_order_ref' => array('type' => 'varchar', 'length' => 128),
            'id_order' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_order_log' => array(
            'id_ebay_order_log' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'id_ebay_order' => array('type' => 'int', 'length' => 11),
            'id_orders' => array('type' => 'varchar', 'length' => 255),
            'type' => array('type' => 'varchar', 'length' => 40),
            'success' => array('type' => 'tinyint', 'length' => 1),
            'data' => array('type' => 'text', 'length' => null),
            'date_add' => array('type' => 'datetime', 'length' => null),
            'date_update' => array('type' => 'datetime', 'length' => null),
        ),

        'ebay_order_order' => array(
            'id_ebay_order_order' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_order' => array('type' => 'int', 'length' => 11),
            'id_order' => array('type' => 'int', 'length' => 11),
            'id_shop' => array('type' => 'int', 'length' => 11),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_product' => array(
            'id_ebay_product' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'id_country' => array('type' => 'int', 'length' => 11),
            'id_product' => array('type' => 'int', 'length' => 11),
            'id_attribute' => array('type' => 'int', 'length' => 11),
            'id_product_ref' => array('type' => 'varchar', 'length' => 32),
            'date_add' => array('type' => 'datetime', 'length' => null),
            'date_upd' => array('type' => 'datetime', 'length' => null),
        ),

        'ebay_product_configuration' => array(
            'id_ebay_product_configuration' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_product' => array('type' => 'int', 'length' => 11),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'blacklisted' => array('type' => 'tinyint', 'length' => 1),
            'extra_images' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_product_image' => array(
            'id_ebay_product_image' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'ps_image_url' => array('type' => 'varchar', 'length' => 255),
            'ebay_image_url' => array('type' => 'varchar', 'length' => 255),
        ),

        'ebay_product_modified' => array(
            'id_ebay_product_modified' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'id_product' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_profile' => array(
            'id_ebay_profile' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_lang' => array('type' => 'int', 'length' => 11),
            'id_shop' => array('type' => 'int', 'length' => 11),
            'ebay_user_identifier' => array('type' => 'varchar', 'length' => 255),
            'ebay_site_id' => array('type' => 'int', 'length' => 11),
            'id_ebay_returns_policy_configuration' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_returns_policy' => array(
            'id_return_policy' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'value' => array('type' => 'varchar', 'length' => 256),
            'description' => array('type' => 'varchar', 'length' => 256),
        ),

        'ebay_returns_policy_configuration' => array(
            'id_ebay_returns_policy_configuration' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'ebay_returns_within' => array('type' => 'varchar', 'length' => 255),
            'ebay_returns_who_pays' => array('type' => 'varchar', 'length' => 255),
            'ebay_returns_description' => array('type' => 'text', 'length' => null),
            'ebay_returns_accepted_option' => array('type' => 'varchar', 'length' => 255),
        ),

        'ebay_returns_policy_description' => array(
            'id_return_policy' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'value' => array('type' => 'varchar', 'length' => 256),
            'description' => array('type' => 'varchar', 'length' => 256),
        ),

        'ebay_shipping' => array(
            'id_ebay_shipping' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'ebay_carrier' => array('type' => 'varchar', 'length' => 256),
            'ps_carrier' => array('type' => 'int', 'length' => 11),
            'extra_fee' => array('type' => 'int', 'length' => 11),
            'international' => array('type' => 'int', 'length' => 11),
            'id_zone' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_shipping_international_zone' => array(
            'id_ebay_shipping' => array('type' => 'int', 'length' => 11),
            'id_ebay_zone' => array('type' => 'varchar', 'length' => 256),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_shipping_location' => array(
            'id_ebay_location' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'location' => array('type' => 'varchar', 'length' => 256),
            'description' => array('type' => 'varchar', 'length' => 256),
        ),

        'ebay_shipping_service' => array(
            'id_shipping_service' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'description' => array('type' => 'varchar', 'length' => 256),
            'shippingService' => array('type' => 'varchar', 'length' => 256),
            'shippingServiceID' => array('type' => 'varchar', 'length' => 256),
            'InternationalService' => array('type' => 'varchar', 'length' => 256),
            'ServiceType' => array('type' => 'varchar', 'length' => 256),
            'ebay_site_id' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_shipping_zone_excluded' => array(
            'id_ebay_zone_excluded' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'region' => array('type' => 'varchar', 'length' => 255),
            'location' => array('type' => 'varchar', 'length' => 255),
            'description' => array('type' => 'varchar', 'length' => 255),
            'excluded' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_stat' => array(
            'id_ebay_stat' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'version' => array('type' => 'varchar', 'length' => 10),
            'data' => array('type' => 'text', 'length' => null, 'null' => false),
            'date_add' => array('type' => 'datetime', 'length' => null),
            'tries' => array('type' => 'tinyint', 'length' => 3, 'unsigned' => true),
        ),

        'ebay_store_category' => array(
            'id_ebay_store_category' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'ebay_category_id' => array('type' => 'varchar', 'length' => 255),
            'name' => array('type' => 'varchar', 'length' => 255),
            'order' => array('type' => 'int', 'length' => 11),
            'ebay_parent_category_id' => array('type' => 'varchar', 'length' => 255),
        ),

        'ebay_store_category_configuration' => array(
            'id_ebay_store_category_configuration' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_profile' => array('type' => 'int', 'length' => 11),
            'ebay_category_id' => array('type' => 'varchar', 'length' => 255),
            'id_category' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_sync_history' => array(
            'id_ebay_sync_history' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'is_manual' => array('type' => 'tinyint', 'length' => 1),
            'datetime' => array('type' => 'datetime', 'length' => null),
        ),

        'ebay_sync_history_product' => array(
            'id_ebay_sync_history_product' => array('type' => 'int', 'length' => 11, 'primary' => true, 'auto_increment' => true),
            'id_ebay_sync_history' => array('type' => 'int', 'length' => 11),
            'id_product' => array('type' => 'int', 'length' => 11),
        ),

        'ebay_user_identifier_token' => array(
            'ebay_user_identifier' => array('type' => 'varchar', 'length' => 255, 'primary' => true, 'auto_increment' => true),
            'token' => array('type' => 'text', 'length' => null),
        ),
    );

    public function checkDatabase()
    {
        foreach ($this->database as $table => $fields) {
            $this->checkTable($table, $fields);
        }

        $this->writeLog();
    }

    public function writeLog()
    {
        $handle = fopen(dirname(__FILE__).'/../log/log_database.txt', 'a+');
        fwrite($handle, print_r($this->logs, true));
        fclose($handle);
    }

    private function checkTable($table, $fields)
    {
        // Check if table exist
        $result = Db::getInstance()->ExecuteS('SHOW TABLES LIKE "'._DB_PREFIX_.$table.'"');
        
        if ($result === false) {
            $this->setLog($table, 'error', 'SQL REQUEST : SHOW TABLES LIKE "'._DB_PREFIX_.$table.'"', Db::getInstance()->getMsgError());
        } elseif (empty($result)) {
            $this->repairTable($table);
        } else {
            $this->checkField($table, $fields);
        }

    }

    private function checkField($table, $fields)
    {
        foreach ($fields as $field => $arguments) {

            $result = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'._DB_PREFIX_.$table.'` LIKE \''.$field.'\';');

            if ($result === false) {
                $this->setLog($table, 'error', 'SQL REQUEST : '.Db::getInstance()->getMsgError());
            } elseif (empty($result)) {
                $this->setLog($table, 'error', 'The '.$field.' column in '.$table.' table doesn\'t exist');
                if ($this->addColumns($table, $field, $arguments)) {
                    $this->setLog($table, 'SUCCESSFULL', 'The ' . $field . ' column in ' . $table . ' added', 'SUCCESSFULL');
                }
            } else {
                $this->checkTypeFields($table, $field, $arguments);
            }

        }
    }
    private function addColumns($table, $field, $arguments)
    {
        $sql = 'ALTER TABLE `'._DB_PREFIX_.$table.'` 
                ADD '.bqSQL($field).' '.bqSQL($arguments['type']).'('.pSQL($arguments['length']).')';


        return Db::getInstance()->Execute($sql);


    }

    private function checkTypeFields($table, $field, $arguments)
    {
        $sql = 'SHOW FIELDS FROM `'._DB_PREFIX_.$table.'` LIKE "'.$field.'";';
        $result = Db::getInstance()->ExecuteS($sql);

        $reset = false;
        if ($result && !empty($result[0])) {

            // Check Null Attribute
            if (isset($result[0]['Null'])
                &&
                (
                    ($result[0]['Null'] == 'YES' && !isset($arguments['null']))
                    ||
                    ($result[0]['Null'] == 'NO' && isset($arguments['null']))
                )
            ) {
                $reset = true;
            }

            // Check Type Attribute
            if (isset($result[0]['Type']) && isset($arguments['type'])) {
                // is int
                if (preg_match('/^int\(([0-9]+)\)$/', $result[0]['Type'], $rs)) {
                    if (
                        ($arguments['type'] != 'int')
                        ||
                        (!isset($arguments['length']) || $rs[1] < $arguments['length'])
                    ) {
                        $reset = true;
                    }

                }

                // is tinyint
                if (preg_match('/^tinyint\(([0-9]+)\)$/', $result[0]['Type'], $rs)) {
                    if (
                        ($arguments['type'] != 'tinyint')
                        ||
                        (!isset($arguments['length']) || $rs[1] < $arguments['length'])
                    ) {
                        $reset = true;
                    }

                }

                // is varchar
                if (preg_match('/^varchar\(([0-9]+)\)$/', $result[0]['Type'], $rs)) {
                    if (
                        ($arguments['type'] != 'varchar')
                        ||
                        (!isset($arguments['length']) || $rs[1] < $arguments['length'])
                    ) {
                        $reset = true;
                    }

                }

                // is text
                if (preg_match('/^text$/', $result[0]['Type'])
                    && ($arguments['type'] != 'text')) {
                    $reset = true;
                }

                // is datetime
                if (preg_match('/^datetime$/', $result[0]['Type'])
                    && ($arguments['type'] != 'datetime')) {
                    $reset = true;
                }

            }

            // Check default
            if (isset($result[0]['Default'])) {
                if (
                    !isset($arguments['default'])
                    ||
                    ($result[0]['Default'] !== $arguments['default'])
                ) {
                    $reset = true;
                }

            }

            // Check Primary Attribute
            if (isset($result[0]['Key']) && $result[0]['Key'] == 'PRI') {
                // is primary
                if (isset($arguments['primary']) && $arguments['primary']) {
                    // Alter Table Primary
                } else {
                    // Delete primary
                }

            }

            // Check Extra Attribute
            if (isset($result[0]['Extra']) && $result[0]['Extra'] == 'auto_increment') {
                // is primary
                if (isset($arguments['auto_increment']) && $arguments['auto_increment']) {
                    // Alter Table Primary
                } else {
                    // Delete primary
                }

            }

            if ($reset) {
                $this->repairField($table, $field, $arguments);
            } else {
                $this->setLog($table, 'success', 'The '.$field.' field in '.$table.' is good');
            }

        } else {
            $this->setLog($table, 'error', 'SQL REQUEST : '.$sql);
        }

    }

    private function repairTable($table)
    {
        $this->setLog($table, 'error', 'The '.$table.' table doesn\'t exist');

        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table.'` ( ';
        $primary_key = array(); //Case of double primary key

        foreach ($this->database[$table] as $column => $arguments) {

            $sql .= '`'.$column.'` '.$arguments['type'];

            if ($arguments['length']) {
                $sql .= '('.$arguments['length'].')';
            }

            if (isset($arguments['unsigned']) && $arguments['unsigned']) {
                $sql .= ' unsigned ';
            }

            if (isset($arguments['null'])) {
                if ($arguments['null']) {
                    $sql .= ' NULL ';
                }

            } else {
                $sql .= ' NOT NULL ';
            }

            if (isset($arguments['auto_increment']) && $arguments['auto_increment']) {
                $sql .= ' AUTO_INCREMENT ';
            }

            if (isset($arguments['primary']) && $arguments['primary']) {
                $primary_key[] = $column;
            }

            $sql .= ', ';
        }

        foreach ($primary_key as $column) {
            $sql .= 'PRIMARY KEY (`'.$column.'`)';
        }

        $sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $result = Db::getInstance()->Execute($sql);

        if ($result) {
            $this->setLog($table, 'error', 'SQL REQUEST : '.$sql, 'SUCCESSFULL');
        } else {
            $this->setLog($table, 'error', 'SQL REQUEST : '.$sql, Db::getInstance()->getMsgError());
        }

    }

    private function repairField($table, $field, $arguments)
    {
        // ALTER TABLE `ps_ebay_api_log` CHANGE `id_ebay_profile` `id_ebay_profile` INT(16) NOT NULL;
        // ALTER TABLE `ps_ebay_api_log` CHANGE `id_ebay_api_log` `id_ebay_api_log` INT(16) NOT NULL AUTO_INCREMENT;
        // ALTER TABLE `ps_ebay_configuration` CHANGE `id_ebay_profile` `id_ebay_profile` INT(11) UNSIGNED NULL DEFAULT NULL;
        $sql = 'ALTER TABLE `'._DB_PREFIX_.$table.'` CHANGE `'.$field.'` `'.$field.'` '.$arguments['type'].'';

        if (isset($arguments['length']) && $arguments['length']) {
            $sql .= '('.$arguments['length'].') ';
        } else {
            $sql .= ' ';
        }

        if (isset($arguments['unsigned']) && $arguments['unsigned']) {
            $sql .= 'UNSIGNED ';
        }

        if (!isset($arguments['null'])) {
            $sql .= 'NOT NULL ';
        } elseif ($arguments['null']) {
            $sql .= 'NULL ';
        }

        if (array_key_exists('default', $arguments)) {
            if ($arguments['default'] === null) {
                $sql .= 'DEFAULT NULL ';
            }

        }

        if (isset($arguments['auto_increment']) && $arguments['auto_increment']) {
            $sql .= 'AUTO_INCREMENT ';
        }

        $result = Db::getInstance()->Execute($sql);

        if ($result) {
            $this->setLog($table, 'execute-success', 'SQL REQUEST : '.$sql, 'SUCCESSFULL');
        } else {
            $this->setLog($table, 'error', 'SQL REQUEST : '.$sql, Db::getInstance()->getMsgError());
        }

    }

    private function setLog($table, $status, $action, $result = '')
    {
        $this->logs[$table][] = array('status' => $status, 'action' => $action, 'result' => $result);
    }
    public function getNbTable()
    {
        return count($this->database);
    }
    public function checkSpecificTable($id)
    {
        $array = array_keys($this->database);

        if (array_key_exists($id - 1, $array)) {
            $this->checkTable($array[$id - 1], $this->database[$array[$id - 1]]);
            return true;
        }
        return false;

    }

    public function getLogForSpecificTable($id)
    {
        $array = array_keys($this->database);

        if (array_key_exists($id - 1, $array)) {
            return $this->logs[$array[$id - 1]];
        }

        return false;
    }
    public function getLog()
    {
        $result = array();

        foreach ($this->logs as $table => $logs) {

            foreach ($logs as $log) {
                if ($log['status'] != 'success') {
                    $result[$table][] = array('status' => $log['status'], 'action' => $log['action'], 'result' => $log['result']);
                }

            }

            if (!array_key_exists($table, $result)) {
                $result[$table][] = array('status' => 'success', 'action' => 'No action on '.$table.' table', 'result' => 'The table is perfect');
            }

        }

        return $result;
    }
    public function getCategoriesPsEbay()
    {
        $sql = 'SELECT
			DISTINCT(ec1.`id_categories`) as id,
			CONCAT(
				IFNULL(ec3.`name`, \'\'),
				IF (ec3.`name` is not null, \' > \', \'\'),
				IFNULL(ec2.`name`, \'\'),
				IF (ec2.`name` is not null, \' > \', \'\'),
				ec1.`name`
			) as name
			FROM `'._DB_PREFIX_.'ebay_category_tmp` ec1
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_tmp` ec2
			ON ec1.`id_categories_ref_parent` = ec2.`id_categories`
			AND ec1.`id_categories_ref_parent` <> \'1\'
			AND ec1.level <> 1
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_tmp` ec3
			ON ec2.`id_categories_ref_parent` = ec3.`id_categories`
			AND ec2.`id_categories_ref_parent` <> \'1\'
			AND ec2.level <> 1
			WHERE ec1.`id_categories` is not null and ec1.`id_categories` not in(
			SELECT `id_category_ref`
			FROM `'._DB_PREFIX_.'ebay_category` ecp
			WHERE ecp. `id_category_ref` is not null)';

        return Db::getInstance()->executeS($sql);

    }

    public function getCategoriesTmp()
    {

        $sql = 'SELECT
			DISTINCT(ec1.`id_categories`) as id,
			CONCAT(
				IFNULL(ec3.`name`, \'\'),
				IF (ec3.`name` is not null, \' > \', \'\'),
				IFNULL(ec2.`name`, \'\'),
				IF (ec2.`name` is not null, \' > \', \'\'),
				ec1.`name`
			) as name
			FROM `'._DB_PREFIX_.'ebay_category_tmp` ec1
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_tmp` ec2
			ON ec1.`id_categories_ref_parent` = ec2.`id_categories`
			AND ec1.`id_categories_ref_parent` <> \'1\'
			AND ec1.level <> 1
			LEFT JOIN `'._DB_PREFIX_.'ebay_category_tmp` ec3
			ON ec2.`id_categories_ref_parent` = ec3.`id_categories`
			AND ec2.`id_categories_ref_parent` <> \'1\'
			AND ec2.level <> 1
			WHERE ec1.`id_categories` is not null';

        return Db::getInstance()->executeS($sql);
    }

    public function comparationCategories($ebay_profile_id)
    {
        $data = array();
        $result = array();
        $data['cat_ebay']=$this->getCategoriesTmp();
        $data['cat_ps']=EbayCategoryConfiguration::getEbayCategories($ebay_profile_id);
        
        if ($data['cat_ps'] == '') {
            $result['table'] = null;
            $result['new'] = false;
            $datas=array();
            return $datas[] = $result;
        }
        $result=array();
        foreach ($data['cat_ps'] as $cat_p) {
            foreach ($data['cat_ebay'] as $cat_ebay) {
                $statut = 0;
                if ($cat_p['id'] == $cat_ebay['id'] and $cat_p['name'] == $cat_ebay['name']) {
                    $result['table'][$cat_p['name']] = 1;

                    $statut=1;
                    break;
                }

            }
            if ($statut == 0) {
                $result['table'][$cat_p['name']] = 0;

            }

        }

        $ps_ebay_cat= $this->getCategoriesPsEbay();
        if ($ps_ebay_cat == '') {
            $result['new'] = false;
        }
        $result['new']=$ps_ebay_cat;
        $datas=array();
         return $datas[] = $result;
        
    }

    public function deleteTmp()
    {

        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_category_tmp`');
    
    }
}
