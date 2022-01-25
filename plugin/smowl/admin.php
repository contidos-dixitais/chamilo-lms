<?php
/* For license terms, see /license.txt */

use Doctrine\Common\Collections\Criteria;

$cidReset = true;

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_admin_script();

$plugin = SmowlPlugin::create();

if ($plugin->get('enabled') !== 'true') {
    api_not_allowed(true);
}

$em = Database::getManager();

$criteria = Criteria::create()
    ->where(
        Criteria::expr()->isNull('parent')
    );

$tools = $em->getRepository('ChamiloPluginBundle:Smowl\SmowlTool')->matching($criteria);

$categoriesGradeBook = [];
foreach($tools as $tool) {
    foreach($tool->getChildren() as $childTool) {
        $categories = [];
        if($childTool->getSession() != null) {
            $categories = Category::load(null, null, $childTool->getCourse()->getCode(), null, null, $childTool->getSession()->getId());
        }
        else {
            $categories = Category::load(null, null, $childTool->getCourse()->getCode());
        }
        if ($categories != null){
            array_push($categoriesGradeBook, $categories[0]);
        }
    }
}

$interbreadcrumb[] = ['url' => api_get_path(WEB_CODE_PATH).'admin/index.php', 'name' => get_lang('PlatformAdmin')];

$htmlHeadXtra[] = api_get_css(
    api_get_path(WEB_PLUGIN_PATH).'smowl/assets/style.css'
);

$template = new Template($plugin->get_title());
$template->assign('tools', $tools);
$template->assign('categories', $categoriesGradeBook);

$content = $template->fetch('smowl/view/admin.tpl');

$template->assign('header', $plugin->get_title());
$template->assign('content', $content);
$template->display_one_col_template();
