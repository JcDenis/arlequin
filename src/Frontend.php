<?php

declare(strict_types=1);

namespace Dotclear\Plugin\arlequin;

use ArrayObject;
use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\Network\Http;

/**
 * @brief       arlequin frontend class.
 * @ingroup     arlequin
 *
 * @author      Oleksandr Syenchuk (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Frontend
{
    use TraitProcess;

    /**
     * The arlequin theme cookie.
     *
     * @var     string  COOKIE_THEME_PREFIX
     */
    public const COOKIE_THEME_PREFIX = 'dc_arlequin_theme_';

    /**
     * The arlequin date cookie.
     *
     * @var     string  COOKIE_UPDDT_PREFIX
     */
    public const COOKIE_UPDDT_PREFIX = 'dc_arlequin_date_';

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!empty($_REQUEST['theme'])) {
            // GET parameter has a theme to set
            $theme = is_string($theme = $_REQUEST['theme']) ? $theme : '';

            if ($theme !== '') {
                // Set cookie for 365 days
                setcookie(self::COOKIE_THEME_PREFIX . self::cookieSuffix(), $theme, [
                    'expires' => time() + 31536000,
                    'path'    => '/',
                ]);
                setcookie(self::COOKIE_UPDDT_PREFIX . self::cookieSuffix(), (string) time(), [
                    'expires' => time() + 31536000,
                    'path'    => '/',
                ]);

                // Redirect if needed
                if (isset($_GET['theme'])) {
                    Http::redirect((string) preg_replace('/(\?|&)theme(=.*)?$/', '', Http::getSelfURI()));
                }

                // Switch theme
                self::switchTheme($theme);
            }
        } elseif (!empty($_COOKIE[self::COOKIE_THEME_PREFIX . self::cookieSuffix()])) {
            // A cookie exist, use it

            $theme = is_string($theme = $_COOKIE[self::COOKIE_THEME_PREFIX . self::cookieSuffix()]) ? $theme : '';
            if ($theme !== '') {
                self::switchTheme($theme);
            }
        } else {
            // Restore original theme
            self::switchTheme();
        }

        App::behavior()->addBehaviors([
            'publicBeforeDocumentV2'         => self::adjustCache(...),
            'urlHandlerServeDocumentHeaders' => self::addHeaders(...),
            'initWidgets'                    => Widgets::initWidgets(...),
        ]);

        return true;
    }

    protected static function cookieSuffix(): string
    {
        return base_convert(App::blog()->uid(), 16, 36);
    }

    public static function adjustCache(): void
    {
        if (!empty($_COOKIE[self::COOKIE_UPDDT_PREFIX . self::cookieSuffix()])) {
            $timestamp = is_numeric($timestamp = $_COOKIE[self::COOKIE_UPDDT_PREFIX . self::cookieSuffix()]) ? (int) $timestamp : 0;
            App::cache()->addTime($timestamp);

            // HTTP dates have a one second precision, a page cached by the browser
            // during the second of the theme switch must not be validated by its date.
            // Then the HTTP cache validation relies on content (ETag).
            $since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && is_string($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
            if ($since !== false && $since <= $timestamp) {
                unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
            }
        }
    }

    /**
     * Add HTTP headers to public documents.
     *
     * Pages content depends on theme cookie.
     *
     * @param   ArrayObject<int, string>    $headers    The headers
     */
    public static function addHeaders(ArrayObject $headers): void
    {
        $headers->append('Vary: Cookie');
    }

    public static function switchTheme(?string $theme = null): void
    {
        if ($theme === null || $theme === '') {
            // Restore original theme if any
            $original = My::settings()->getStr('original', false);
            if ($original !== '') {
                // Restore initial theme
                App::cache()->setAvoidCache(true);
                App::blog()->settings()->get('system')->set('theme', $original);
                App::frontend()->theme = $original;
            }

            return;
        }

        $current = App::blog()->settings()->get('system')->getStr('theme', false);
        if ($current === $theme) {
            return;
        }

        // Check if theme is not excluded
        $excluded = array_map(trim(...), explode(';', My::settings()->getStr('exclude', false)));
        if (in_array($theme, $excluded, true)) {
            return;
        }

        // Save original theme
        My::settings()->put('original', $current);

        // Switch to temporary theme
        App::cache()->setAvoidCache(true);
        App::blog()->settings()->get('system')->set('theme', $theme);
        App::frontend()->__set('theme', $theme);
    }
}
