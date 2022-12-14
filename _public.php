<?php
/**
 * @brief arlequin, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Oleksandr Syenchuk, Pierre Van Glabeke and contributors
 *
 * @copyright Jean-Crhistian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH')) {
    return;
}

require __DIR__ . '/_widgets.php';

publicArlequinEngine::init();
dcCore::app()->addBehavior('publicBeforeDocumentV2', ['publicArlequinEngine','adjustCache']);
dcCore::app()->tpl->addValue('themesList', ['publicArlequinInterface','template']);

class publicArlequinEngine
{
    public static $cookie_theme;
    public static $cookie_upddt;

    public static function init()
    {
        $cname              = base_convert(dcCore::app()->blog->uid, 16, 36);
        self::$cookie_theme = 'dc_theme_' . $cname;
        self::$cookie_upddt = 'dc_user_upddt_' . $cname;

        if (!empty($_REQUEST['theme'])) {
            # Set cookie for 365 days
            setcookie(self::$cookie_theme, $_REQUEST['theme'], time() + 31536000, '/');
            setcookie(self::$cookie_upddt, (string) time(), time() + 31536000, '/');

            # Redirect if needed
            if (isset($_GET['theme'])) {
                $p = '/(\?|&)theme(=.*)?$/';
                http::redirect(preg_replace($p, '', http::getSelfURI()));
            }

            # Switch theme
            self::switchTheme($_REQUEST['theme']);
        } elseif (!empty($_COOKIE[self::$cookie_theme])) {
            self::switchTheme($_COOKIE[self::$cookie_theme]);
        }
    }

    public static function adjustCache()
    {
        if (!empty($_COOKIE[self::$cookie_upddt])) {
            dcCore::app()->cache['mod_ts'][] = (int) $_COOKIE[self::$cookie_upddt];
        }
    }

    public static function switchTheme($theme)
    {
        if (dcCore::app()->blog->settings->arlequinMulti->mt_exclude) {
            if (in_array($theme, explode('/', dcCore::app()->blog->settings->arlequinMulti->mt_exclude))) {
                return;
            }
        }

        dcCore::app()->public->theme = dcCore::app()->blog->settings->system->theme = $theme;
    }
}

class publicArlequinInterface
{
    public static function arlequinWidget($w)
    {
        return self::getHTML($w);
    }

    public static function template($attr)
    {
        return '<?php echo publicArlequinInterface::getHTML(); ?>';
    }

    public static function getHTML($w = false)
    {
        if ($w->offline) {
            return;
        }

        if (!$w->checkHomeOnly(dcCore::app()->url->type)) {
            return;
        }

        $cfg = @unserialize(dcCore::app()->blog->settings->arlequinMulti->get('mt_cfg'));
        if ($cfg === false || ($names = self::getNames()) === false) {
            return;
        }

        # Current page URL and the associated query string. Note : the URL for
        # the switcher ($s_url) is different to the URL for an item ($e_url)
        $s_url = $e_url = http::getSelfURI();

        # If theme setting is already present in URL, we will replace its value
        $replace = preg_match('/(\\?|&)theme\\=[^&]*/', $e_url);

        # URI extension to send theme setting by query string
        if ($replace) {
            $ext = '';
        } elseif (strpos($e_url, '?') === false) {
            $ext = '?theme=';
        } else {
            $ext = (substr($e_url, -1) == '?' ? '' : '&amp;') . 'theme=';
        }

        $res = '';
        foreach ($names as $k => $v) {
            if ($k == dcCore::app()->public->theme) {
                $format = $cfg['a_html'];
            } else {
                $format = $cfg['e_html'];
            }

            if ($replace) {
                $e_url = preg_replace(
                    '/(\\?|&)(theme\\=)([^&]*)/',
                    '$1${2}' . addcslashes($k, '$\\'),
                    $e_url
                );
                $val = '';
            } else {
                $val = html::escapeHTML(rawurlencode($k));
            }
            $res .= sprintf(
                $format,
                $e_url,
                $ext,
                $val,
                html::escapeHTML($v['name']),
                html::escapeHTML($v['desc']),
                html::escapeHTML($k)
            );
        }

        # Nothing to display
        if (!trim($res)) {
            return;
        }

        $res = sprintf($cfg['s_html'], $s_url, $res);

        if ($w) {
            return $w->renderDiv(
                $w->content_only,
                'arlequin ' . $w->class,
                '',
                ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '') . $res
            );
        }

        return $res;
    }

    public static function getNames()
    {
        $mt_exclude = dcCore::app()->blog->settings->arlequinMulti->mt_exclude;
        $exclude    = [];
        if (!empty($mt_exclude)) {
            $exclude = array_flip(explode('/', dcCore::app()->blog->settings->arlequinMulti->mt_exclude));
        }

        $names = array_diff_key(dcCore::app()->themes->getModules(), $exclude);

        return empty($names) ? false : $names;
    }
}
