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

/**
 * Plugin definitions.
 */
class My
{
    /** @var string Required php version */
    public const PHP_MIN = '7.4';

    /**
     * This module id.
     */
    public static function id(): string
    {
        return basename(dirname(__DIR__));
    }

    /**
     * This module name.
     */
    public static function name(): string
    {
        return __((string) dcCore::app()->plugins->moduleInfo(self::id(), 'name'));
    }

    /**
     * Check php version.
     */
    public static function phpCompliant(): bool
    {
        return version_compare(phpversion(), self::PHP_MIN, '>=');
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
            'e_html' => '<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
            'a_html' => '<li><strong>%4$s</strong></li>',
            's_html' => '<ul>%2$s</ul>',
        ];
    }
}
