<?php

/* For license terms, see /license.txt */

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\CourseRelUser;
use Chamilo\CoreBundle\Entity\Session;
use Chamilo\CoreBundle\Entity\SessionRelCourseRelUser;
use Chamilo\CourseBundle\Entity\CTool;
use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Chamilo\UserBundle\Entity\User;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of Smowl
 *
 * @author Contidos Dixitais <noa@contidosdixitais.com>
 */
class SmowlPlugin extends Plugin
{
    const TABLE_TOOL = 'plugin_smowl_tool';
    const TABLE_PLATFORM = 'plugin_smowl_platform';

    public $isAdminPlugin = true;

    protected function __construct()
    {
        $version = '1.0';
        $author = 'Juan Cortizas Ponte';

        $settings = [
            'enabled' => 'boolean',
        ];

        parent::__construct($version, $author, $settings);

        $this->setCourseSettings();
    }

    /**
     * Get the class instance
     * @staticvar SmowlPlugin $result
     * @return SmowlPlugin
     */
    public static function create()
    {
        static $result = null;

        return $result ?: $result = new self();
    }

    /**
     * Get the plugin directory name
     */
    public function get_name()
    {
        return 'smowl';
    }

    /**
     * Install the plugin. Setup the database
     */
    public function install()
    {
        $pluginEntityPath = $this->getEntityPath();

        if (!is_dir($pluginEntityPath)) {
            if (!is_writable(dirname($pluginEntityPath))) {
                $message = get_lang('ErrorCreatingDir').': '.$pluginEntityPath;
                Display::addFlash(Display::return_message($message, 'error'));

                return false;
            }

            mkdir($pluginEntityPath, api_get_permissions_for_new_directories());
        }

        $fs = new Filesystem();
        $fs->mirror(__DIR__.'/Entity/', $pluginEntityPath, null, ['override']);

        $this->createPluginTables();
    }

    /**
     * Unistall plugin. Clear the database
     */
    public function uninstall()
    {
        $pluginEntityPath = $this->getEntityPath();
        $fs = new Filesystem();

        if ($fs->exists($pluginEntityPath)) {
            $fs->remove($pluginEntityPath);
        }

        try {
            $this->dropPluginTables();
            $this->removeTools();
        } catch (DBALException $e) {
            error_log('Error while uninstalling smowl plugin: '.$e->getMessage());
        }
    }

