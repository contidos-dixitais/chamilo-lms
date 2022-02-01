<?php
/* For license terms, see /license.txt */

/**
 * Uninstall the Smowl Plugin.
 *
 * @package chamilo.plugin.smowl
 */

if (!api_is_platform_admin()) {
    die('You must have admin permissions to uninstall plugins');
}

SmowlPlugin::create()->uninstall();
