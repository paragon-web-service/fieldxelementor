<?php


namespace IDP\Helper\Factory;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\SAML2\GenerateLogoutResponse;
use IDP\Helper\SAML2\GenerateResponse;
use IDP\Helper\WSFED\GenerateWsFedResponse;
use IDP\Helper\JWT\GenerateJwtToken;
class ResponseDecisionHandler
{
    public static function getResponseHandler($ZV, $VW)
    {
        switch ($ZV) {
            case MoIDPConstants::LOGOUT_RESPONSE:
                return new GenerateLogoutResponse($VW[0], $VW[1], $VW[2]);
                goto Hr;
            case MoIDPConstants::SAML_RESPONSE:
                return new GenerateResponse($VW[0], $VW[1], $VW[2], $VW[3], $VW[4], $VW[5], $VW[6], $VW[7]);
                goto Hr;
            case MoIDPConstants::WS_FED_RESPONSE:
                return new GenerateWsFedResponse($VW[0], $VW[1], $VW[2], $VW[3], $VW[4], $VW[5], $VW[6]);
                goto Hr;
            case MoIDPConstants::JWT_RESPONSE:
                return new GenerateJwtToken($VW[1], $VW[2], $VW[3], $VW[4], $VW[6]);
                goto Hr;
        }
        uG:
        Hr:
    }
}
