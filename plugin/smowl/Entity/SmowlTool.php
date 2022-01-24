<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\Entity\Smowl;

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\Session;
use Chamilo\CoreBundle\Entity\GradebookEvaluation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use PHPExiftool\Driver\Tag\MXF\ViewportAspectRatio;

/**
 * Class SmowlTool
 *
 * @ORM\Table(name="plugin_smowl_tool")
 * @ORM\Entity()
 */
class SmowlTool
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name = '';
    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = null;
    /**
     * @var string
     *
     * @ORM\Column(name="launch_url", type="string")
     */
    private $launchUrl = '';
    /**
     * @var string
     *
     * @ORM\Column(name="entity_name", type="string", nullable=true)
     */
    private $entityName = '';
    /**
     * @var string
     *
     * @ORM\Column(name="license_key", type="string", nullable=true)
     */
    private $licenseKey = '';
    /**
     * @var string|null
     *
     * @ORM\Column(name="modality", type="string", nullable=true)
     */
    private $modality = null;
    /**
     * @var bool
     *
     * @ORM\Column(name="course_code", type="string", nullable=false, options={"default": false})
     */
    private $courseCode = false;
    /**
     * @var Course|null
     *
     * @ORM\ManyToOne(targetEntity="Chamilo\CoreBundle\Entity\Course")
     * @ORM\JoinColumn(name="c_id", referencedColumnName="id")
     */
    private $course = null;
    /**
     * @var Session|null
     *
     * @ORM\ManyToOne(targetEntity="Chamilo\CoreBundle\Entity\Course")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id")
     */
    private $session = null;    
    /**
     * @var GradebookEvaluation|null
     *
     * @ORM\ManyToOne(targetEntity="Chamilo\CoreBundle\Entity\GradebookEvaluation")
     * @ORM\JoinColumn(name="gradebook_eval_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $gradebookEval = null;
    /**
     * @var SmowlTool|null
     *
     * @ORM\ManyToOne(targetEntity="Chamilo\PluginBundle\Entity\Smowl\SmowlTool", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Chamilo\PluginBundle\Entity\Smowl\SmowlTool", mappedBy="parent")
     */
    private $children;
    /**
     * @var string|null
     *
     * @ORM\Column(name="login_url", type="string", nullable=true)
     */
    private $loginUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="redirect_url", type="string", nullable=true)
     */
    private $redirectUrl;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Chamilo\PluginBundle\Entity\Smowl\LineItem", mappedBy="tool")
     */
    private $lineItems;

    /**
     * @var array
     *
     * @ORM\Column(name="launch_presentation", type="json")
     */
    private $launchPresentation;

    /**
     * SmowlTool constructor.
     */
    public function __construct()
    {
        $this->description = null;
        $this->modality = null;
        $this->courseCode = false;
        $this->course = null;
        $this->session = null;
        $this->gradebookEval = null;
        $this->children = new ArrayCollection();
        $this->entityName = null;
        $this->licenseKey = null;
        $this->lineItems = new ArrayCollection();
        $this->launchPresentation = [
            'document_target' => 'iframe',
        ];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return SmowlTool
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return SmowlTool
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getLaunchUrl()
    {
        return $this->launchUrl;
    }

    /**
     * @param string $launchUrl
     *
     * @return SmowlTool
     */
    public function setLaunchUrl($launchUrl)
    {
        $this->launchUrl = $launchUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getModality()
    {
        return $this->modality;
    }

    /**
     * @param null|string $modality
     *
     * @return SmowlTool
     */
    public function setModality($modality)
    {
        $this->modality = $modality;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGlobal()
    {
        return $this->course === null;
    }

    /**
     * @param array $params
     *
     * @return null|string
     */
    public function encodemodality(array $params)
    {
        if (empty($params)) {
            return null;
        }

        $pairs = [];

        foreach ($params as $key => $value) {
            $pairs[] = "$key=$value";
        }

        return implode("\n", $pairs);
    }

    /**
     * @return array
     */
    public function getmodalityAsArray()
    {
        $params = [];
        $lines = explode("\n", $this->modality);
        $lines = array_filter($lines);

        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line, 2);

            $key = self::filterSpecialChars($key);
            $value = self::filterSpaces($value);

            $params[$key] = $value;
        }

        return $params;
    }

    /**
     * Map the key from custom param.
     *
     * @param string $key
     *
     * @return string
     */
    private static function filterSpecialChars($key)
    {
        $newKey = '';
        $key = strtolower($key);
        $split = str_split($key);

        foreach ($split as $char) {
            if (
                ($char >= 'a' && $char <= 'z') || ($char >= '0' && $char <= '9')
            ) {
                $newKey .= $char;

                continue;
            }

            $newKey .= '_';
        }

        return $newKey;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private static function filterSpaces($value)
    {
        $newValue = preg_replace('/\s+/', ' ', $value);

        return trim($newValue);
    }

    /**
     * @return array
     */
    public function parsemodality()
    {
        if (empty($this->modality)) {
            return [];
        }

        $params = [];
        $strings = explode("\n", $this->modality);

        foreach ($strings as $string) {
            if (empty($string)) {
                continue;
            }

            $pairs = explode('=', $string, 2);
            $key = self::filterSpecialChars($pairs[0]);
            $value = $pairs[1];

            $params['custom_'.$key] = $value;
        }

        return $params;
    }

    /**
     * Get courseCode.
     *
     * @return bool
     */
    public function getCourseCode()
    {
        return $this->courseCode;
    }

    /**
     * Set courseCode.
     *
     * @param bool $courseCode
     *
     * @return SmowlTool
     */
    public function setCourseCode($courseCode)
    {
        $this->courseCode = $courseCode;

        return $this;
    }

    /**
     * Get course.
     *
     * @return Course|null
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set course.
     *
     * @param Course|null $course
     *
     * @return SmowlTool
     */
    public function setCourse(Course $course = null)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get session.
     *
     * @return Session|null
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set course.
     *
     * @param Course|null $course
     *
     * @return SmowlTool
     */
    public function setSession(Session $session = null)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get gradebookEval.
     *
     * @return GradebookEvaluation|null
     */
    public function getGradebookEval()
    {
        return $this->gradebookEval;
    }

    /**
     * Set gradebookEval.
     *
     * @param GradebookEvaluation|null $gradebookEval
     *
     * @return SmowlTool
     */
    public function setGradebookEval($gradebookEval)
    {
        $this->gradebookEval = $gradebookEval;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSharingName()
    {
        $unserialize = $this->unserializePrivacy();

        return (bool) $unserialize['share_name'];
    }

    /**
     * @return bool
     */
    public function isSharingEmail()
    {
        $unserialize = $this->unserializePrivacy();

        return (bool) $unserialize['share_email'];
    }

    /**
     * @return bool
     */
    public function isSharingPicture()
    {
        $unserialize = $this->unserializePrivacy();

        return (bool) $unserialize['share_picture'];
    }

    /**
     * @return SmowlTool|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param SmowlTool $parent
     *
     * @return SmowlTool
     */
    public function setParent(SmowlTool $parent)
    {
        $this->parent = $parent;

        $this->licenseKey = $parent->getLicenseKey();
        $this->entityName = $parent->getEntityName();

        return $this;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @param string $licenseKey
     *
     * @return SmowlTool
     */
    public function setLicenseKey($licenseKey)
    {
        $this->licenseKey = $licenseKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     *
     * @return SmowlTool
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get loginUrl.
     *
     * @return null|string
     */
    public function getLoginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * Set loginUrl.
     *
     * @param string|null $loginUrl
     *
     * @return SmowlTool
     */
    public function setLoginUrl($loginUrl)
    {
        $this->loginUrl = $loginUrl;

        return $this;
    }

    /**
     * Get redirectUlr.
     *
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Set redirectUrl.
     *
     * @param string|null $redirectUrl
     *
     * @return SmowlTool
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Add LineItem to lineItems.
     *
     * @param LineItem $lineItem
     *
     * @return $this
     */
    public function addLineItem(LineItem $lineItem)
    {
        $lineItem->setTool($this);

        $this->lineItems[] = $lineItem;

        return $this;
    }

    /**
     * @param int    $resourceLinkId
     * @param int    $resourceId
     * @param string $tag
     * @param int    $limit
     * @param int    $page
     *
     * @return ArrayCollection
     */
    public function getLineItems($resourceLinkId = 0, $resourceId = 0, $tag = '', $limit = 0, $page = 1)
    {
        $criteria = Criteria::create();

        if ($resourceLinkId) {
            $criteria->andWhere(Criteria::expr()->eq('tool', $resourceId));
        }

        if ($resourceId) {
            $criteria->andWhere(Criteria::expr()->eq('tool', $resourceId));
        }

        if (!empty($tag)) {
            $criteria->andWhere(Criteria::expr()->eq('tag', $tag));
        }

        $limit = (int) $limit;
        $page = (int) $page;

        if ($limit > 0) {
            $criteria->setMaxResults($limit);

            if ($page > 0) {
                $criteria->setFirstResult($page * $limit);
            }
        }

        return $this->lineItems->matching($criteria);
    }

    /**
     * Set lineItems.
     *
     * @param ArrayCollection $lineItems
     *
     * @return $this
     */
    public function setLineItems(ArrayCollection $lineItems)
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param string $target
     *
     * @return $this
     */
    public function setDocumenTarget($target)
    {
        $this->launchPresentation['document_target'] = in_array($target, ['iframe', 'window']) ? $target : 'iframe';

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentTarget()
    {
        return $this->launchPresentation['document_target'] ?: 'iframe';
    }

    /**
     * @return array
     */
    public function getLaunchPresentation()
    {
        return $this->launchPresentation;
    }

    /**
     * @param string $replacement
     *
     * @return SmowlTool
     */
    public function setReplacementForUserId($replacement)
    {
        $this->replacementParams['user_id'] = $replacement;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReplacementForUserId()
    {
        if (!empty($this->replacementParams['user_id'])) {
            return $this->replacementParams['user_id'];
        }

        return null;
    }

    /**
     * @param array $coursesId
     *
     * @return ArrayCollection
     */
    public function getChildrenInCourses(array $coursesId)
    {
        return $this->children->filter(
            function (SmowlTool $child) use ($coursesId) {
                return in_array($child->getCourse()->getId(), $coursesId);
            }
        );
    }
}
