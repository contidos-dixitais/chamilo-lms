<?php
/* For licensing terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Firebase\JWT\JWT;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_course_script(false);
api_block_anonymous_users(false);

$jwt = empty($_REQUEST['JWT']) ? '' : $_REQUEST['JWT'];

$em = Database::getManager();
$course = api_get_course_entity(api_get_course_int_id());

try {
    if (empty($jwt)) {
        throw new Exception('Token is missing');
    }

    $jwtParts = explode('.', $jwt, 3);
    $payloadStr = JWT::urlsafeB64Decode($jwtParts[1]);
    $payload = json_decode($payloadStr, true);

    if (empty($payload)) {
        throw new Exception('Token payload is empty');
    }

    if (empty($payload['https://purl.imsglobal.org/spec/lti-dl/claim/data'])) {
        throw new Exception('Data claim is missing');
    }

    if ($payload['aud'] !== SmowlPlugin::getIssuerUrl()) {
        throw new Exception('Audience not valid');
    }

    $toolId = str_replace('tool:', '', $payload['https://purl.imsglobal.org/spec/lti-dl/claim/data']);
    /** @var SmowlTool $smowlTool */
    $smowlTool = $em->find('ChamiloPluginBundle:Smowl\SmowlTool', $toolId);

    if (empty($smowlTool)) {
        throw new Exception('LTI tool not found');
    }

    if ($payload['iss'] !== $smowlTool->getClientId()) {
        throw new Exception('Consumer not valid');
    }

    $decodedJwt = JWT::decode($jwt, $smowlTool->publicKey, ['RS256']);

    if (empty($decodedJwt->{'https://purl.imsglobal.org/spec/lti-dl/claim/content_items'})) {
        throw new Exception('Content items are missing');
    }

    foreach ($decodedJwt->{'https://purl.imsglobal.org/spec/lti-dl/claim/content_items'} as $contentItemClaim) {
        /** @var SmowlContentItemType|null $contentItem */
        $contentItem = null;

        switch ($contentItemClaim->type) {
            case 'ltiResourceLink':
                $contentItem = new SmowlResourceLink($contentItemClaim);
            default:
                continue;
        }

        $contentItem->save($smowlTool, $course);
    }
} catch (Exception $exception) {
    $message = Display::return_message($exception->getMessage(), 'error');

    api_not_allowed(true, $message);
}

$plugin = SmowlPlugin::create();

Display::addFlash(
    Display::return_message($plugin->get_lang('ToolAdded'), 'success')
);
?>
<!DOCTYPE html>
<body>
<script>window.parent.location.href = '<?php echo api_get_course_url(); ?>';</script>
</body>
