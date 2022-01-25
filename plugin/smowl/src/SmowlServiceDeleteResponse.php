<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlServiceDeleteResponse.
 */
class SmowlServiceDeleteResponse extends SmowlServiceResponse
{
    /**
     * SmowlServiceDeleteResponse constructor.
     *
     * @param SmowlServiceResponseStatus $statusInfo
     * @param mixed|null                  $bodyParam
     */
    public function __construct(SmowlServiceResponseStatus $statusInfo, $bodyParam = null)
    {
        $statusInfo->setOperationRefIdentifier('deleteResult');

        parent::__construct($statusInfo, $bodyParam);
    }

    /**
     * @param SimpleXMLElement $xmlBody
     */
    protected function generateBody(SimpleXMLElement $xmlBody)
    {
        $xmlBody->addChild('deleteResultResponse');
    }
}
