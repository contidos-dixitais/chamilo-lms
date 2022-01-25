<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlReadServiceResponse
 */
class SmowlServiceReadResponse extends SmowlServiceResponse
{
    /**
     * SmowlServiceReadResponse constructor.
     *
     * @param SmowlServiceResponseStatus $statusInfo
     * @param mixed|null                  $bodyParam
     */
    public function __construct(SmowlServiceResponseStatus $statusInfo, $bodyParam = null)
    {
        $statusInfo->setOperationRefIdentifier('readResult');

        parent::__construct($statusInfo, $bodyParam);
    }

    /**
     * @param SimpleXMLElement $xmlBody
     */
    protected function generateBody(SimpleXMLElement $xmlBody)
    {
        $resultResponse = $xmlBody->addChild('readResultResponse');

        $xmlResultScore = $resultResponse->addChild('result')
            ->addChild('resultScore');

        $xmlResultScore->addChild('language', 'en');
        $xmlResultScore->addChild('textString', $this->bodyParams);
    }
}
