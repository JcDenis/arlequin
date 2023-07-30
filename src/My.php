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
use Dotclear\Module\MyPlugin;

/**
 * This module definitions.
 */
class My extends MyPlugin
{
    public static function checkCustomContext(int $context): ?bool
    {
        return !in_array($context, [My::BACKEND, My::MANAGE, My::MENU]) ? null :
            defined('DC_CONTEXT_ADMIN')
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
            ]), dcCore::app()->blog->id);
    }

    /**
     * Get distributed models.
     *
     * Use Behavior arlequinAddModels to add models with synthax:
     *  [
     *      'name'=>__('Model name'),   // Nom du modèle prédéfini
     *      's_html'=>'[HTML code]',    // Code HTML du sélecteur de thème
     *      'e_html'=>'[HTML code]',    // Code HTML d'un item pouvant être sélectionné
     *      'a_html'=>'[HTML code]'     // Code HTML d'un item actif (thème sélectionné)
     *  ]
     */
    public static function distributedModels(): array
    {
        return [
            [
                'name'   => __('Bullets list'),
                's_html' => '<ul>%2$s</ul>',
                'e_html' => '<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
                'a_html' => '<li><strong>%4$s</strong></li>',
            ],
            [
                'name'   => __('Scrolled list'),
                's_html' => '<form action="%1$s" method="post">' . "\n" .
                    '<p><select name="theme">' . "\n" .
                    '%2$s' . "\n" .
                    '</select>' . "\n" .
                    '<input type="submit" value="' . __('ok') . '"/></p>' . "\n" .
                    '</form>',
                'e_html' => '<option value="%3$s">%4$s</option>',
                'a_html' => '<option value="%3$s" selected="selected" disabled="disabled">%4$s (' . __('active theme') . ')</option>',
            ],
        ];
    }

    public static function defaultModel(): array
    {
        return [
            'name'   => __('Default'),
            'e_html' => '<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
            'a_html' => '<li><strong>%4$s</strong></li>',
            's_html' => '<ul>%2$s</ul>',
        ];
    }
}
