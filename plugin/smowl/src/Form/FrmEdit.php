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


        if (null === $parent) {
            $this->addUrl('launch_url', $plugin->get_lang('LaunchUrl'), true);
        }

        if (null === $parent) {
            $gradebookUrl = api_get_path(WEB_CODE_PATH).'gradebook/index.php?'.api_get_cidreq();

            $this->addLabel(
                $plugin->get_lang('AssigmentAndGradesService'),
                sprintf(
                    $plugin->get_lang('YouNeedCreateTheGradebokInCourseFirst'),
                    Display::url($gradebookUrl, $gradebookUrl)
                )
            );
        }

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
        $this->setDefaults(
            [
                'name' => $this->tool->getName(),
                'description' => $this->tool->getDescription(),
                'launch_url' => $this->tool->getLaunchUrl(),
            ]
        );
    }
}
