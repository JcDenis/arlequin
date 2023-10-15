<?php
/**
 * @file
 * @brief       The plugin arlequin definition
 * @ingroup     arlequin
 *
 * @defgroup    arlequin Plugin arlequin.
 *
 * Allows visitors choose a theme.
 *
 * @author      Oleksandr Syenchuk (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Arlequin',
    'Allows visitors choose a theme',
    'Oleksandr Syenchuk, Pierre Van Glabeke and contributors',
    '2.5',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository'  => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