    /**
     * Creates the plugin tables on database
     *
     * @return boolean
     * @throws DBALException
     */
    private function createPluginTables()
    {
        $entityManager = Database::getManager();
        $connection = $entityManager->getConnection();

        if ($connection->getSchemaManager()->tablesExist(self::TABLE_TOOL)) {
            return true;
        }

        $queries = [
            "CREATE TABLE plugin_smowl_tool (
                    id INT AUTO_INCREMENT NOT NULL,
                    c_id INT DEFAULT NULL,
                    session_id INT DEFAULT NULL,
                    gradebook_eval_id INT DEFAULT NULL,
                    parent_id INT DEFAULT NULL,
                    name VARCHAR(255) NOT NULL,
                    description LONGTEXT DEFAULT NULL,
                    launch_url VARCHAR(255) NOT NULL,
                    entity_name VARCHAR(255) DEFAULT NULL,
                    license_key VARCHAR(255) DEFAULT NULL,
                    modality VARCHAR(255) DEFAULT NULL,
                    course_code VARCHAR(255) DEFAULT NULL,
                    login_url VARCHAR(255) DEFAULT NULL,
                    redirect_url VARCHAR(255) DEFAULT NULL,
                    launch_presentation LONGTEXT NOT NULL COMMENT '(DC2Type:json)',
                    INDEX IDX_C5E47F7C91D79BD3 (c_id),
                    INDEX IDX_C5E47F7C82F80D8B (gradebook_eval_id),
                    INDEX IDX_C5E47F7C727ACA70 (parent_id),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB",
            "CREATE TABLE plugin_smowl_platform (
                    id INT AUTO_INCREMENT NOT NULL,
                    kid VARCHAR(255) NOT NULL,
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB",
            "CREATE TABLE plugin_smowl_token (
                    id INT AUTO_INCREMENT NOT NULL,
                    tool_id INT DEFAULT NULL,
                    scope LONGTEXT NOT NULL COMMENT '(DC2Type:json)',
                    hash VARCHAR(255) NOT NULL,
                    created_at INT NOT NULL,
                    expires_at INT NOT NULL,
                    INDEX IDX_F7B5692F8F7B22CC (tool_id),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB",
            "ALTER TABLE plugin_smowl_tool
                ADD CONSTRAINT FK_C5E47F7C91D79BD3 FOREIGN KEY (c_id) REFERENCES course (id)",
            "ALTER TABLE plugin_smowl_tool
                ADD CONSTRAINT FK_C5E47F7C82F80D8B FOREIGN KEY (gradebook_eval_id)
                REFERENCES gradebook_evaluation (id) ON DELETE SET NULL",
            "ALTER TABLE plugin_smowl_tool
                ADD CONSTRAINT FK_C5E47F7C727ACA70 FOREIGN KEY (parent_id)
                REFERENCES plugin_smowl_tool (id) ON DELETE CASCADE",
            "ALTER TABLE plugin_smowl_token
                ADD CONSTRAINT FK_F7B5692F8F7B22CC FOREIGN KEY (tool_id)
                REFERENCES plugin_smowl_tool (id) ON DELETE CASCADE",
            "CREATE TABLE plugin_smowl_lineitem (
                    id INT AUTO_INCREMENT NOT NULL,
                    tool_id INT NOT NULL,
                    evaluation INT NOT NULL,
                    resource_id VARCHAR(255) DEFAULT NULL,
                    tag VARCHAR(255) DEFAULT NULL,
                    start_date DATETIME DEFAULT NULL,
                    end_date DATETIME DEFAULT NULL,
                    INDEX IDX_BA81BBF08F7B22CC (tool_id),
                    UNIQUE INDEX UNIQ_BA81BBF01323A575 (evaluation),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB",
            "ALTER TABLE plugin_smowl_lineitem ADD CONSTRAINT FK_BA81BBF08F7B22CC FOREIGN KEY (tool_id)
                REFERENCES plugin_smowl_tool (id) ON DELETE CASCADE",
            "ALTER TABLE plugin_smowl_lineitem ADD CONSTRAINT FK_BA81BBF01323A575 FOREIGN KEY (evaluation)
                REFERENCES gradebook_evaluation (id) ON DELETE CASCADE "
        ];

        foreach ($queries as $query) {
            Database::query($query);
        }

        return true;
    }

    /**
     * Drops the plugin tables on database
     *
     * @return boolean
     */
    private function dropPluginTables()
    {
        Database::query("DROP TABLE IF EXISTS plugin_smowl_lineitem");
        Database::query("DROP TABLE IF EXISTS plugin_smowl_token");
        Database::query("DROP TABLE IF EXISTS plugin_smowl_platform");
        Database::query("DROP TABLE IF EXISTS plugin_smowl_tool");

        return true;
    }

    private function removeTools()
    {
        $sql = "DELETE FROM c_tool WHERE link LIKE 'smowl/start.php%' AND category = 'plugin'";
        Database::query($sql);
    }

    /**
     * Set the course settings
     */
    private function setCourseSettings()
    {
        $button = Display::toolbarButton(
            $this->get_lang('ConfigureExternalTool'),
            api_get_path(WEB_PLUGIN_PATH).'smowl/configure.php?'.api_get_cidreq(),
            'cog',
            'primary'
        );

        // This setting won't be saved in the database.
        $this->course_settings = [
            [
                'name' => $this->get_lang('smowlDescription').$button.'<hr>',
                'type' => 'html',
            ],
        ];
    }

