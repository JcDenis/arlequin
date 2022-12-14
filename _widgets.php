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

dcCore::app()->addBehavior('initWidgets', ['adminArlequin','initWidgets']);

class adminArlequin
{
    public static $initialized = false;

    public static function initWidgets($w)
    {
        $w->create(
            'arlequin',
            __('Arlequin'),
            ['publicArlequinInterface','arlequinWidget'],
            null,
            __('Theme switcher')
        )
        ->addTitle(__('Choose a theme'))
        ->addHomeOnly()
        ->addContentOnly()
        ->addClass()
        ->addOffline();
    }

    public static function getDefaults()
    {
        return [
            'e_html' => '<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
            'a_html' => '<li><strong>%4$s</strong></li>',
            's_html' => '<ul>%2$s</ul>',
        ];
    }

    public static function loadSettings($settings)
    {
        self::$initialized = false;
        $mt_cfg            = @unserialize($settings->arlequinMulti->get('mt_cfg'));
        $mt_exclude        = $settings->arlequinMulti->get('mt_exclude');

        // Paramètres corrompus ou inexistants
        if ($mt_cfg === false || $mt_exclude === null || !(isset($mt_cfg['e_html']) && isset($mt_cfg['a_html']) && isset($mt_cfg['s_html']))) {
            $mt_cfg = adminArlequin::getDefaults();
            $settings->addNamespace('arlequinMulti');
            $settings->arlequinMulti->put('mt_cfg', serialize($mt_cfg), 'string', 'Arlequin configuration');
            $settings->arlequinMulti->put('mt_exclude', 'customCSS', 'string', 'Excluded themes');
            self::$initialized = true;
            dcCore::app()->blog->triggerBlog();
        }

        return [$mt_cfg,$mt_exclude];
    }
}
