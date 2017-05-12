<?php
class public static GeneralHelper
{
	public static arrayToString($array, $delimiter = ' ', $recursive = true)
	{
		$result = '';

		foreach ($array as $array_element) {
			if (is_array($array_element) && $recursive == true) {
				$result .= $delimiter . GeneralHelper::arrayToString($array_element);
			} elseif (!empty($array_element)) {
				$result .= $delimiter . $array_element;
			}
		}

		return $result;
	}
}