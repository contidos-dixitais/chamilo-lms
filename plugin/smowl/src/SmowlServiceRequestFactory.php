<?php
/* For licensing terms, see /license.txt */

/**
 * Class SmowlServiceRequestFactory.
 */
class SmowlServiceRequestFactory
{
    /**
     * @param SimpleXMLElement $xml
     *
     * @return SmowlServiceRequest|null
     */
    public static function create(SimpleXMLElement $xml)
    {
        $bodyChildren = $xml->imsx_POXBody->children();

        if (!empty($bodyChildren)) {
            $name = $bodyChildren->getName();

            switch ($name) {
                case 'replaceResultRequest':
                    return new SmowlServiceReplaceRequest($xml);
                case 'readResultRequest':
                    return new SmowlServiceReadRequest($xml);
                case 'deleteResultRequest':
                    return new SmowlServiceDeleteRequest($xml);
                default:
                    $name = str_replace(['ResultRequest', 'Request'], '', $name);

                    return new SmowlServiceUnsupportedRequest($xml, $name);
            }
        }

        return null;
    }
}
