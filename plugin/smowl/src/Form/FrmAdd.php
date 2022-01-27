<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\Smowl\Form;

use Category;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Display;
use FormValidator;
use Smowl;
use SmowlPlugin;
use SmowlAssignmentGradesService;
use SmowlNamesRoleProvisioningService;

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
        $this->toolIsV1p3 = $this->baseTool;
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

        $courseInfo = api_get_course_info();
        $sessionId = api_get_session_id();

        $exerciseList = $plugin->getCourseExercises($courseInfo, $sessionId);

        if(!empty($exerciseList)) {
            $this->addRadio(
                'tool_type',
                get_lang('RegistrationRoleWhatDoYouWantToDo'),
                [
                    'tool_type_internal' => 'Ejercicio del curso',
                    'tool_type_external' => 'Herramienta externa',
                ],
                []
            );

            $my_exercise_list = [];
            $titleSavedAsHtml = api_get_configuration_value('save_titles_as_html');
            if (is_array($exerciseList)) {
                foreach ($exerciseList as $row) {
                    $my_exercise_list[$row['iid']] = '';
            
                    $exerciseTitle = $row['title'];
                    if ($titleSavedAsHtml) {
                        $exerciseTitle = strip_tags(api_html_entity_decode(trim($exerciseTitle)));
                    }
                    $my_exercise_list[$row['iid']] .= $exerciseTitle;
                }
            }
            
            $this->addHtml('<div class="tool_type_internal" style="display: block;">');
            $this->addSelect(
                'exerciseId',
                get_lang('Exercise'),
                $my_exercise_list,
                []
            );
            $this->addHtml('</div>');

            $this->addHtml('<div class="tool_type_external" style="display: none;">');
            if (null === $this->baseTool) {
                $this->addUrl('launch_url', $plugin->get_lang('LaunchUrl'), true);
            }
            $this->addHtml('</div>');

            $defaults = [];
            $defaults['tool_type'] = 'tool_type_internal';

            $this->setDefaults($defaults);

        } else {
            $this->addHtml('<div class="tool_type_external" style="display: block;">');
            if (null === $this->baseTool) {
                $this->addUrl('launch_url', $plugin->get_lang('LaunchUrl'), true);
            }
            $this->addHtml('</div>');
        }

        $this->addButtonCreate($plugin->get_lang('AddExternalTool'));
        $this->applyFilter('__ALL__', 'trim');
    }

    public function setDefaultValues()
    {
        $defaults = [];

        if ($this->baseTool) {
            $defaults['name'] = $this->baseTool->getName();
            $defaults['description'] = $this->baseTool->getDescription();
        }

        $this->setDefaults($defaults);
    }

    public function returnForm(): string
    {
        $js = "<script>
                \$(function () {
                    \$('[name=\"tool_type\"]').on('change', function () {
                        $('.tool_type_internal, .tool_type_external').hide();

                        $('.' + this.value).show();
                    })
                });
            </script>";

        return $js.parent::returnForm();
    }
}
