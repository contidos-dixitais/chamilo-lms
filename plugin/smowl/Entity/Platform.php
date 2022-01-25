<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\Entity\Smowl;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Platform
 *
 * @package Chamilo\PluginBundle\Entity\Smowl
 *
 * @ORM\Table(name="plugin_smowl_platform")
 * @ORM\Entity()
 */
class Platform
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    protected $id;
    /**
     * @var string
     *
     * @ORM\Column(name="entity_name", type="string")
     */
    private $entityName;
    /**
     * @var string
     *
     * @ORM\Column(name="license_key", type="text")
     */
    private $licenseKey;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return Platform
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     *
     * Get entityName.
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set entityName.
     *
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * Get licenseKey.
     *
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * Set licenseKey.
     *
     * @param string $licenseKey
     *
     * @return Platform
     */
    public function setLicenseKey($licenseKey)
    {
        $this->licenseKey = $licenseKey;

        return $this;
    }
}
