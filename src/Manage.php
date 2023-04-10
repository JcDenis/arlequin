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

use ArrayObject;
use dcCore;
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Form\{
    Div,
    Form,
    Input,
    Label,
    Note,
    Para,
    Submit,
    Text,
    Textarea
};
use Dotclear\Helper\Html\Html;
use Exception;

class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && My::phpCompliant()
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        try {
            $s = dcCore::app()->blog->settings->get(My::id());

            $model   = json_decode((string) $s->get('model'), true);
            $exclude = $s->get('exclude');

            // initialize settings
            $initialized = false;
            if ($model === false || $exclude === null || !(isset($model['e_html']) && isset($model['a_html']) && isset($model['s_html']))) {
                $model = My::defaultModel();
                $s->put('model', json_encode($model), 'string', 'Arlequin configuration');
                $s->put('exclude', 'customCSS', 'string', 'Excluded themes');

                dcPage::AddSuccessNotice(__('Settings have been reinitialized.'));
                dcCore::app()->blog->triggerBlog();
            }

            // collect settings
            if (isset($_POST['mt_action_config'])) {
                $model['e_html'] = $_POST['e_html'];
                $model['a_html'] = $_POST['a_html'];
                $model['s_html'] = $_POST['s_html'];
                $exclude         = $_POST['exclude'];
            }

            // save settings
            if (isset($_POST['mt_action_config'])) {
                $s->put('model', json_encode($model));
                $s->put('exclude', $exclude);

                dcPage::AddSuccessNotice(__('System settings have been updated.'));
                dcCore::app()->blog->triggerBlog();
                dcCore::app()->adminurl->redirect('admin.plugin.' . My::id(), ['config' => 1]);
            }

            // restore settings
            if (isset($_POST['mt_action_restore'])) {
                $s->drop('model');
                $s->drop('exclude');

                dcPage::AddSuccessNotice(__('Settings have been reinitialized.'));
                dcCore::app()->blog->triggerBlog();
                dcCore::app()->adminurl->redirect('admin.plugin.' . My::id(), ['restore' => 1]);
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        $models = new ArrayObject(My::distributedModels());

        dcCore::app()->callBehavior('arlequinAddModels', $models);

        $models = iterator_to_array($models);
        $s      = dcCore::app()->blog->settings->get(My::id());
        $model  = json_decode((string) $s->get('model'), true);
        $header = '';

        foreach ($models as $m) {
            $header .= "\t" .
                'arlequin.addModel(' .
                '"' . Html::escapeJS($m['name']) . '",' .
                '"' . addcslashes($m['s_html'], "\n\"\'") . '",' .
                '"' . addcslashes($m['e_html'], "\n\"\'") . '",' .
                '"' . addcslashes($m['a_html'], "\n\"\'") . '"' .
                ");\n";
        }

        dcPage::openModule(
            My::name(),
            dcPage::jsModuleLoad(My::id() . '/js/models.js') . '
            <script type="text/javascript">
            //<![CDATA[
            arlequin.msg.predefined_models = "' . Html::escapeJS(__('Predefined models')) . '";
            arlequin.msg.select_model = "' . Html::escapeJS(__('Select a model')) . '";
            arlequin.msg.user_defined = "' . Html::escapeJS(__('User defined')) . '";
            $(function() {
            	arlequin.addDefault();
            ' . $header . '
            });
            //]]>
            </script>'
        );

        echo
        dcPage::breadcrumb([
            Html::escapeHTML(dcCore::app()->blog->name) => '',
            My::name()                                  => '',
        ]) .
        dcPage::notices() .

        (new Form(My::id() . 'form'))->method('post')->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id()))->fields([
            (new Text('h4', __('Switcher display format'))),
            (new Div())->id('models'),
            (new Div())->class('two-boxes odd')->items([
                (new Para())->items([
                    (new Label(__('Switcher HTML code:'), Label::OUTSIDE_LABEL_BEFORE))->for('s_html'),
                    (new Textarea('s_html', Html::escapeHTML($model['s_html'])))->cols(50)->rows(10),
                ]),
            ]),
            (new Div())->class('two-boxes even')->items([
                (new Para())->items([
                    (new Label(__('Item HTML code:'), Label::OUTSIDE_LABEL_BEFORE))->for('e_html'),
                    (new Input('e_html'))->size(50)->maxlenght(200)->value(Html::escapeHTML($model['e_html'])),
                ]),
                (new Para())->items([
                    (new Label(__('Active item HTML code:'), Label::OUTSIDE_LABEL_BEFORE))->for('a_html'),
                    (new Input('a_html'))->size(50)->maxlenght(200)->value(Html::escapeHTML($model['a_html'])),
                ]),
            ]),
            (new Div())->class('two-boxes odd')->items([
                (new Para())->items([
                    (new Label(__('Excluded themes:'), Label::OUTSIDE_LABEL_BEFORE))->for('exclude'),
                    (new Input('exclude'))->size(50)->maxlenght(200)->value(Html::escapeHTML($s->get('exclude'))),
                ]),
                (new Note())->class('form-note')->text('Semicolon separated list of themes IDs (theme folder name). Ex: ductile;berlin'),
            ]),
            (new Para())->separator(' ')->items([
                dcCore::app()->formNonce(false),
                (new Submit(['mt_action_config']))->value(__('Save')),
                (new Submit(['mt_action_restore']))->value(__('Restore defaults')),
            ]),
        ])->render();

        dcPage::helpBlock('arlequin');
        dcPage::closeModule();
    }
}