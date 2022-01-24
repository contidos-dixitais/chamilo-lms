<?php

if (!api_is_platform_admin()) {
    die('You must have admin permissions to install plugins');
}

SmowlPlugin::create()->install();
