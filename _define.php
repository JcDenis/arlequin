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
    '2.5.1',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-02-24T23:31:12+00:00',
    ]
);
