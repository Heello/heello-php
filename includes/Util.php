<?
/**
 * Some basic utility functions to help throughout the Heello API library.
 */

namespace Heello;

class Util {
	/**
	 * Grabs all values from GET and POST params, merges them into a
	 * single array, and then checks for a single value.
	 *
	 * @param $key The key to check for
	 * @param $onfail The value to return if the array key isn't found
	 */
	public static function gpval($key, $onfail = null) {
		$gp = array_merge($_GET, $_POST);
		return isset($gp[$key]) ? ($gp[$key] ? $gp[$key] : $onfail) : $onfail;
	}

	/*
	 * Checks to see if the given key exists in the given array, and returns
	 * it if so. Otherwise, returns the default.
	 */
	function get($arr, $key, $default = null) {
		return isset($arr[$key])?$arr[$key]:$default;
	}
}
