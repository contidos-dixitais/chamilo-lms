<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Chamilo\PluginBundle\Smowl\Form\FrmEdit;

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_admin_script();

if (!isset($_REQUEST['id'])) {
    api_not_allowed(true);
}

$params = [
    'cidReq' => $_GET['cidReq'],
    'id_session' => isset($_GET['id_session']) ? intval($_GET['id_session']) : 0,
    'gidReq' => isset($_GET['gidReq']) ? intval($_GET['gidReq']) : 0,
    'gradebook' => isset($_GET['gradebook']) ? intval($_GET['gradebook']) : 0,
    'origin' => isset($_GET['origin']) ? $_GET['origin'] : 0,
];

$toolId = intval($_REQUEST['id']);

$plugin = SmowlPlugin::create();
$em = Database::getManager();

/** @var SmowlTool $tool */
$tool = $em->find('ChamiloPluginBundle:Smowl\SmowlTool', $toolId);

if (!$tool) {
    Display::addFlash(
        Display::return_message($plugin->get_lang('NoTool'), 'error')
    );

    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php');
    exit;
}

$form = new FrmEdit('smowl_edit_tool', [], $tool);
$form->build();

if ($form->validate()) {
    $formValues = $form->exportValues();

    $tool
        ->setName($formValues['name'])
        ->setDescription(
            empty($formValues['description']) ? null : $formValues['description']
        );

    if (null === $tool->getParent()) {
        $tool->setLaunchUrl($formValues['launch_url']);
    }

    if (null == $tool->getParent()) {
        /** @var SmowlTool $child */
        foreach ($tool->getChildren() as $child) {
            $child
                ->setLaunchUrl($tool->getLaunchUrl());

            $em->persist($child);

            $courseTool = $plugin->findCourseToolByLink(
                $child->getCourse(),
                $child
            );

            $plugin->updateCourseTool($courseTool, $child);
        }
    } else {
        $courseTool = $plugin->findCourseToolByLink(
            $tool->getCourse(),
            $tool
        );

        $plugin->updateCourseTool($courseTool, $tool);
    }

    $em->persist($tool);
    $em->flush();

    Display::addFlash(
        Display::return_message($plugin->get_lang('ToolEdited'), 'success')
    );

    if (empty($_GET['cidReq'])) {    
        header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php');
    } else {
        header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/configure.php?'.http_build_query($params));
    }

    exit;
} else {
    $form->setDefaultValues();
}

$interbreadcrumb[] = ['url' => api_get_path(WEB_CODE_PATH).'admin/index.php', 'name' => get_lang('PlatformAdmin')];
$interbreadcrumb[] = ['url' => api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php', 'name' => $plugin->get_title()];

$template = new Template($plugin->get_lang('EditExternalTool'));
$template->assign('form', $form->returnForm());

$content = $template->fetch('smowl/view/add.tpl');

$template->assign('header', $plugin->get_title());
$template->assign('content', $content);
$template->display_one_col_template();
