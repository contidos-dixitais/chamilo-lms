<?php
/* For license terms, see /license.txt */
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Chamilo\PluginBundle\Smowl\Form\FrmAdd;

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_admin_script();

$plugin = SmowlPlugin::create();

$em = Database::getManager();

$form = new FrmAdd('smowl_create_tool');
$form->build();

if ($form->validate()) {
    $formValues = $form->exportValues();

    $externalTool = new SmowlTool();
    $externalTool
        ->setName($formValues['name'])
        ->setDescription(
            empty($formValues['description']) ? null : $formValues['description']
        )
        ->setCourse(null);

    $externalTool
        ->setLaunchUrl($formValues['launch_url']);

    $em->persist($externalTool);
    $em->flush();

    Display::addFlash(
        Display::return_message($plugin->get_lang('ToolAdded'), 'success')
    );

    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php');
    exit;
}

$form->setDefaultValues();

$interbreadcrumb[] = ['url' => api_get_path(WEB_CODE_PATH).'admin/index.php', 'name' => get_lang('PlatformAdmin')];
$interbreadcrumb[] = ['url' => api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php', 'name' => $plugin->get_title()];

$pageTitle = $plugin->get_lang('AddExternalTool');

$template = new Template($pageTitle);
$template->assign('form', $form->returnForm());

$content = $template->fetch('smowl/view/add.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
