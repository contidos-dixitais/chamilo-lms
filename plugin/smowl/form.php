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

$params['resource_link_id'] = $tool->getId();
$params['resource_link_title'] = $tool->getName();
$params['resource_link_description'] = $tool->getDescription();

$toolEval = $tool->getGradebookEval();

if (!empty($toolEval)) {
    $params['lis_result_sourcedid'] = json_encode(
        ['e' => $toolEval->getId(), 'u' => $user->getId(), 'l' => uniqid(), 'lt' => time()]
    );
    $params['lis_outcome_service_url'] = api_get_path(WEB_PATH).'smowl/os';
    $params['lis_person_sourcedid'] = "$platformDomain:$toolUserId";
    $params['lis_course_section_sourcedid'] = Smowl::getCourseSectionSourcedId($platformDomain, $course, $session);
}

$params['user_id'] = $toolUserId;

if ($tool->isSharingPicture()) {
    $params['user_image'] = UserManager::getUserPicture($user->getId());
}

if ($tool->isSharingName()) {
    $params['lis_person_name_given'] = $user->getFirstname();
    $params['lis_person_name_family'] = $user->getLastname();
    $params['lis_person_name_full'] = $user->getFirstname().' '.$user->getLastname();
}

if ($tool->isSharingEmail()) {
    $params['lis_person_contact_email_primary'] = $user->getEmail();
}

if (DRH === $user->getStatus()) {
    if (!empty($scopeMentor)) {
        $params['role_scope_mentor'] = $scopeMentor;
    }
}

$params['context_id'] = $course->getId();
$params['context_type'] = 'CourseSection';
$params['context_label'] = $course->getCode();
$params['context_title'] = $course->getTitle();
$params['launch_presentation_locale'] = api_get_language_isocode();
$params['launch_presentation_document_target'] = $tool->getDocumentTarget();
$params['tool_consumer_info_product_family_code'] = 'Chamilo LMS';
$params['tool_consumer_info_version'] = api_get_version();
$params['tool_consumer_instance_guid'] = $platformDomain;
$params['tool_consumer_instance_name'] = api_get_setting('siteName');
$params['tool_consumer_instance_url'] = api_get_path(WEB_PATH);
$params['tool_consumer_instance_contact_email'] = api_get_setting('emailAdministrator');
$params['oauth_callback'] = 'about:blank';

$smowlPlugin->trimParams($customParams);

$params += Smowl::substituteVariablesInCustomParams(
    $params,
    $customParams,
    $user,
    $course,
    $session,
    $platformDomain,
    $tool
);

$smowlPlugin->trimParams($params);

if (!empty($tool->getLicenseKey()) && !empty($tool->getEntityName())) {
    $consumer = new OAuthConsumer(
        $tool->getLicenseKey(),
        $tool->getEntityName(),
        null
    );
    $hmacMethod = new OAuthSignatureMethod_HMAC_SHA1();

    $request = OAuthRequest::from_consumer_and_token(
        $consumer,
        '',
        'POST',
        $tool->getLaunchUrl(),
        $params
    );
    $request->sign_request($hmacMethod, $consumer, '');

    $params = $request->get_parameters();
}

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
