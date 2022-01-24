<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\Form;

use Category;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Display;
use Exception;
use FormValidator;
use Smowl;
use SmowlPlugin;

/**
 * Class FrmAdd.
 */
class FrmEdit extends FormValidator
{
    /**
     * @var SmowlTool|null
     */
    private $tool;

    /**
     * FrmAdd constructor.
     *
     * @param string          $name
     * @param array           $attributes
     * @param SmowlTool|null $tool
     */
    public function __construct(
        $name,
        $attributes = [],
        SmowlTool $tool = null
    ) {
        parent::__construct($name, 'POST', '', '', $attributes, self::LAYOUT_HORIZONTAL, true);

        $this->tool = $tool;
    }

    /**
     * Build the form.
     *
     * @param bool $globalMode
     */
    public function build($globalMode = true)
    {
        $plugin = SmowlPlugin::create();
        $course = $this->tool->getCourse();
        $parent = $this->tool->getParent();

        $this->addHeader($plugin->get_lang('ToolSettings'));

        if (null !== $course && $globalMode) {
            $this->addHtml(
                Display::return_message(
                    sprintf($plugin->get_lang('ToolAddedOnCourseX'), $course->getTitle()),
                    'normal',
                    false
                )
            );
        }

        $this->addText('name', get_lang('Name'));
        $this->addTextarea('description', get_lang('Description'));
        $this->freeze(['version']);

        if (null === $parent) {
            $this->addText('client_id', $plugin->get_lang('ClientId'), true);
            $this->freeze(['client_id']);
            $this->addTextarea(
                'public_key',
                $plugin->get_lang('PublicKey'),
                ['style' => 'font-family: monospace;', 'rows' => 5],
                true
            );
            $this->addUrl('login_url', $plugin->get_lang('LoginUrl'));
            $this->addUrl('redirect_url', $plugin->get_lang('RedirectUrl'));
        }

        $this->addSelect(
            'document_target',
            get_lang('LinkTarget'),
            ['iframe' => 'iframe', 'window' => 'window']
        );

        if (null === $parent
            || (null !== $parent && !$parent->isActiveDeepLinking())
        ) {
            $this->addCheckBox(
                'deep_linking',
                [null, $plugin->get_lang('SupportDeppLinkingHelp'), null],
                $plugin->get_lang('SupportDeepLinking')
            );
        }

        $gradebookUrl = api_get_path(WEB_CODE_PATH).'gradebook/index.php?'.api_get_cidreq();

        $this->addLabel(
            $plugin->get_lang('AssigmentAndGradesService'),
            sprintf(
                $plugin->get_lang('YouNeedCreateTheGradebokInCourseFirst'),
                Display::url($gradebookUrl, $gradebookUrl)
            )
        );

        if (!$parent) {
            $this->addText(
                'replacement_user_id',
                [
                    $plugin->get_lang('ReplacementUserId'),
                    $plugin->get_lang('ReplacementUserIdHelp'),
                ],
                false
            );
            $this->applyFilter('replacement_user_id', 'trim');
        }

        $this->addHtml('</div>');
        $this->addButtonUpdate($plugin->get_lang('EditExternalTool'));
        $this->addHidden('id', $this->tool->getId());
        $this->addHidden('action', 'edit');
        $this->applyFilter('__ALL__', 'trim');
    }

    /**
     * @throws Exception
     */
    public function setDefaultValues()
    {
        $advServices = $this->tool->getAdvantageServices();

        $this->setDefaults(
            [
                'name' => $this->tool->getName(),
                'description' => $this->tool->getDescription(),
                'launch_url' => $this->tool->getLaunchUrl(),
                'consumer_key' => $this->tool->getConsumerKey(),
                'shared_secret' => $this->tool->getSharedSecret(),
                'share_name' => $this->tool->isSharingName(),
                'share_email' => $this->tool->isSharingEmail(),
                'share_picture' => $this->tool->isSharingPicture(),
                'version' => $this->tool->getVersion(),
                'client_id' => $this->tool->getClientId(),
                'public_key' => $this->tool->publicKey,
                'login_url' => $this->tool->getLoginUrl(),
                'redirect_url' => $this->tool->getRedirectUrl(),
                'document_target' => $this->tool->getDocumentTarget(),
                'replacement_user_id' => $this->tool->getReplacementForUserId(),
            ]
        );
    }
}
