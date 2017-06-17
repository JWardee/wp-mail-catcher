<?php
class GeneralHelper
{
    public static function arrayToSqlString($pieces, $glue = ', ')
    {
        if (is_array($pieces)) {
            foreach ($pieces as $r_pieces) {
                if (is_array($r_pieces)) {
                    $retVal[] = GeneralHelper::arrayToSqlString($glue, $r_pieces);
                } else {
                    $retVal[] = $r_pieces;
                }
            }
        }

        return implode($glue, $retVal);
	}
}


