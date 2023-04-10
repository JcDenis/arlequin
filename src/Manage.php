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

try {
    include __DIR__ . '/inc/models.php';

    // Initialisation
    dcCore::app()->blog->settings->addNamespace('arlequinMulti');
    [$mt_cfg, $mt_exclude] = adminArlequin::loadSettings(dcCore::app()->blog->settings);
    if (adminArlequin::$initialized) {
        dcAdminNotices::AddSuccessNotice(__('Settings have been reinitialized.'));
    }

    // Enregistrement des données depuis les formulaires
    if (isset($_POST['mt_action_config'])) {
        $mt_cfg['e_html'] = $_POST['e_html'];
        $mt_cfg['a_html'] = $_POST['a_html'];
        $mt_cfg['s_html'] = $_POST['s_html'];
        $mt_exclude       = $_POST['mt_exclude'];
    }

    // Traitement des requêtes
    if (isset($_POST['mt_action_config'])) {
        dcCore::app()->blog->settings->arlequinMulti->put('mt_cfg', serialize($mt_cfg));
        dcCore::app()->blog->settings->arlequinMulti->put('mt_exclude', $mt_exclude);
        dcAdminNotices::AddSuccessNotice(__('System settings have been updated.'));
        dcCore::app()->blog->triggerBlog();
        dcCore::app()->adminurl->redirect('admin.plugin.' . basename(__DIR__), ['config' => 1]);
    }
    if (isset($_POST['mt_action_restore'])) {
        dcCore::app()->blog->settings->arlequinMulti->drop('mt_cfg');
        dcCore::app()->blog->settings->arlequinMulti->drop('mt_exclude');
        dcAdminNotices::AddSuccessNotice(__('Settings have been reinitialized.'));
        dcCore::app()->blog->triggerBlog();
        dcCore::app()->adminurl->redirect('admin.plugin.' . basename(__DIR__), ['restore' => 1]);
    }
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

// Headers
$jsModels = '';
$cslashes = "\n\"\'";
foreach ($mt_models as $m) { // @phpstan-ignore-line
    $jsModels .= "\t" .
        'arlequin.addModel(' .
        '"' . html::escapeJS($m['name']) . '",' .
        '"' . addcslashes($m['s_html'], $cslashes) . '",' .
        '"' . addcslashes($m['e_html'], $cslashes) . '",' .
        '"' . addcslashes($m['a_html'], $cslashes) . '"' .
        ");\n";
}

// DISPLAY
echo '
<html><head><title>' . __('Arlequin') . '</title>' .
dcPage::jsLoad(dcPage::getPF(basename(__DIR__) . '/js/models.js')) . '
<script type="text/javascript">
//<![CDATA[
arlequin.msg.predefined_models = "' . html::escapeJS(__('Predefined models')) . '";
arlequin.msg.select_model = "' . html::escapeJS(__('Select a model')) . '";
arlequin.msg.user_defined = "' . html::escapeJS(__('User defined')) . '";
$(function() {
	arlequin.addDefault();
' . $jsModels . '
});
//]]>
</script>
</head><body>' .
dcPage::breadcrumb([
    html::escapeHTML(dcCore::app()->blog->name)              => '',
    '<span class="page-title">' . __('Arlequin') . '</span>' => '',
]) .
dcPage::notices() . '

<form action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)) . '" method="post">
<h4>' . __('Switcher display format') . '</h4>
<div id="models"></div>

<div class="two-boxes odd">
<p><label for="s_html">' . __('Switcher HTML code:') . '</label> ' .
    form::textArea('s_html', 50, 10, html::escapeHTML($mt_cfg['s_html'])) . '</p>
</div><div class="two-boxes even">
<p><label for="e_html">' . __('Item HTML code:') . '</label> ' .
    form::field('e_html', 50, 200, html::escapeHTML($mt_cfg['e_html'])) . '</p>
<p><label for="a_html">' . __('Active item HTML code:') . '</label> ' .
    form::field('a_html', 50, 200, html::escapeHTML($mt_cfg['a_html'])) . '</p>
</div><div class="two-boxes odd">
<p><label for="mt_exclude">' . __("Excluded themes (separated by slashs '/'):") . '</label> ' .
    form::field('mt_exclude', 50, 200, html::escapeHTML($mt_exclude)) . '</p>
<p class="info">' . __('The names to be taken into account are those of the theme files.') . '</p>
</div>
<p>
    <input type="submit" name="mt_action_config" value="' . __('Save') . '" />
	<input type="submit" name="mt_action_restore" value="' . __('Restore defaults') . '" />' .
    dcCore::app()->formNonce() . '</p>
</form>';

dcPage::helpBlock('arlequin'); ?>
</body></html>
