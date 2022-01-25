<?php

/* For licensing terms, see /license.txt */

/* To show the plugin course icons you need to add these icons:
 * main/img/icons/22/plugin_name.png
 * main/img/icons/64/plugin_name.png
 * main/img/icons/64/plugin_name_na.png
*/

/**
 * Proctorated exam plugin with Smowl
 */
class SMOWLPluginOLD extends Plugin
{
    const MODALITY_COURSE = 'course';
    const MODALITY_QUIZ_ = 'quiz';
    const MODALITY_TEST = 'test';
    const MODALITY_SURVEY = 'survey';
    const MODALITY_OTHER = 'other';

    /**
     * SMOWLPluginOLD constructor.
     */
    protected function __construct()
    {
        parent::__construct(
            '1.0',
            'Contidos Dixitais',
            [
                'tool_enable' => 'boolean',
                'entity_name' => 'string',
                'license_key' => 'string',
            ]
        );

        $this->isAdminPlugin = true;
    }

    /**
     * @return SMOWLPluginOLD|null
     */
    public static function create()
    {
        static $result = null;

        return $result ? $result : $result = new self();
    }

    public function install()
    {
        $sql = "CREATE TABLE IF NOT EXISTS plugin_smowl_exam (
                id INT unsigned NOT NULL auto_increment PRIMARY KEY,
                c_id INT unsigned NOT NULL DEFAULT 0,
                session_id INT unsigned NOT NULL DEFAULT 0,
                language VARCHAR(255) DEFAULT NULL,
                modality VARCHAR(255) DEFAULT NULL,
                register_url VARCHAR(255) DEFAULT NULL,
                access_url INT NOT NULL DEFAULT 1
                )";
        Database::query($sql);

        // Copy icons into the main/img/icons folder
        $iconName = 'smowl';
        $iconsList = [
            '64/'.$iconName.'.png',
            '64/'.$iconName.'_na.png',
            '32/'.$iconName.'.png',
            '32/'.$iconName.'_na.png',
            '22/'.$iconName.'.png',
            '22/'.$iconName.'_na.png',
        ];
        $sourceDir = api_get_path(SYS_PLUGIN_PATH).'smowl/resources/img/';
        $destinationDir = api_get_path(SYS_CODE_PATH).'img/icons/';
        foreach ($iconsList as $icon) {
            $src = $sourceDir.$icon;
            $dest = $destinationDir.$icon;
            copy($src, $dest);
        }
    }

    public function uninstall()
    {
        $t_settings = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
        $t_options = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);

        $variables = [
            'tool_enable',
            'entity_name',
            'license_key'
        ];

        $urlId = api_get_current_access_url_id();

        foreach ($variables as $variable) {
            $sql = "DELETE FROM $t_settings WHERE variable = '$variable' AND access_url = $urlId";
            Database::query($sql);
        }

        $em = Database::getManager();
        $sm = $em->getConnection()->getSchemaManager();
        if ($sm->tablesExist('plugin_smowl_exam')) {
            Database::query("DELETE FROM plugin_smowl_exam WHERE access_url = $urlId");
        }

        if (1 == $urlId) {
            $sql = "DELETE FROM $t_options WHERE variable = 'smowl_plugin'";
            Database::query($sql);

            if ($sm->tablesExist('plugin_smowl_exam')) {
                Database::query('DROP TABLE IF EXISTS plugin_smowl_exam');
            }
        }

        // Remove icons from the main/img/icons folder
        $iconName = 'smowl';
        $iconsList = [
            '64/'.$iconName.'.png',
            '64/'.$iconName.'_na.png',
            '32/'.$iconName.'.png',
            '32/'.$iconName.'_na.png',
            '22/'.$iconName.'.png',
            '22/'.$iconName.'_na.png',
        ];
        $destinationDir = api_get_path(SYS_CODE_PATH).'img/icons/';
        foreach ($iconsList as $icon) {
            $dest = $destinationDir.$icon;
            if (is_file($dest)) {
                @unlink($dest);
            }
        }
    }
}