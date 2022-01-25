<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;

require_once __DIR__.'/../../main/inc/global.inc.php';

$plugin = SmowlPlugin::create();

api_protect_admin_script();

$em = Database::getManager();

/** @var SmowlTool $tool */
$tool = isset($_GET['id']) ? $em->find('ChamiloPluginBundle:Smowl\SmowlTool', intval($_GET['id'])) : 0;

if (!$tool) {
    api_not_allowed(true);
}

$links = [];
$links[] = 'smowl/start.php?id='.$tool->getId();

if (!$tool->getParent()) {
    /** @var SmowlTool $child */
    foreach ($tool->getChildren() as $child) {
        $links[] = "smowl/start.php?id=".$child->getId();
    }
}

$em->remove($tool);
$em->flush();

$em
    ->createQuery("DELETE FROM ChamiloCourseBundle:CTool ct WHERE ct.category = :category AND ct.link IN (:links)")
    ->execute(['category' => 'plugin', 'links' => $links]);

Display::addFlash(
    Display::return_message($plugin->get_lang('ToolDeleted'), 'success')
);

header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php');
exit;
