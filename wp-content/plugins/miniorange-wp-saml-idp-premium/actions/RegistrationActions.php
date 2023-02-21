<?php


namespace IDP\Actions;

use IDP\Exception\InvalidPhoneException;
use IDP\Exception\OTPRequiredException;
use IDP\Exception\OTPSendingFailedException;
use IDP\Exception\OTPValidationFailedException;
use IDP\Exception\PasswordMismatchException;
use IDP\Exception\PasswordResetFailedException;
use IDP\Exception\PasswordStrengthException;
use IDP\Exception\RegistrationRequiredFieldsException;
use IDP\Handler\LKHandler;
use IDP\Handler\RegistrationHandler;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
class RegistrationActions extends BasePostAction
{
    use Instance;
    private $handler;
    private $lkHandler;
    private $funcs = array("\155\157\137\x69\144\160\x5f\x72\145\x67\x69\163\164\x65\162\137\143\x75\x73\164\x6f\155\145\x72", "\155\x6f\x5f\151\144\160\x5f\166\x61\154\151\144\141\164\x65\137\x6f\164\x70", "\155\157\137\151\144\x70\137\160\150\157\x6e\x65\x5f\x76\145\x72\151\x66\x69\143\141\164\151\x6f\156", "\155\157\137\151\x64\x70\137\143\157\156\156\x65\x63\x74\137\x76\x65\162\151\146\x79\137\x63\165\x73\164\157\x6d\145\162", "\x6d\x6f\x5f\151\x64\160\x5f\x66\x6f\x72\147\157\164\137\160\x61\163\x73\167\x6f\162\144", "\x6d\x6f\137\151\x64\160\x5f\x67\157\137\x62\x61\143\x6b", "\155\x6f\137\151\144\x70\137\162\x65\163\x65\156\x64\137\x6f\164\160", "\162\x65\x6d\x6f\x76\145\x5f\151\144\160\x5f\141\x63\143\x6f\165\x6e\x74", "\155\157\x5f\151\144\x70\137\166\x65\162\151\146\171\137\154\x69\143\145\x6e\x73\145", "\x72\x65\146\x72\x65\163\x68\137\163\x70\137\165\x73\145\162\x73");
    public function __construct()
    {
        $this->handler = RegistrationHandler::instance();
        $this->lkHandler = LKHandler::instance();
        parent::__construct();
    }
    public function handle_post_data()
    {
        if (!(current_user_can("\x6d\141\x6e\x61\x67\x65\137\x6f\160\164\151\157\x6e\x73") and isset($_POST["\x6f\x70\x74\151\x6f\156"]))) {
            goto Z2;
        }
        $C2 = trim($_POST["\x6f\160\x74\x69\157\x6e"]);
        try {
            $this->route_post_data($C2);
        } catch (RegistrationRequiredFieldsException $S5) {
            do_action("\155\x6f\x5f\151\x64\160\137\163\150\157\167\x5f\x6d\x65\x73\163\x61\x67\145", $S5->getMessage(), "\x45\x52\x52\x4f\x52");
        } catch (PasswordStrengthException $S5) {
            do_action("\155\157\137\151\x64\x70\137\x73\150\157\167\137\x6d\145\163\x73\x61\x67\x65", $S5->getMessage(), "\105\x52\x52\117\x52");
        } catch (PasswordMismatchException $S5) {
            do_action("\x6d\x6f\137\151\144\160\x5f\x73\150\157\x77\x5f\155\x65\163\163\141\147\145", $S5->getMessage(), "\x45\x52\x52\x4f\122");
        } catch (InvalidPhoneException $S5) {
            update_site_option("\x6d\157\137\x69\x64\160\x5f\162\145\x67\151\x73\x74\162\x61\164\x69\157\x6e\x5f\x73\x74\x61\x74\x75\x73", "\x4d\117\x5f\117\124\120\137\104\105\114\x49\x56\105\122\x45\x44\x5f\106\101\111\x4c\125\122\105");
            do_action("\155\157\137\151\144\160\x5f\163\x68\x6f\x77\137\155\145\x73\163\x61\x67\x65", $S5->getMessage(), "\x45\122\x52\x4f\122");
        } catch (OTPRequiredException $S5) {
            update_site_option("\x6d\157\x5f\x69\144\x70\137\162\x65\147\151\x73\164\x72\141\164\x69\157\156\137\163\164\x61\164\165\163", "\x4d\117\x5f\x4f\124\x50\x5f\126\x41\x4c\x49\104\x41\124\111\x4f\116\x5f\106\x41\x49\x4c\125\x52\x45");
            do_action("\x6d\157\x5f\151\x64\x70\x5f\163\150\157\167\x5f\155\x65\x73\x73\x61\147\x65", $S5->getMessage(), "\x45\122\122\117\122");
        } catch (OTPValidationFailedException $S5) {
            update_site_option("\155\x6f\137\151\x64\160\137\x72\145\x67\151\163\164\162\141\164\151\x6f\156\x5f\x73\x74\x61\x74\x75\163", "\x4d\117\137\117\x54\120\137\126\101\114\111\104\101\x54\111\x4f\116\x5f\106\101\111\x4c\x55\122\x45");
            do_action("\x6d\157\x5f\151\x64\160\x5f\163\150\x6f\167\137\155\145\163\x73\141\147\x65", $S5->getMessage(), "\x45\122\x52\x4f\122");
        } catch (OTPSendingFailedException $S5) {
            update_site_option("\155\x6f\137\x69\x64\160\137\162\x65\x67\151\163\164\162\141\x74\x69\x6f\x6e\x5f\163\164\141\164\165\163", "\x4d\117\137\117\x54\120\x5f\104\x45\x4c\x49\126\105\122\x45\104\x5f\x46\101\111\x4c\125\122\x45");
            do_action("\155\x6f\x5f\151\x64\x70\x5f\163\150\157\167\x5f\x6d\145\x73\x73\x61\x67\x65", $S5->getMessage(), "\x45\x52\122\x4f\122");
        } catch (PasswordResetFailedException $S5) {
            do_action("\155\x6f\137\151\144\x70\x5f\x73\150\157\x77\x5f\155\x65\x73\163\141\x67\145", $S5->getMessage(), "\105\122\x52\x4f\122");
        } catch (\Exception $S5) {
            if (!MSI_DEBUG) {
                goto W2;
            }
            MoIDPUtility::mo_debug("\105\170\143\x65\160\164\151\157\156\x20\117\143\143\x75\162\x72\145\144\x20\x64\165\162\151\x6e\147\40\123\x53\117\40" . $S5);
            W2:
            wp_die($S5->getMessage());
        }
        Z2:
    }
    public function route_post_data($C2)
    {
        switch ($C2) {
            case $this->funcs[0]:
                $this->handler->_idp_register_customer($_POST);
                goto u0;
            case $this->funcs[1]:
                $this->handler->_idp_validate_otp($_POST);
                goto u0;
            case $this->funcs[2]:
                $this->handler->_mo_idp_phone_verification($_POST);
                goto u0;
            case $this->funcs[3]:
                $this->handler->_mo_idp_verify_customer($_POST);
                goto u0;
            case $this->funcs[4]:
                $this->handler->_mo_idp_forgot_password();
                goto u0;
            case $this->funcs[5]:
            case $this->funcs[7]:
                $this->handler->_mo_idp_go_back();
                goto u0;
            case $this->funcs[6]:
                $this->handler->_send_otp_token(get_site_option("\x6d\157\137\151\x64\160\137\x61\144\155\x69\x6e\137\145\155\141\151\154"), '', "\x45\115\x41\111\114");
                goto u0;
            case $this->funcs[8]:
                $this->lkHandler->_mo_verify_license($_POST);
                goto u0;
            case $this->funcs[9]:
                $this->lkHandler->refresh_sp_users_count();
                goto u0;
        }
        xh:
        u0:
    }
}
