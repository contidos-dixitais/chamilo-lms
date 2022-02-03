<?php
/* For license terms, see /license.txt */

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_course_script();
api_protect_teacher_script();

$courseInfo = api_get_course_info();
$sessionId = api_get_session_id();

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

$exerciseList = $plugin->getCourseExercises($courseInfo, $sessionId);

$my_exercise_list = [];
$my_exercise_list['0'] = get_lang('AllExercises');
$my_exercise_list['-1'] = get_lang('OrphanQuestions');
$titleSavedAsHtml = api_get_configuration_value('save_titles_as_html');
if (is_array($exercise_list)) {
    foreach ($exercise_list as $row) {
        $my_exercise_list[$row['iid']] = '';
        if ($row['iid'] == $fromExercise && $selected_course == api_get_course_int_id()) {
            $my_exercise_list[$row['iid']] = ">&nbsp;&nbsp;&nbsp;&nbsp;";
        }

        $exerciseTitle = $row['title'];
        if ($titleSavedAsHtml) {
            $exerciseTitle = strip_tags(api_html_entity_decode(trim($exerciseTitle)));
        }
        $my_exercise_list[$row['iid']] .= $exerciseTitle;
    }
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

            $launchUrl = $formValues['launch_url'];

            if(empty($formValues['launch_url'])) {
                $exerciseId = $formValues['exerciseId'];

                $exercise = new Exercise($courseInfo['real_id']);
                $exercise->read($exerciseItem['iid']);

                $launchUrl = api_get_path(WEB_CODE_PATH).
                'exercise/overview.php?exerciseId='.$exerciseId.'&'.api_get_cidreq().'&id_session=0';
            }

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
                ->setLaunchUrl($launchUrl);
            }

            $em->persist($tool);
            $em->flush();

            $plugin->addCourseTool($course, $tool);

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

        $form = new \Chamilo\PluginBundle\Smowl\Form\FrmEdit('smowl_edit_tool', [], $tool);
        $form->build(false);

        if ($form->validate()) {
            $formValues = $form->getSubmitValues();

            $tool
                ->setName($formValues['name'])
                ->setDescription(
                    empty($formValues['description']) ? null : $formValues['description']
                );


            if (null === $tool->getParent()) {
                $tool
                ->setLaunchUrl($formValues['launch_url']);
            }

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


$template->assign('content', $content);
$template->display_one_col_template();
