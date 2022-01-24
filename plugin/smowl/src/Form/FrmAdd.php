<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\Smowl\Form;

use Category;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Display;
use FormValidator;
use ImsLti;
use SmowlPlugin;
use LtiAssignmentGradesService;
use LtiNamesRoleProvisioningService;

/**
 * Class FrmAdd.
 */
class FrmAdd extends FormValidator
{
    /**
     * @var SmowlTool|null
     */
    private $baseTool;
    /**
     * @var bool
     */
    private $toolIsV1p3;

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

        $this->baseTool = $tool;
        $this->toolIsV1p3 = $this->baseTool
            && !empty($this->baseTool->publicKey)
            && !empty($this->baseTool->getLoginUrl())
            && !empty($this->baseTool->getRedirectUrl());
    }

    /**
     * Build the form
     */
    public function build()
    {
        $plugin = SmowlPlugin::create();

        $this->addHeader($plugin->get_lang('ToolSettings'));
        $this->addText('name', get_lang('Name'));
        $this->addTextarea('description', get_lang('Description'));

        if (null === $this->baseTool) {
            $this->addUrl('launch_url', $plugin->get_lang('LaunchUrl'), true);
            $this->addHtml('<div>');
            $this->addText('launch_url', $plugin->get_lang('LaunchUrl'), false);
            $this->addText('entity_name', $plugin->get_lang('EntityName'), false);
            $this->addText('license_key', $plugin->get_lang('LicenseKey'), false);
            $this->addText('modality', $plugin->get_lang('Modality'), false);
            $this->addUrl('login_url', $plugin->get_lang('LoginUrl'), false);
            $this->addUrl('redirect_url', $plugin->get_lang('RedirectUrl'), false);            
            $this->addHtml('</div>');
        }

        $this->addButtonAdvancedSettings('lti_adv');
        $this->addHtml('<div id="lti_adv_options" style="display:none;">');
        $this->addSelect(
            'document_target',
            get_lang('LinkTarget'),
            ['iframe' => 'iframe', 'window' => 'window']
        );

        if (null === $this->baseTool
            || ($this->baseTool)
        ) {
            $this->addCheckBox(
                'deep_linking',
                [null, $plugin->get_lang('SupportDeppLinkingHelp'), null],
                $plugin->get_lang('SupportDeepLinking')
            );
        }

        $showAGS = false;

        if (api_get_course_int_id()) {
            $caterories = Category::load(null, null, api_get_course_id());

            if (!empty($caterories)) {
                $showAGS = true;
            }
        } else {
            $showAGS = true;
        }

        $this->addHtml('<div class="'.ImsLti::V_1P3.'" style="display: none;">');

        if (!$showAGS) {
            $gradebookUrl = api_get_path(WEB_CODE_PATH).'gradebook/index.php?'.api_get_cidreq();

            $this->addLabel(
                $plugin->get_lang('AssigmentAndGradesService'),
                sprintf(
                    $plugin->get_lang('YouNeedCreateTheGradebokInCourseFirst'),
                    Display::url($gradebookUrl, $gradebookUrl)
                )
            );
        }

        $this->addHtml('</div>');

        if (!$this->baseTool) {
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

        $this->addButtonCreate($plugin->get_lang('AddExternalTool'));
        $this->applyFilter('__ALL__', 'trim');
    }

    public function setDefaultValues()
    {
        $defaults = [];
        $defaults['version'] = ImsLti::V_1P1;

        if ($this->baseTool) {
            $defaults['name'] = $this->baseTool->getName();
            $defaults['description'] = $this->baseTool->getDescription();
            $defaults['document_target'] = $this->baseTool->getDocumentTarget();
            $defaults['share_name'] = $this->baseTool->isSharingName();
            $defaults['share_email'] = $this->baseTool->isSharingEmail();
            $defaults['share_picture'] = $this->baseTool->isSharingPicture();
            $defaults['public_key'] = $this->baseTool->publicKey;
            $defaults['login_url'] = $this->baseTool->getLoginUrl();
            $defaults['redirect_url'] = $this->baseTool->getRedirectUrl();
        }

        $this->setDefaults($defaults);
    }

    public function returnForm(): string
    {
        $js = "<script>
                \$(function () {
                    \$('[name=\"version\"]').on('change', function () {
                        $('.".ImsLti::V_1P1.", .".ImsLti::V_1P3."').hide();

                        $('.' + this.value).show();
                    })
                });
            </script>";

        return $js.parent::returnForm(); // TODO: Change the autogenerated stub
    }
}
