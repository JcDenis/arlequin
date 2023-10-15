<?php

declare(strict_types=1);

namespace Dotclear\Plugin\arlequin;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
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

/**
 * @brief       arlequin manage class.
 * @ingroup     arlequin
 *
 * @author      Oleksandr Syenchuk (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            $s = My::settings();

            $model   = json_decode((string) $s->get('model'), true);
            $exclude = $s->get('exclude');

            // initialize settings
            $initialized = false;
            if ($model === false || $exclude === null || !(isset($model['e_html']) && isset($model['a_html']) && isset($model['s_html']))) {
                $model = My::defaultModel();
                $s->put('model', json_encode($model), 'string', 'Arlequin configuration');
                $s->put('exclude', 'customCSS', 'string', 'Excluded themes');

                Notices::AddSuccessNotice(__('Settings have been reinitialized.'));
                App::blog()->triggerBlog();
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

                Notices::AddSuccessNotice(__('System settings have been updated.'));
                App::blog()->triggerBlog();
                My::redirect(['config' => 1]);
            }

            // restore settings
            if (isset($_POST['mt_action_restore'])) {
                $s->drop('model');
                $s->drop('exclude');

                Notices::AddSuccessNotice(__('Settings have been reinitialized.'));
                App::blog()->triggerBlog();
                My::redirect(['restore' => 1]);
            }
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $models = new ArrayObject(My::distributedModels());

        App::behavior()->callBehavior('arlequinAddModels', $models);

        $models = iterator_to_array($models);
        $s      = My::settings();
        $model  = json_decode((string) $s->get('model'), true);
        $model  = array_merge(My::defaultModel(), is_array($model) ? $model : []);
        $header = '';

        foreach ($models as $m) {
            $m = array_merge(My::defaultModel(), $m);
            $header .= "\t" .
                'arlequin.addModel(' .
                '"' . Html::escapeJS($m['name']) . '",' .
                '"' . addcslashes($m['s_html'], "\n\"\'") . '",' .
                '"' . addcslashes($m['e_html'], "\n\"\'") . '",' .
                '"' . addcslashes($m['a_html'], "\n\"\'") . '"' .
                ");\n";
        }

        Page::openModule(
            My::name(),
            Page::jsLoad('models') . '
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
        Page::breadcrumb([
            Html::escapeHTML(App::blog()->name()) => '',
            My::name()                            => '',
        ]) .
        Notices::getNotices() .

        (new Form(My::id() . 'form'))->method('post')->action(App::backend()->getPageURL())->fields([
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
                (new Submit(['mt_action_config']))->value(__('Save')),
                (new Submit(['mt_action_restore']))->value(__('Restore defaults')),
                ... My::hiddenFields(),
            ]),
        ])->render();

        Page::helpBlock('arlequin');
        Page::closeModule();
    }
}
