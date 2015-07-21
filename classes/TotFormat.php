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

class TotFormat
{
	public static function cleanNonUnicodeSupport($pattern)
	{
		if(method_exists('Tools', 'cleanNonUnicodeSupport'))
			return Tools::cleanNonUnicodeSupport($pattern);
		else 
			return $pattern;
	}

	/**
	 * Format e-mail to be valid
	 *
	 * @param string $email e-mail address to format
	 * @return string email if it is valid, email without @ + _missspelled@dontknow.com if not valid
	 */
	public static function formatEmail($email)
	{
		if (empty($email) || !preg_match(self::cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z0-9]+$/ui'), $email))
			return str_replace('@', '__at__', $email).'_misspelled@dontknow.com';
		return $email;
	}

	/**
	 * Format name to be valid
	 *
	 * @param string $name Name to format
	 * @return string formatted name
	 */
	public static function formatName($name)
	{
		return trim(preg_replace(self::cleanNonUnicodeSupport('/[0-9!<>,;?=+()@#"°{}_$%:]+/u'), ' ', Tools::stripslashes($name)));
	}
	
	/**
	 * Format a postal address to be valid
	 *
	 * @param string $address Address to format
	 * @return string formatted address
	 */
	public static function formatAddress($address)
	{
		if (empty($address))
			return $address;
		return trim(preg_replace('/[!<>?=+@{}_$%]+/u', ' ', Tools::stripslashes($address)));
	}

	/**
	 * Format a postal code to be valid
	 *
	 * @param string $postcode Postal code to format
	 * @return string formatted post code
	 */
	public static function formatPostCode($postcode)
	{
		if (empty($postcode))
			return $postcode;
		return trim(preg_replace('/[^a-zA-Z 0-9-]+/', ' ', Tools::stripslashes($postcode)));
	}

	/**
	 * Format city name to be valid
	 *
	 * @param string $city City name to format
	 * @return string formatted city name
	 */
	public static function formatCityName($city)
	{
		return trim(preg_replace('/[!<>;?=+@#"°{}_$%]+/u', ' ', Tools::stripslashes($city)));
	}
	
	/**
	 * Format phone number to be valid
	 *
	 * @param string $number Phone number to format
	 * @return string formatted phone number
	 */
	public static function formatPhoneNumber($number)
	{
		return trim(preg_replace('/[^+0-9. ()-]+/', ' ', $number));        
	}
	
	/**
	 * Format product description by removing potential hazardous code
	 * @param string $desc description to be cleaned
	 */
	public static function formatDescription($desc)
	{
		if(method_exists('Tools', 'purifyHTML'))
			$desc = Tools::purifyHTML($desc);
		
		return $desc;
	}    
	
	
	/**
	 * This function will take JSON string and indent it very readable.
	 *
	 * @param string $json to be formatted
	 */
	public static function prettyPrint( $json )
	{
		$result = '';
		$level = 0;
		$in_quotes = false;
		$in_escape = false;
		$ends_line_level = NULL;
		$json_length = Tools::strlen( $json );

		for( $i = 0; $i < $json_length; $i++ ) {
			$char = $json[$i];
			$new_line_level = NULL;
			$post = "";
			if( $ends_line_level !== NULL ) {
				$new_line_level = $ends_line_level;
				$ends_line_level = NULL;
			}
			if ( $in_escape ) {
				$in_escape = false;
			} else if( $char === '"' ) {
				$in_quotes = !$in_quotes;
			} else if( ! $in_quotes ) {
				switch( $char ) {
					case '}': case ']':
						$level--;
						$ends_line_level = NULL;
						$new_line_level = $level;
						break;

					case '{': case '[':
						$level++;
					case ',':
						$ends_line_level = $level;
						break;

					case ':':
						$post = " ";
						break;

					case " ": case "\t": case "\n": case "\r":
						$char = "";
						$ends_line_level = $new_line_level;
						$new_line_level = NULL;
						break;
				}
			} else if ( $char === '\\' ) {
				$in_escape = true;
			}
			if( $new_line_level !== NULL ) {
				$result .= "\n".str_repeat( "\t", $new_line_level );
			}
			$result .= $char.$post;
		}

		return $result;
	}

	
}