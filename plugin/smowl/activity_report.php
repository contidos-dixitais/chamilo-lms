<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Chamilo\PluginBundle\Entity\Smowl\Platform;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_course_script();

$em = Database::getManager();

/** @var SmowlTool $tool */
$tool = isset($_GET['id']) ? $em->find('ChamiloPluginBundle:Smowl\SmowlTool', intval($_GET['id'])) : null;

/** @var Platform $platform */
$platform = Database::getManager()
    ->getRepository('ChamiloPluginBundle:Smowl\Platform')
    ->findOneBy([]);
$entityName = $platform->getEntityName();
$licenseKey = $platform->getLicenseKey();
$courseCode = $tool->getCourse()->getCode();
$courseId = $tool->getCourse()->getId();

$user = api_get_user_entity(
    api_get_user_id()
);

if (!$tool) {
    api_not_allowed(true);
}

if (!empty($tool->getSession())){
    $users = SessionManager::get_users_by_session(session_id());
} else {
    $users = CourseManager::get_user_list_from_course_code($courseCode);
}

$userList = "";
foreach ($users as $user) {
    $userList .= $user['user_id'].'.'.$user['firstname'].'smiley'.$user['lastname'].',';
}
$userList = substr($userList, 0, -1);

$userList = str_replace('@', '', $userList);

$smowlPlugin = SmowlPlugin::create();

$pageTitle = Security::remove_XSS($tool->getName());

$launchUrl = api_get_path(WEB_PLUGIN_PATH).'smowl/form.php?'.http_build_query(['id' => $tool->getId()]);

$template = new Template($pageTitle);
$template->assign('tool', $tool);

$template->assign('launch_url', $launchUrl);
$template->assign('entity_name', $entityName);
$template->assign('license_key', $licenseKey);
$template->assign('modality', 'quiz');
$template->assign('course_code', $courseCode);
$template->assign('course_id', $courseId);
$template->assign('user_id', api_get_user_id());
$template->assign('lang', api_get_language_isocode());
$template->assign('users', $userList);
$template->assign('course_link', urlencode(api_get_course_url($courseCode, api_get_session_id())));

$content = $template->fetch('smowl/view/activity_report.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
