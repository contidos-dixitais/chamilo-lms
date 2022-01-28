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
$template->assign('user_id', api_get_user_id());
$template->assign('lang', api_get_language_isocode());
$template->assign('course_link', urlencode(api_get_course_url($courseCode, api_get_session_id())));

$content = $template->fetch('smowl/view/register_user.tpl');

$template->assign('header', $pageTitle);
$template->assign('content', $content);
$template->display_one_col_template();
