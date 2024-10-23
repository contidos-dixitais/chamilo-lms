<?php

class ManagerUsersSigninPlugin extends Plugin
{
    protected function __construct()
    {
        parent::__construct(
            '0.1',
            'ContidosDixitais',
            [
                'tool_enable' => 'boolean',
            ]
        );
    }

    public static function create()
    {
        static $result = null;

        return $result ? $result : $result = new self();
    }

    public function install()
    {
        $this->install_course_fields_in_all_courses(false);
    }

    public function uninstall()
    {
        $this->uninstall_course_fields_in_all_courses();
    }
}
