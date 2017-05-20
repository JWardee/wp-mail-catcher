<?php
class GeneralHelper
{
    public static function arrayToSqlString($array)
    {
        // TODO: Need to sanitise $ids
        if (is_array($array)) {
            return implode(',', $array);
        }

        return $array;
	}
}


