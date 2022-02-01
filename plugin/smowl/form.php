<?php
/* For license terms, see /license.txt */

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\Session;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Chamilo\UserBundle\Entity\User;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_course_script(false);
api_block_anonymous_users(false);

$em = Database::getManager();

/** @var SmowlTool $tool */
$tool = isset($_GET['id'])
    ? $em->find('ChamiloPluginBundle:Smowl\SmowlTool', (int) $_GET['id'])
    : null;

if (!$tool) {
    api_not_allowed(true);
}

/** @var SmowlPlugin $smowlPlugin */
$smowlPlugin = SmowlPlugin::create();
/** @var Session $session */
$session = $em->find('ChamiloCoreBundle:Session', api_get_session_id());
/** @var Course $course */
$course = $em->find('ChamiloCoreBundle:Course', api_get_course_int_id());
/** @var User $user */
$user = $em->find('ChamiloUserBundle:User', api_get_user_id());

$pluginPath = api_get_path(WEB_PLUGIN_PATH).'smowl/';
$toolUserId = SmowlPlugin::getLaunchUserIdClaim($tool, $user);
$platformDomain = str_replace(['https://', 'http://'], '', api_get_setting('InstitutionUrl'));

$params = [];
$params['title'] = $tool->getName();
$params['text'] = $tool->getDescription();
$params['data'] = 'tool:'.$tool->getId();

$params['user_id'] = $toolUserId;

$params['context_id'] = $course->getId();
$params['context_type'] = 'CourseSection';
$params['context_label'] = $course->getCode();
$params['context_title'] = $course->getTitle();
$params['launch_presentation_locale'] = api_get_language_isocode();
$params['tool_consumer_info_product_family_code'] = 'Chamilo LMS';
$params['tool_consumer_info_version'] = api_get_version();
$params['tool_consumer_instance_guid'] = $platformDomain;
$params['tool_consumer_instance_name'] = api_get_setting('siteName');
$params['tool_consumer_instance_url'] = api_get_path(WEB_PATH);
$params['tool_consumer_instance_contact_email'] = api_get_setting('emailAdministrator');
$params['oauth_callback'] = 'about:blank';

$smowlPlugin->trimParams($params);

$smowlPlugin->removeUrlParamsFromLaunchParams($tool, $params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>title</title>
</head>
<body>
<form action="<?php echo $tool->getLaunchUrl() ?>" name="smowlLaunchForm" method="post"
      encType="application/x-www-form-urlencoded">
    <?php foreach ($params as $key => $value) { ?>
        <input type="hidden" name="<?php echo $key ?>" value="<?php echo htmlspecialchars($value) ?>">
    <?php } ?>
</form>
<script>document.smowlLaunchForm.submit();</script>
</body>
</html>
