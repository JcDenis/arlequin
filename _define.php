<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007,2015                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		'Arlequin',
	/* Description*/	'Allows visitors choose a theme',
	/* Author */		'Oleksandr Syenchuk, Pierre Van Glabeke',
	/* Version */		'1.4',
	/* Properties */
	array(
		'permissions' => 'contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.19',
		'support' => 'http://forum.dotclear.org/viewtopic.php?id=48345',
		'details' => 'http://plugins.dotaddict.org/dc2/details/arlequin'
		)
);