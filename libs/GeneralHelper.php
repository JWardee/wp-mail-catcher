<?php
class GeneralHelper
{
    public static function arrayToString($pieces, $glue = ', ')
    {
        if (empty($pieces)) {
            return '';
        }

        if (is_array($pieces)) {
            foreach ($pieces as $r_pieces) {
                if (is_array($r_pieces)) {
                    $retVal[] = GeneralHelper::arrayToString($glue, $r_pieces);
                } else {
                    $retVal[] = $r_pieces;
                }
            }

            return implode($glue, $retVal);
        }

        return $pieces;
	}
}


