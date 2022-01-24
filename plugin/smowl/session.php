<?php
/* For licensing terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_admin_script(false);

$plugin = SmowlPlugin::create();
$webPluginPath = api_get_path(WEB_PLUGIN_PATH).'smowl/';

$request = Request::createFromGlobals();
$smowlToolId = $request->query->getInt('id');

$em = Database::getManager();

try {
    if ($plugin->get('enabled') !== 'true') {
        throw new Exception(get_lang('NotAllowed'));
    }

    /** @var SmowlTool $tool */
    $tool = $em->find('ChamiloPluginBundle:Smowl\SmowlTool',$smowlToolId);

    if (!$tool) {
        throw new Exception($plugin->get_lang('NoTool'));
    }

    if ($tool->getParent()) {
        throw new Exception($plugin->get_lang('NoAllowed'));
    }

    $content = '';

    $form = new FormValidator('frm_multiply', 'post', api_get_self().'?id='.$tool->getId());
    $form->addLabel($plugin->get_lang('Tool'), $tool->getName());
    $form->addSelectAjax(
        'sessions',
        get_lang('Sessions'),
        $slctCourses,
        [
            'url' => api_get_path(WEB_AJAX_PATH).'session.ajax.php?'.http_build_query(
                [
                    'a' => 'search_session'
                ]
            ),
            'multiple' => false
        ]
    );
    $form->addHidden('tool_id', $tool->getId());
    $form->addButtonExport(get_lang('Next'));

    if ($form->validate()) {
        $em = Database::getManager();
        $formValues = $form->exportValues();
        $formValues['sessions'] = empty($formValues['sessions']) ? [] : $formValues['sessions'];

        if (!$formValues['sessions']) {
            Display::addFlash(
                Display::return_message($plugin->get_lang('NeedToSelectASession'), 'error', false)
            );
            header('Location:'.api_get_self());
            exit;
        }

        header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/multiply_session.php?id='.$formValues['tool_id'].'&session_id='.$formValues['sessions']);

        exit;
    }

    $form->protect();

    $content = $form->returnForm();

    $interbreadcrumb[] = ['url' => api_get_path(WEB_CODE_PATH).'admin/index.php', 'name' => get_lang('PlatformAdmin')];
    $interbreadcrumb[] = ['url' => api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php', 'name' => $plugin->get_title()];

    $template = new Template($plugin->get_lang('AddInCourses'));
    $template->assign('header', $plugin->get_lang('AddInCourses'));
    $template->assign('content', $content);
    $template->display_one_col_template();
} catch (Exception $exception) {
    Display::addFlash(
        Display::return_message($exception->getMessage(), 'error')
    );

    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php');
}
