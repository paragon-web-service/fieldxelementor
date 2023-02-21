<?php


namespace IDP\Handler;

use IDP\Exception\InvalidEncryptionCertException;
use IDP\Exception\IssuerValueAlreadyInUseException;
use IDP\Exception\NoServiceProviderConfiguredException;
use IDP\Exception\SPNameAlreadyInUseException;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\Utilities\MoIDPUtility;
class SPSettingsUtility extends BaseHandler
{
    public function checkIfValidServiceProvider($di, $sP = FALSE, $xO = NULL)
    {
        if (!($sP && array_key_exists($xO, $di) && MoIDPUtility::isBlank($di[$xO]) || MoIDPUtility::isBlank($di))) {
            goto OU;
        }
        throw new NoServiceProviderConfiguredException();
        OU:
    }
    public function checkIssuerAlreadyInUse($QB, $e6, $Pk)
    {
        global $dbIDPQueries;
        $di = $dbIDPQueries->get_sp_from_issuer($QB);
        if (!(!MoIDPUtility::isBlank($di) && !MoIDPUtility::isBlank($e6) && $di->id != $e6)) {
            goto QS;
        }
        throw new IssuerValueAlreadyInUseException($di);
        QS:
        if (!(!MoIDPUtility::isBlank($di) && !MoIDPUtility::isBlank($Pk) && $Pk != $di->mo_idp_sp_name)) {
            goto hi;
        }
        throw new IssuerValueAlreadyInUseException($di);
        hi:
    }
    public function checkNameAlreaydInUse($Pk, $e6 = NULL)
    {
        global $dbIDPQueries;
        $di = $dbIDPQueries->get_sp_from_name($Pk);
        if (!(!MoIDPUtility::isBlank($di) && !MoIDPUtility::isBlank($e6) && $di->id != $e6)) {
            goto dW;
        }
        throw new SPNameAlreadyInUseException($di);
        dW:
        if (!(!MoIDPUtility::isBlank($di) && MoIDPUtility::isBlank($e6))) {
            goto kW;
        }
        throw new SPNameAlreadyInUseException($di);
        kW:
    }
    public function checkIfValidEncryptionCertProvided($C2, $Ix)
    {
        if (!(!MoIDPUtility::isBlank($C2) && MoIDPUtility::isBlank($Ix))) {
            goto cz;
        }
        throw new InvalidEncryptionCertException();
        cz:
    }
}
