<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

class Redactor
{
    private static $redactedText = '[redacted]';

    // Matches a url up to the first whitespace, quote or angle bracket
    private static $urlPattern = '#https?://[^\s"\'<>]+#i';

    // Query params that mark a url as sensitive, e.g. password reset links. `;` covers `&amp;` encoded urls
    private static $sensitiveParamPattern = '#[?&;](?:action=rp\b|key=)#i';

    private static $redactableFields = ['subject', 'message'];

    /**
     * Run the redactable fields of a log through the redaction filter
     *
     * @param array $log a log entry, either before it's saved or after it's read from the db
     *
     * @return array the log with its redactable fields redacted
     */
    public static function redactLog(array $log): array
    {
        foreach (self::$redactableFields as $field) {
            if (isset($log[$field])) {
                $log[$field] = self::redact($log[$field], $field);
            }
        }

        return $log;
    }

    /**
     * Run the content of a log through the redaction filter
     *
     * @param mixed $content the value of the field being redacted
     * @param string $field the name of the field, either `subject` or `message`
     *
     * @return mixed the redacted content
     */
    private static function redact($content, string $field)
    {
        if (!is_string($content)) {
            return $content;
        }

        return apply_filters('wp_mail_catcher_redact', $content, $field);
    }

    /**
     * Default redaction, replaces any url that contains a sensitive query param
     * Hooked into the redaction filter and can be disabled from the settings screen
     *
     * @param string $content
     *
     * @return string
     */
    public static function redactSensitiveUrls($content)
    {
        if (!is_string($content) || !Settings::get('redact_sensitive_urls')) {
            return $content;
        }

        return preg_replace_callback(self::$urlPattern, function ($matches) {
            return preg_match(self::$sensitiveParamPattern, $matches[0]) ? self::$redactedText : $matches[0];
        }, $content);
    }
}
