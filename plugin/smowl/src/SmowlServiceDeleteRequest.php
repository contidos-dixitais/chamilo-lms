<?php
/* For licensing terms, see /license.txt */

use Chamilo\CoreBundle\Entity\GradebookEvaluation;
use Chamilo\UserBundle\Entity\User;

/**
 * Class SmowlDeleteServiceRequest.
 */
class SmowlServiceDeleteRequest extends SmowlServiceRequest
{
    /**
     * SmowlDeleteServiceRequest constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct(SimpleXMLElement $xml)
    {
        parent::__construct($xml);

        $this->responseType = SmowlServiceResponse::TYPE_DELETE;
        $this->xmlRequest = $this->xmlRequest->deleteResultRequest;
    }

    protected function processBody()
    {
        $resultRecord = $this->xmlRequest->resultRecord;
        $sourcedId = (string) $resultRecord->sourcedGUID->sourcedId;
        $sourcedId = htmlspecialchars_decode($sourcedId);

        $sourcedParts = json_decode($sourcedId, true);

        if (empty($sourcedParts)) {
            $this->statusInfo
                ->setSeverity(SmowlServiceResponseStatus::SEVERITY_ERROR)
                ->setCodeMajor(SmowlServiceResponseStatus::CODEMAJOR_FAILURE);

            return;
        }

        $em = Database::getManager();
        /** @var GradebookEvaluation $evaluation */
        $evaluation = $em->find('ChamiloCoreBundle:GradebookEvaluation', $sourcedParts['e']);
        /** @var User $user */
        $user = $em->find('ChamiloUserBundle:User', $sourcedParts['u']);

        if (empty($evaluation) || empty($user)) {
            $this->statusInfo
                ->setSeverity(SmowlServiceResponseStatus::SEVERITY_STATUS)
                ->setCodeMajor(SmowlServiceResponseStatus::CODEMAJOR_FAILURE);

            return;
        }

        $results = Result::load(null, $user->getId(), $evaluation->getId());

        if (empty($results)) {
            $this->statusInfo
                ->setSeverity(SmowlServiceResponseStatus::SEVERITY_STATUS)
                ->setCodeMajor(SmowlServiceResponseStatus::CODEMAJOR_FAILURE);

            return;
        }

        /** @var Result $result */
        $result = $results[0];
        $result->addResultLog($user->getId(), $evaluation->getId());
        $result->delete();

        $this->statusInfo
            ->setSeverity(SmowlServiceResponseStatus::SEVERITY_STATUS)
            ->setCodeMajor(SmowlServiceResponseStatus::CODEMAJOR_SUCCESS);
    }
}
