<?php

use Chamilo\CoreBundle\Entity\Session;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_course_script();

$em = Database::getManager();

$tool = isset($_GET['id']) ? $em->find('ChamiloPluginBundle:Smowl\SmowlTool', $_GET['id']) : null;
$user = api_get_user_entity(api_get_user_id());

if (!$tool) {
    api_not_allowed(true);
}

$smowlPlugin = SmowlPlugin::create();

$pageTitle = Security::remove_XSS($tool->getName());

$launchUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/login.php?id='.$tool->getId();

if ($tool->getDocumentTarget() == 'window') {
    header("Location: $launchUrl");
    exit;
}

$template = new Template($pageTitle);
$template->assign('tool', $tool);

$template->assign('entity_name', $entityName);
$template->assign('license_key', $licenseKey);
$template->assign('modality', $modality);
$template->assign('course_code', $courseCode);
$template->assign('session_id', $sessionId);
$template->assign('user_id', api_get_user_id());
$template->assign('lang', api_get_language_isocode());
$template->assign('course_link', "");
$template->assign('launchUrl', $launchUrl);

$content = $template->fetch('ims_lti/view/start.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
