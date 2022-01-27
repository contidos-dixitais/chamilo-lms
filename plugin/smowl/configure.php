<?php
/* For license terms, see /license.txt */

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_course_script();
api_protect_teacher_script();

$plugin = SmowlPlugin::create();
$em = Database::getManager();
$toolsRepo = $em->getRepository('ChamiloPluginBundle:Smowl\SmowlTool');

/** @var SmowlTool $baseTool */
$baseTool = isset($_REQUEST['type']) ? $toolsRepo->find(intval($_REQUEST['type'])) : null;
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'add';

/** @var Course $course */
$course = $em->find('ChamiloCoreBundle:Course', api_get_course_int_id());
$addedTools = $toolsRepo->findBy(['course' => $course]);
$globalTools = $toolsRepo->findBy(['parent' => null, 'course' => null]);

if ($baseTool && !$baseTool->isGlobal()) {
    Display::addFlash(
        Display::return_message($plugin->get_lang('ToolNotAvailable'), 'warning')
    );

    header('Location: '.api_get_self().'?'.api_get_cidreq());
    exit;
}

$categories = Category::load(null, null, $course->getCode());

switch ($action) {
    case 'add':
        $form = new \Chamilo\PluginBundle\Smowl\Form\FrmAdd('smowl_add_tool', [], $baseTool);
        $form->build();

        if ($baseTool) {
            $form->addHidden('type', $baseTool->getId());
        }

        if ($form->validate()) {
            $formValues = $form->getSubmitValues();

            $tool = new SmowlTool();

            if ($baseTool) {
                $tool = clone $baseTool;
                $tool->setParent($baseTool);
            }

            $tool
                ->setName($formValues['name'])
                ->setDescription(
                    empty($formValues['description']) ? null : $formValues['description']
                )
                ->setCourse($course);

            if (!$baseTool) {
                $tool
                ->setLaunchUrl($formValues['launch_url']);
            }

            $em->persist($tool);
            $em->flush();

            Display::addFlash(
                Display::return_message($plugin->get_lang('ToolAdded'), 'success')
            );

            header('Location: '.api_get_self().'?'.api_get_cidreq());
            exit;
        }

        $form->setDefaultValues();
        break;
    case 'edit':
        /** @var SmowlTool|null $tool */
        $tool = null;

        if (!empty($_REQUEST['id'])) {
            $tool = $em->find('ChamiloPluginBundle:Smowl\SmowlTool', (int) $_REQUEST['id']);
        }

        if (empty($tool) ||
            !SmowlPlugin::existsToolInCourse($tool->getId(), $course)
        ) {
            api_not_allowed(
                true,
                Display::return_message($plugin->get_lang('ToolNotAvailable'), 'error')
            );

            break;
        }

        $form = new \Chamilo\PluginBundle\Form\FrmEdit('smowl_edit_tool', [], $tool);
        $form->build(false);

        if ($form->validate()) {
            $formValues = $form->getSubmitValues();

            $tool
                ->setName($formValues['name'])
                ->setDescription(
                    empty($formValues['description']) ? null : $formValues['description']
                );

            $tool
            ->setLaunchUrl($formValues['launch_url']);

            $em->persist($tool);
            $em->flush();

            $courseTool = $plugin->findCourseToolByLink($course, $tool);

            if ($courseTool) {
                $plugin->updateCourseTool($courseTool, $tool);
            }

            Display::addFlash(
                Display::return_message($plugin->get_lang('ToolEdited'), 'success')
            );

            header('Location: '.api_get_self().'?'.api_get_cidreq());
            exit;
        }

        $form->setDefaultValues();
        break;
}

$template = new Template($plugin->get_lang('AddExternalTool'));
$template->assign('type', $baseTool ? $baseTool->getId() : null);
$template->assign('added_tools', $addedTools);
$template->assign('global_tools', $globalTools);
$template->assign('form', $form->returnForm());

$content = $template->fetch('smowl/view/add.tpl');

$actions = Display::url(
    Display::return_icon('add.png', $plugin->get_lang('AddExternalTool'), [], ICON_SIZE_MEDIUM),
    api_get_self().'?'.api_get_cidreq()
);

if (!empty($categories)) {
    $actions .= Display::url(
        Display::return_icon('gradebook.png', get_lang('MakeQualifiable'), [], ICON_SIZE_MEDIUM),
        './gradebook/add_eval.php?selectcat='.$categories[0]->get_id().'&'.api_get_cidreq()
    );
}

$template->assign('actions', Display::toolbarAction('lti_toolbar', [$actions]));
$template->assign('content', $content);
$template->display_one_col_template();
