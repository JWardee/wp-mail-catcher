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

            // Remove empty strings from array
            $tmp = array_filter($retVal, function($value) {
                return !empty($value);
            });

            return implode($glue, $tmp);
        }

        return $pieces;
	}

    public static function slug_to_label($slug)
    {
        return ucfirst(str_replace('_', ' ', $slug));

    }
}


