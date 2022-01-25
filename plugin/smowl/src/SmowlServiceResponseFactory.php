<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlServiceResponseFactory.
 */
class SmowlServiceResponseFactory
{
    /**
     * @param string                      $type
     * @param SmowlServiceResponseStatus $statusInfo
     * @param mixed                       $bodyParam
     *
     * @return SmowlServiceResponse|null
     */
    public static function create($type, SmowlServiceResponseStatus $statusInfo, $bodyParam = null)
    {
        switch ($type) {
            case SmowlServiceResponse::TYPE_REPLACE:
                return new SmowlServiceReplaceResponse($statusInfo, $bodyParam);
            case SmowlServiceResponse::TYPE_READ:
                return new SmowlServiceReadResponse($statusInfo, $bodyParam);
            case SmowlServiceResponse::TYPE_DELETE:
                return new SmowlServiceDeleteResponse($statusInfo, $bodyParam);
            default:
                return new SmowlServiceUnsupportedResponse($statusInfo, $type);
        }

        return null;
    }
}