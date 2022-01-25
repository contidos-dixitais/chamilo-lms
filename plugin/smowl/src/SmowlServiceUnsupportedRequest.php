<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlServiceUnsupportedRequest.
 */
class SmowlServiceUnsupportedRequest extends SmowlServiceRequest
{
    /**
     * SmowlDeleteServiceRequest constructor.
     *
     * @param SimpleXMLElement $xml
     * @param string           $name
     */
    public function __construct(SimpleXMLElement $xml, $name)
    {
        parent::__construct($xml);

        $this->responseType = $name;
    }

    protected function processBody()
    {
        $this->statusInfo
            ->setSeverity(SmowlServiceResponseStatus::SEVERITY_STATUS)
            ->setCodeMajor(SmowlServiceResponseStatus::CODEMAJOR_UNSUPPORTED)
            ->setDescription(
                $this->responseType.' is not supported'
            );
    }
}
