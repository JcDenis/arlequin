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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

$mt_models = [];

/* Syntaxe pour ajouter vos propres modèles prédéfinis :

$mt_models[] = array(
    'name'=>__('Model name'),	// Nom du modèle prédéfini, éventuellement
                            // traduit dans un fichier de langue
    's_html'=>'[HTML code]',		// Code HTML du sélecteur de thème
    'e_html'=>'[HTML code]',		// Code HTML d'un item pouvant être sélectionné
    'a_html'=>'[HTML code]'		// Code HTML d'un item actif (thème sélectionné)
);

//*/

$mt_models[] = [
    'name'   => __('Bullets list'),
    's_html' => '<ul>%2$s</ul>',
    'e_html' => '<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
    'a_html' => '<li><strong>%4$s</strong></li>',
];

$mt_models[] = [
    'name'   => __('Scrolled list'),
    's_html' => '<form action="%1$s" method="post">' . "\n" .
        '<p><select name="theme">' . "\n" .
        '%2$s' . "\n" .
        '</select>' . "\n" .
        '<input type="submit" value="' . __('ok') . '"/></p>' . "\n" .
        '</form>',
    'e_html' => '<option value="%3$s">%4$s</option>',
    'a_html' => '<option value="%3$s" selected="selected" disabled="disabled">%4$s (' . __('active theme') . ')</option>',
];
