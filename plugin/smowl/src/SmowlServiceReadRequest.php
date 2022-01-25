<?php
/* For licensing terms, see /license.txt */

use Chamilo\CoreBundle\Entity\GradebookEvaluation;
use Chamilo\UserBundle\Entity\User;

/**
 * Class SmowlServiceReadRequest.
 */
class SmowlServiceReadRequest extends SmowlServiceRequest
{
    /**
     * SmowlServiceReadRequest constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct(SimpleXMLElement $xml)
    {
        parent::__construct($xml);

        $this->responseType = SmowlServiceResponse::TYPE_READ;
        $this->xmlRequest = $this->xmlRequest->readResultRequest;
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

        $ltiScore = '';
        $responseDescription = get_plugin_lang('ScoreNotSet', 'SmowlPlugin');

        if (!empty($results)) {
            /** @var Result $result */
            $result = $results[0];
            $ltiScore = 0;

            if (!empty($result->get_score())) {
                $ltiScore = $result->get_score() / $evaluation->getMax();
            }

            $responseDescription = sprintf(
                get_plugin_lang('ScoreForXUserIsYScore', 'SmowlPlugin'),
                $user->getId(),
                $ltiScore
            );
        }

        $this->statusInfo
            ->setSeverity(SmowlServiceResponseStatus::SEVERITY_STATUS)
            ->setCodeMajor(SmowlServiceResponseStatus::CODEMAJOR_SUCCESS)
            ->setDescription($responseDescription);

        $this->responseBodyParam = (string) $ltiScore;
    }
}
