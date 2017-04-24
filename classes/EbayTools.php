<?php
/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayTools
{

    /**
     * Get a value from $_POST / $_GET
     * if unavailable, take a default value
     *
     * @param string $key           Value key
     * @param mixed  $default_value (optional)
     * @return mixed Value
     */
    public static function getValue($key, $default_value = false)
    {
        if (!isset($key) || empty($key) || !is_string($key)) {
            return false;
        }

        $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default_value));

        if (is_string($ret)) {
            return stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret))));
        }

        return $ret;
    }

    public static function getIsset($key)
    {
        if (!isset($key) || empty($key) || !is_string($key)) {
            return false;
        }

        return isset($_POST[$key]) ? true : (isset($_GET[$key]) ? true : false);
    }

    public static function phpCheckSyntax($fileName, $checkIncludes = true, $delete = false)
    {
        try {
            self::checkSyntax($fileName, $checkIncludes);
        } catch (Exception $e) {
            if (true === $delete) {
                # we copy the file before deleting it
                @unlink($fileName.'.error');
                @copy($fileName, $fileName.'.error');
                @unlink($fileName);
            }

            return false;
        }

        return true;
    }

    /**
     * @param      $fileName
     * @param bool $checkIncludes
     * @throws Exception
     */
    public static function checkSyntax($fileName, $checkIncludes = true)
    {
        // If it is not a file or we can't read it throw an exception
        if (!is_file($fileName) || !is_readable($fileName)) {
            throw new Exception("Cannot read file ".$fileName);
        }

        // Sort out the formatting of the filename
        $fileName = realpath($fileName);

        // Get the shell output from the syntax check command
        $output = shell_exec('php -l "'.$fileName.'"');

        // Try to find the parse error text and chop it off
        $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, -1, $count);

        // If the error text above was matched, throw an exception containing the syntax error
        if ($count > 0) {
            throw new Exception(trim($syntaxError));
        }

        // If we are going to check the files includes
        if ($checkIncludes) {
            foreach (self::getIncludes($fileName) as $include) {
                // Check the syntax for each include
                self::checkSyntax($include);
            }
        }
    }

    public static function getIncludes($fileName)
    {
        // NOTE that any file coming into this function has already passed the syntax check, so
        // we can assume things like proper line terminations

        $includes = array();
        // Get the directory name of the file so we can prepend it to relative paths
        $dir = dirname($fileName);

        // Split the contents of $fileName about requires and includes
        // We need to slice off the first element since that is the text up to the first include/require
        $requireSplit = array_slice(preg_split('/require|include/i', Tools::file_get_contents($fileName)), 1);

        // For each match
        foreach ($requireSplit as $string) {
            // Substring up to the end of the first line, i.e. the line that the require is on
            $string = Tools::substr($string, 0, strpos($string, ";"));

            // If the line contains a reference to a variable, then we cannot analyse it
            // so skip this iteration
            if (strpos($string, "$") !== false) {
                continue;
            }

            // Split the string about single and double quotes
            $quoteSplit = preg_split('/[\'"]/', $string);

            // The value of the include is the second element of the array
            // Putting this in an if statement enforces the presence of '' or "" somewhere in the include
            // includes with any kind of run-time variable in have been excluded earlier
            // this just leaves includes with constants in, which we can't do much about
            if ($include = $quoteSplit[1]) {
                // If the path is not absolute, add the dir and separator
                // Then call realpath to chop out extra separators
                if (strpos($include, ':') === false) {
                    $include = realpath($dir.DIRECTORY_SEPARATOR.$include);
                }

                array_push($includes, $include);
            }
        }

        return $includes;
    }
}