    /**
     * @param Course     $course
     * @param smowlTool $smowl
     *
     * @return CTool
     */
    public function findCourseToolByLink(Course $course, smowlTool $smowl)
    {
        $em = Database::getManager();
        $toolRepo = $em->getRepository('ChamiloCourseBundle:CTool');

        /** @var CTool $cTool */
        $cTool = $toolRepo->findOneBy(
            [
                'cId' => $course,
                'link' => self::generateToolLink($smowl),
            ]
        );

        return $cTool;
    }

    /**
     * @param CTool      $courseTool
     * @param smowlTool $smowl
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateCourseTool(CTool $courseTool, smowlTool $smowl)
    {
        $em = Database::getManager();

        $courseTool->setName($smowl->getName());

        if ('iframe' !== $smowl->getDocumentTarget()) {
            $courseTool->setTarget('_blank');
        } else {
            $courseTool->setTarget('_self');
        }

        $em->persist($courseTool);
        $em->flush();
    }

    /**
     * @param smowlTool $tool
     *
     * @return string
     */
    private static function generateToolLink(smowlTool $tool)
    {
        return 'smowl/start.php?id='.$tool->getId();
    }

    /**
     * Add the course tool.
     *
     * @param Course     $course
     * @param smowlTool $smowl
     * @param bool       $isVisible
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addCourseTool(Course $course, smowlTool $smowl, $isVisible = true)
    {
        $cTool = $this->createLinkToCourseTool(
            $smowl->getName(),
            $course->getId(),
            null,
            self::generateToolLink($smowl)
        );
        $cTool
            ->setTarget(
                $smowl->getDocumentTarget() === 'iframe' ? '_self' : '_blank'
            )
            ->setVisibility($isVisible);

        $em = Database::getManager();
        $em->persist($cTool);
        $em->flush();
    }

    /**
     * @return string
     */
    protected function getConfigExtraText()
    {
        $text = $this->get_lang('smowlDescription');
        $text .= sprintf(
            $this->get_lang('ManageToolButton'),
            api_get_path(WEB_PLUGIN_PATH).'smowl/admin.php'
        );

        return $text;
    }

    /**
     * @return string
     */
    public function getEntityPath()
    {
        return api_get_path(SYS_PATH).'src/Chamilo/PluginBundle/Entity/'.$this->getCamelCaseName();
    }

    public static function isInstructor()
    {
        api_is_allowed_to_edit(false, true);
    }


    /**
     * @param int $userId
     *
     * @return string
     */
    public static function generateToolUserId($userId)
    {
        $siteName = api_get_setting('siteName');
        $institution = api_get_setting('Institution');
        $toolUserId = "$siteName - $institution - $userId";
        $toolUserId = api_replace_dangerous_char($toolUserId);

        return $toolUserId;
    }

    /**
     * @param smowlTool $tool
     * @param User       $user
     *
     * @return string
     */
    public static function getLaunchUserIdClaim(smowlTool $tool, User $user)
    {
        if (null !== $tool->getParent()) {
            $tool = $tool->getParent();
        }

        $replacement = $tool->getReplacementForUserId();

        if (empty($replacement)) {
            return (string) $user->getId();
        }

        $replaced = str_replace(
            ['$User.id', '$User.username'],
            [$user->getId(), $user->getUsername()],
            $replacement
        );

        return $replaced;
    }

    /**
     * @param array      $contentItem
     * @param smowlTool $baseSmowlTool
     * @param Course     $course
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveItemAsSmowlLink(array $contentItem, smowlTool $baseSmowlTool, Course $course)
    {
        $em = Database::getManager();
        $smowlRepo = $em->getRepository('ChamiloPluginBundle:smowl\smowlTool');

        $url = empty($contentItem['url']) ? $baseSmowlTool->getLaunchUrl() : $contentItem['url'];

        /** @var smowlTool $newSmowlTool */
        $newSmowlTool = $smowlRepo->findOneBy(['launchUrl' => $url, 'parent' => $baseSmowlTool, 'course' => $course]);

        if (null === $newSmowlTool) {
            $newSmowlTool = new smowlTool();
            $newSmowlTool
                ->setLaunchUrl($url)
                ->setParent(
                    $baseSmowlTool
                )
                ->setCourse($course);
        }

