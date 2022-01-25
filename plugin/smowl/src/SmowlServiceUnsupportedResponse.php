<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlServiceUnsupportedResponse.
 */
class SmowlServiceUnsupportedResponse extends SmowlServiceResponse
{
    /**
     * SmowlServiceUnsupportedResponse constructor.
     *
     * @param SmowlServiceResponseStatus $statusInfo
     * @param string                      $type
     */
    public function __construct(SmowlServiceResponseStatus $statusInfo, $type)
    {
        $statusInfo->setOperationRefIdentifier($type);

        parent::__construct($statusInfo);
    }

    /**
     * @param SimpleXMLElement $xmlBody
     */
    protected function generateBody(SimpleXMLElement $xmlBody)
    {
    }
}
