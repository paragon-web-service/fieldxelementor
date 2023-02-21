<?php


namespace IDP\Handler;

use IDP\Exception\InvalidPhoneException;
use IDP\Exception\OTPRequiredException;
use IDP\Exception\OTPSendingFailedException;
use IDP\Exception\OTPValidationFailedException;
use IDP\Exception\PasswordMismatchException;
use IDP\Exception\PasswordResetFailedException;
use IDP\Exception\PasswordStrengthException;
use IDP\Exception\RegistrationRequiredFieldsException;
use IDP\Exception\RequiredFieldsException;
use IDP\Helper\Utilities\MoIDPUtility;
class RegistrationUtility extends BaseHandler
{
    public function checkPwdStrength($Gl, $xq)
    {
        if (!(strlen($xq) < 6 || strlen($Gl) < 6)) {
            goto Yd;
        }
        throw new PasswordStrengthException();
        Yd:
    }
    public function pwdAndCnfrmPwdMatch($Gl, $xq)
    {
        if (!($xq != $Gl)) {
            goto JV;
        }
        throw new PasswordMismatchException();
        JV:
    }
    public function checkIfRegReqFieldsEmpty($bn)
    {
        try {
            $this->checkIfRequiredFieldsEmpty($bn);
        } catch (RequiredFieldsException $S5) {
            throw new RegistrationRequiredFieldsException();
        }
    }
    public function isValidPhoneNumber($mA)
    {
        if (MoIDPUtility::validatePhoneNumber($mA)) {
            goto mH;
        }
        throw new InvalidPhoneException($mA);
        mH:
    }
    public function checkIfOTPEntered($bn)
    {
        try {
            $this->checkIfRequiredFieldsEmpty($bn);
        } catch (RequiredFieldsException $S5) {
            throw new OTPRequiredException();
        }
    }
    public function checkIfOTPValidationPassed($bn, $xO)
    {
        if (!(!array_key_exists($xO, $bn) || strcasecmp($bn[$xO], "\x53\x55\x43\x43\105\123\x53") != 0)) {
            goto k2;
        }
        throw new OTPValidationFailedException();
        k2:
    }
    public function checkIfOTPSentSuccessfully($bn, $xO)
    {
        if (!(!array_key_exists($xO, $bn) || strcasecmp($bn[$xO], "\123\125\x43\x43\x45\x53\x53") != 0)) {
            goto Us;
        }
        throw new OTPSendingFailedException();
        Us:
    }
    public function checkIfPasswordResetSuccesfully($bn, $xO)
    {
        if (!(!array_key_exists($xO, $bn) || strcasecmp($bn[$xO], "\123\125\x43\x43\x45\123\x53") != 0)) {
            goto ag;
        }
        throw new PasswordResetFailedException();
        ag:
    }
}
