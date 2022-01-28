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

$params = [
    'id' => $tool->getId(),
    'cidReq' => $_GET['cidReq'],
    'id_session' => isset($_GET['id_session']) ? intval($_GET['id_session']) : 0,
    'gidReq' => isset($_GET['gidReq']) ? intval($_GET['gidReq']) : 0,
    'gradebook' => isset($_GET['gradebook']) ? intval($_GET['gradebook']) : 0,
    'origin' => isset($_GET['origin']) ? $_GET['origin'] : 0,
];

$plugin = SmowlPlugin::create();

/** @var Platform $platform */
$platform = Database::getManager()
    ->getRepository('ChamiloPluginBundle:Smowl\Platform')
    ->findOneBy([]);
$entityName = $platform->getEntityName();

$userRegistered = $plugin->checkUserRegistration($entityName, api_get_user_id());

$startUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/start.php?'.http_build_query($params);
$registerUserUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/register_user.php?'.http_build_query($params);
$userReportUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/user_report.php?'.http_build_query($params);
$activityReportUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/activity_report.php?'.http_build_query($params);

$pageTitle = Security::remove_XSS($tool->getName());

$template = new Template($pageTitle);
$template->assign('tool', $tool);

$template->assign('user_registered', $userRegistered);
$template->assign('exam_url', $startUrl);
$template->assign('register_user_url', $registerUserUrl);
$template->assign('user_reports_url', $userReportUrl);
$template->assign('activity_reports_url', $activityReportUrl);

$content = $template->fetch('smowl/view/menu.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
