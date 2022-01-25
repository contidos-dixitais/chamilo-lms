<?php
/* For licensing terms, see /license.txt */

use Chamilo\PluginBundle\Entity\Smowl\SmowlTool;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SmowlAdvantageService.
 */
abstract class SmowlAdvantageService
{
    /**
     * @var SmowlTool
     */
    protected $tool;

    /**
     * SmowlAdvantageService constructor.
     *
     * @param SmowlTool $tool
     */
    public function __construct(SmowlTool $tool)
    {
        $this->tool = $tool;
    }

    /**
     * @param SmowlTool $tool
     *
     * @return SmowlAdvantageService
     */
    public function setTool(SmowlTool $tool)
    {
        $this->tool = $tool;

        return $this;
    }

    /**
     * @return array
     */
    abstract public function getAllowedScopes();

    /**
     * @param Request      $request
     * @param JsonResponse $response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return SmowlServiceResource
     */
    abstract public static function getResource(Request $request, JsonResponse $response);
}
