<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;

require_once __DIR__.'/../../main/inc/global.inc.php';

$plugin = SmowlPlugin::create();

api_protect_admin_script();

$params = [
    'cidReq' => $_GET['cidReq'],
    'id_session' => isset($_GET['id_session']) ? intval($_GET['id_session']) : 0,
    'gidReq' => isset($_GET['gidReq']) ? intval($_GET['gidReq']) : 0,
    'gradebook' => isset($_GET['gradebook']) ? intval($_GET['gradebook']) : 0,
    'origin' => isset($_GET['origin']) ? $_GET['origin'] : 0,
];

$em = Database::getManager();

/** @var SmowlTool $tool */
$tool = isset($_GET['id']) ? $em->find('ChamiloPluginBundle:Smowl\SmowlTool', intval($_GET['id'])) : 0;

if (!$tool) {
    api_not_allowed(true);
}

$links = [];
$links[] = 'smowl/menu.php?id='.$tool->getId();

if (!$tool->getParent()) {
    /** @var SmowlTool $child */
    foreach ($tool->getChildren() as $child) {
        $links[] = "smowl/menu.php?id=".$child->getId();
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

if (empty($_GET['cidReq'])) {    
    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php');
} else {
    header('Location: '.api_get_path(WEB_PLUGIN_PATH).'smowl/configure.php?'.http_build_query($params));
}

exit;
