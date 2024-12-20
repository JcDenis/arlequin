<?php

declare(strict_types=1);

namespace Dotclear\Plugin\arlequin;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;
use Dotclear\Module\ModuleDefine;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       arlequin frontend class.
 * @ingroup     arlequin
 *
 * @author      Oleksandr Syenchuk (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Widgets
{
    public static function initWidgets(WidgetsStack $w): void
    {
        $w->create(
            'arlequin',
            My::name(),
            self::parseWidget(...),
            null,
            __('Theme switcher')
        )
        ->addTitle(__('Choose a theme'))
        ->addHomeOnly()
        ->addContentOnly()
        ->addClass()
        ->addOffline();
    }

    public static function parseWidget(WidgetsElement $w): string
    {
        if ($w->offline || !$w->checkHomeOnly(App::url()->type)) {
            return '';
        }

        $model   = json_decode((string) My::settings()->get('model'), true);
        $exclude = explode(';', (string) My::settings()->get('exclude'));
        $modules = array_diff_key(App::themes()->getDefines(['state' => ModuleDefine::STATE_ENABLED], true), array_flip($exclude));
        if (!is_array($model) || empty($modules)) {
            return '';
        }

        # Current page URL and the associated query string. Note : the URL for
        # the switcher ($s_url) is different to the URL for an item ($e_url)
        $s_url = $e_url = Http::getSelfURI();

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
        foreach ($modules as $id => $module) {
            $id = (string) $id;
            if ($id == App::frontend()->theme) {
                $format = $model['a_html'];
            } else {
                $format = $model['e_html'];
            }

            if ($replace) {
                $e_url = preg_replace(
                    '/(\\?|&)(theme\\=)([^&]*)/',
                    '$1${2}' . addcslashes($id, '$\\'),
                    (string) $e_url
                );
                $val = '';
            } else {
                $val = Html::escapeHTML(rawurlencode($id));
            }
            $res .= sprintf(
                $format,
                $e_url,
                $ext,
                $val,
                Html::escapeHTML($module['name']),
                Html::escapeHTML($module['desc']),
                Html::escapeHTML($id)
            );
        }

        # Nothing to display
        if (!trim($res)) {
            return '';
        }

        return $w->renderDiv(
            (bool) $w->content_only,
            'arlequin ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') . sprintf($model['s_html'], $s_url, $res)
        );
    }
}
