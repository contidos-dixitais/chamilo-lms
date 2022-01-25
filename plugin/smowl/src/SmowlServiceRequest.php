<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlServiceRequest.
 */
abstract class SmowlServiceRequest
{
    /**
     * @var string
     */
    protected $responseType;

    /**
     * @var SimpleXMLElement
     */
    protected $xmlHeaderInfo;

    /**
     * @var SimpleXMLElement
     */
    protected $xmlRequest;

    /**
     * @var SmowlServiceResponseStatus
     */
    protected $statusInfo;

    /**
     * @var mixed
     */
    protected $responseBodyParam;

    /**
     * SmowlServiceRequest constructor.
     *
     * @param SimpleXMLElement $xml
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->statusInfo = new SmowlServiceResponseStatus();

        $this->xmlHeaderInfo = $xml->imsx_POXHeader->imsx_POXRequestHeaderInfo;
        $this->xmlRequest = $xml->imsx_POXBody->children();
    }

    protected function processHeader()
    {
        $info = $this->xmlHeaderInfo;

        $this->statusInfo->setMessageRefIdentifier($info->imsx_messageIdentifier);

        error_log("Service Request: tool version {$info->imsx_version} message ID {$info->imsx_messageIdentifier}");
    }

    abstract protected function processBody();

    /**
     * @return SmowlServiceResponse|null
     */
    private function generateResponse()
    {
        $response = SmowlServiceResponseFactory::create(
            $this->responseType,
            $this->statusInfo,
            $this->responseBodyParam
        );

        return $response;
    }

    /**
     * @return SmowlServiceResponse|null
     */
    public function process()
    {
        $this->processHeader();
        $this->processBody();

        return $this->generateResponse();
    }
}