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

$params = [
    'id' => $tool->getId(),
    'cidReq' => $_GET['cidReq'],
    'id_session' => isset($_GET['id_session']) ? intval($_GET['id_session']) : 0,
    'gidReq' => isset($_GET['gidReq']) ? intval($_GET['gidReq']) : 0,
    'gradebook' => isset($_GET['gradebook']) ? intval($_GET['gradebook']) : 0,
    'origin' => isset($_GET['origin']) ? $_GET['origin'] : 0,
];

$interbreadcrumb[] = [
    'url' => api_get_path(WEB_PLUGIN_PATH).'smowl/menu.php?'.http_build_query($params),
    'name' => $tool->getName()
];

$interbreadcrumb[] = [
    'url' => api_get_path(WEB_PLUGIN_PATH).'smowl/user_report.php?'.http_build_query($params),
    'name' => 'Informe de regitro de usuarios'
];

$template = new Template($pageTitle);
$template->assign('tool', $tool);

$template->assign('launch_url', $launchUrl);
$template->assign('entity_name', $entityName);
$template->assign('license_key', $licenseKey);
$template->assign('modality', 'quiz');
$template->assign('course_code', $courseCode);
$template->assign('user_id', api_get_user_id());
$template->assign('lang', api_get_language_isocode());
$template->assign('users', $userList);
$template->assign('course_link', urlencode(api_get_course_url($courseCode, api_get_session_id())));

$content = $template->fetch('smowl/view/user_report.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
