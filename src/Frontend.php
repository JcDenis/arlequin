<?php

declare(strict_types=1);

namespace Dotclear\Plugin\arlequin;

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
    public const COOKIE_THEME_PREFIX = 'dc_theme_';

    /**
     * The arlequin date cookie.
     *
     * @var     string  COOKIE_UPDDT_PREFIX
     */
    public const COOKIE_UPDDT_PREFIX = 'dc_user_upddt_';

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
            # Set cookie for 365 days
            setcookie(self::COOKIE_THEME_PREFIX . self::cookieSuffix(), $_REQUEST['theme'], time() + 31536000, '/');
            setcookie(self::COOKIE_UPDDT_PREFIX . self::cookieSuffix(), (string) time(), time() + 31536000, '/');

            # Redirect if needed
            if (isset($_GET['theme'])) {
                Http::redirect((string) preg_replace('/(\?|&)theme(=.*)?$/', '', Http::getSelfURI()));
            }

            # Switch theme
            self::switchTheme($_REQUEST['theme']);
        } elseif (!empty($_COOKIE[self::COOKIE_THEME_PREFIX . self::cookieSuffix()])) {
            self::switchTheme($_COOKIE[self::COOKIE_THEME_PREFIX . self::cookieSuffix()]);
        }

        App::behavior()->addBehaviors([
            'publicBeforeDocumentV2' => self::adjustCache(...),
            'initWidgets'            => Widgets::initWidgets(...),
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
            App::cache()->addTime((int) $_COOKIE[self::COOKIE_UPDDT_PREFIX . self::cookieSuffix()]);
        }
    }

    public static function switchTheme(string $theme): void
    {
        if (App::blog()->settings()->get('system')->get('theme') == $theme) {
            return;
        }

        App::cache()->setAvoidCache(true);

        if (My::settings()->get('mt_exclude')) {
            if (in_array($theme, explode('/', My::settings()->get('mt_exclude')))) {
                return;
            }
        }

        App::blog()->settings()->get('system')->set('theme', $theme);
        App::frontend()->theme = $theme;
    }
}
