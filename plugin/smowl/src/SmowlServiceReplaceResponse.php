<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlReplaceServiceResponse.
 */
class SmowlServiceReplaceResponse extends SmowlServiceResponse
{
    /**
     * SmowlServiceReplaceResponse constructor.
     *
     * @param SmowlServiceResponseStatus $statusInfo
     * @param mixed|null                  $bodyParam
     */
    public function __construct(SmowlServiceResponseStatus $statusInfo, $bodyParam = null)
    {
        $statusInfo->setOperationRefIdentifier('replaceResult');

        parent::__construct($statusInfo, $bodyParam);
    }

    /**
     * @param SimpleXMLElement $xmlBody
     */
    protected function generateBody(SimpleXMLElement $xmlBody)
    {
        $xmlBody->addChild('replaceResultResponse');
    }
}
