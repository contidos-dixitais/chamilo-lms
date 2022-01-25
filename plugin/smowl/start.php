<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_course_script();

$em = Database::getManager();

/** @var SmowlTool $tool */
$tool = isset($_GET['id']) ? $em->find('ChamiloPluginBundle:Smowl\SmowlTool', intval($_GET['id'])) : null;
$user = api_get_user_entity(
    api_get_user_id()
);

if (!$tool) {
    api_not_allowed(true);
}

$imsSmowlPlugin = SmowlPlugin::create();

$pageTitle = Security::remove_XSS($tool->getName());


$is1p3 = !empty($tool->publicKey) && !empty($tool->getClientId()) &&
    !empty($tool->getLoginUrl()) && !empty($tool->getRedirectUrl());

if ($is1p3) {
    $launchUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/login.php?id='.$tool->getId();
} else {
    $launchUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/form.php?'.http_build_query(['id' => $tool->getId()]);
}

if ($tool->getDocumentTarget() == 'window') {
    header("Location: $launchUrl");
    exit;
}

$template = new Template($pageTitle);
$template->assign('tool', $tool);

$template->assign('launch_url', $launchUrl);

$content = $template->fetch('smowl/view/start.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