        $newSmowlTool
            ->setName(
                !empty($contentItem['title']) ? $contentItem['title'] : $baseSmowlTool->getName()
            )
            ->setDescription(
                !empty($contentItem['text']) ? $contentItem['text'] : null
            );

        $em->persist($newSmowlTool);
        $em->flush();

        $courseTool = $this->findCourseToolByLink($course, $newSmowlTool);

        if ($courseTool) {
            $this->updateCourseTool($courseTool, $newSmowlTool);

            return;
        }

        $this->addCourseTool($course, $newSmowlTool);
    }

    /**
     * @return null|SimpleXMLElement
     */
    private function getRequestXmlElement()
    {
        $request = file_get_contents("php://input");

        if (empty($request)) {
            return null;
        }

        return new SimpleXMLElement($request);
    }

    /**
     * @param int    $toolId
     * @param Course $course
     *
     * @return bool
     */
    public static function existsToolInCourse($toolId, Course $course)
    {
        $em = Database::getManager();
        $toolRepo = $em->getRepository('ChamiloPluginBundle:smowl\smowlTool');

        /** @var smowlTool $tool */
        $tool = $toolRepo->findOneBy(['id' => $toolId, 'course' => $course]);

        return !empty($tool);
    }

    /**
     * @param array $params
     */
    public function trimParams(array &$params)
    {
        foreach ($params as $key => $value) {
            $newValue = preg_replace('/\s+/', ' ', $value);
            $params[$key] = trim($newValue);
        }
    }

    /**
     * @param smowlTool $tool
     * @param array      $params
     *
     * @return array
     */
    public function removeUrlParamsFromLaunchParams(smowlTool $tool, array &$params)
    {
        $urlQuery = parse_url($tool->getLaunchUrl(), PHP_URL_QUERY);

        if (empty($urlQuery)) {
            return $params;
        }

        $queryParams = [];
        parse_str($urlQuery, $queryParams);
        $queryKeys = array_keys($queryParams);

        foreach ($queryKeys as $key) {
            if (isset($params[$key])) {
                unset($params[$key]);
            }
        }
    }

    /**
     * Avoid conflict with foreign key when deleting a course
     *
     * @param int $courseId
     */
    public function doWhenDeletingCourse($courseId)
    {
        $em = Database::getManager();
        $q = $em
            ->createQuery(
                'DELETE FROM ChamiloPluginBundle:smowl\smowlTool tool
                    WHERE tool.course = :c_id and tool.parent IS NOT NULL'
            );
        $q->execute(['c_id' => (int) $courseId]);

        $em->createQuery('DELETE FROM ChamiloPluginBundle:smowl\smowlTool tool WHERE tool.course = :c_id')
            ->execute(['c_id' => (int) $courseId]);
    }

    /**
     * Generate a key pair and key id for the platform.
     *
     * Rerturn a associative array like ['kid' => '...', 'private' => '...', 'public' => '...'].
     *
     * @return array
     */
    private static function generatePlatformKeys()
    {
        // Create the private and public key
        $res = openssl_pkey_new(
            [
                'digest_alg' => 'sha256',
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );

        // Extract the private key from $res to $privateKey
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);

        // Extract the public key from $res to $publicKey
        $publicKey = openssl_pkey_get_details($res);

        return [
            'kid' => bin2hex(openssl_random_pseudo_bytes(10)),
            'private' => $privateKey,
            'public' => $publicKey["key"],
        ];
    }

    /**
     * @return string
     */
    public static function getIssuerUrl()
    {
        $webPath = api_get_path(WEB_PATH);

        return trim($webPath, " /");
    }

    public static function getCoursesForParentTool(smowlTool $tool)
    {
        $coursesId = [];
        if (!$tool->getParent()) {
            $coursesId = $tool->getChildren()->map(function (smowlTool $tool) {
                return $tool->getCourse();
            });
        }

        return $coursesId;
    }
}
