<?php
/**
 * @brief arlequin, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Oleksandr Syenchuk, Pierre Van Glabeke and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\arlequin;

use dcCore;
use Dotclear\Core\Process;
use Dotclear\Helper\Network\Http;

class Frontend extends Process
{
    public const COOKIE_THEME_PREFIX = 'dc_theme_';
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

        dcCore::app()->addBehaviors([
            'publicBeforeDocumentV2' => [self::class, 'adjustCache'],
            'initWidgets'            => [Widgets::class, 'initWidgets'],
        ]);

        return true;
    }

    protected static function cookieSuffix(): string
    {
        return base_convert(dcCore::app()->blog->uid, 16, 36);
    }

    public static function adjustCache(): void
    {
        if (!empty($_COOKIE[self::COOKIE_UPDDT_PREFIX . self::cookieSuffix()])) {
            dcCore::app()->cache['mod_ts'][] = (int) $_COOKIE[self::COOKIE_UPDDT_PREFIX . self::cookieSuffix()];
        }
    }

    public static function switchTheme(string $theme): void
    {
        if (dcCore::app()->blog->settings->get('system')->get('theme') == $theme) {
            return;
        }

        if (My::settings()->get('mt_exclude')) {
            if (in_array($theme, explode('/', My::settings()->get('mt_exclude')))) {
                return;
            }
        }

        dcCore::app()->blog->settings->get('system')->set('theme', $theme);
        dcCore::app()->public->theme = $theme;
    }
}
