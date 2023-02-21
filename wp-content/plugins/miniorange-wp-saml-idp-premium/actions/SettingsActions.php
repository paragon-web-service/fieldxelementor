<?php


namespace IDP\Actions;

use IDP\Exception\InvalidEncryptionCertException;
use IDP\Exception\InvalidOperationException;
use IDP\Exception\IssuerValueAlreadyInUseException;
use IDP\Exception\InvalidMetaDataFileException;
use IDP\Exception\InvalidMetaDataUrlException;
use IDP\Exception\InvalidSPSSODescriptorException;
use IDP\Exception\JSErrorException;
use IDP\Exception\NoServiceProviderConfiguredException;
use IDP\Exception\NotRegisteredException;
use IDP\Exception\RequiredFieldsException;
use IDP\Exception\SPNameAlreadyInUseException;
use IDP\Handler\AttributeSettingsHandler;
use IDP\Handler\CustomLoginURLHandler;
use IDP\Handler\RoleBasedSSOHandler;
use IDP\Handler\FeedbackHandler;
use IDP\Handler\IDPSettingsHandler;
use IDP\Handler\SPSettingsHandler;
use IDP\Handler\SupportHandler;
use IDP\Handler\MetadataReaderHandler;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
class SettingsActions extends BasePostAction
{
    use Instance;
    private $handler;
    private $supportHandler;
    private $idpSettingsHandler;
    private $feedbackHandler;
    private $attrSettingsHandler;
    private $metadataReaderHandler;
    private $customLoginURLHandler;
    private $roleBasedSSOHandler;
    public function __construct()
    {
        $this->handler = SPSettingsHandler::instance();
        $this->supportHandler = SupportHandler::instance();
        $this->idpSettingsHandler = IDPSettingsHandler::instance();
        $this->feedbackHandler = FeedbackHandler::instance();
        $this->attrSettingsHandler = AttributeSettingsHandler::instance();
        $this->metadataReaderHandler = MetadataReaderHandler::instance();
        $this->roleBasedSSOHandler = RoleBasedSSOHandler::instance();
        $this->customLoginURLHandler = CustomLoginURLHandler::instance();
        $this->_nonce = "\x69\144\160\137\x73\x65\164\164\151\156\x67\x73";
        parent::__construct();
    }
    private $funcs = array("\x6d\157\x5f\141\x64\x64\137\x69\x64\160", "\155\x6f\137\145\x64\151\x74\137\151\x64\x70", "\x6d\157\x5f\x73\150\x6f\x77\137\x73\160\x5f\163\145\x74\164\x69\156\x67\x73", "\x6d\157\x5f\x69\144\160\x5f\x64\x65\154\145\x74\145\x5f\163\x70\137\x73\x65\164\x74\x69\x6e\147\x73", "\x6d\157\x5f\151\144\x70\137\145\156\164\151\x74\x79\137\151\144", "\143\150\141\x6e\x67\x65\x5f\156\x61\155\x65\x5f\x69\x64", "\x6d\x6f\x5f\x69\x64\160\x5f\143\157\156\164\141\143\164\x5f\x75\x73\137\161\x75\x65\162\171\137\x6f\x70\x74\151\157\x6e", "\155\157\x5f\x69\x64\x70\x5f\146\x65\145\144\142\141\x63\153\137\x6f\x70\164\x69\157\156", "\155\x6f\x5f\x69\144\160\137\141\x74\x74\162\x5f\163\145\x74\x74\x69\x6e\x67\163", "\x6d\x6f\137\141\x64\x64\137\x72\157\x6c\x65\137\141\164\x74\x72\x69\142\165\x74\x65", "\x6d\157\x5f\x73\141\x76\145\x5f\x63\165\x73\164\157\155\137\151\x64\x70\x5f\141\x74\164\162", "\155\x6f\137\163\150\x6f\167\137\163\163\157\137\x75\x73\145\162\x73", "\155\x6f\137\x69\144\160\x5f\165\160\154\x6f\x61\144\137\155\x65\164\141\144\141\164\x61", "\x6d\x6f\137\x69\x64\x70\x5f\x65\x64\x69\x74\x5f\155\145\164\x61\144\x61\164\141", "\155\157\137\151\x64\160\x5f\141\144\144\x5f\143\x75\163\x74\157\x6d\137\x6c\157\x67\x69\156\x5f\x75\162\154", "\155\157\137\x69\144\x70\137\162\145\163\x74\162\x69\x63\164\137\x72\157\x6c\x65\x73");
    public function handle_post_data()
    {
        if (!(current_user_can("\x6d\x61\156\141\147\x65\x5f\157\160\x74\x69\157\x6e\163") and isset($_POST["\x6f\160\x74\151\x6f\156"]))) {
            goto qf;
        }
        $C2 = trim($_POST["\x6f\x70\164\151\157\156"]);
        try {
            $this->route_post_data($C2);
            $this->changeSPInSession($_POST);
        } catch (NotRegisteredException $S5) {
            do_action("\155\x6f\x5f\x69\x64\x70\137\163\150\x6f\x77\x5f\155\x65\x73\163\x61\x67\145", $S5->getMessage(), "\105\x52\x52\117\122");
        } catch (NoServiceProviderConfiguredException $S5) {
            do_action("\155\157\137\151\x64\160\137\x73\150\157\167\x5f\155\x65\x73\163\x61\147\x65", $S5->getMessage(), "\105\x52\x52\117\122");
        } catch (JSErrorException $S5) {
            do_action("\155\157\x5f\x69\144\x70\137\163\150\157\x77\x5f\155\x65\x73\163\x61\147\145", $S5->getMessage(), "\105\x52\122\117\122");
        } catch (RequiredFieldsException $S5) {
            do_action("\155\157\x5f\151\x64\x70\137\x73\150\157\x77\x5f\x6d\145\163\163\141\x67\x65", $S5->getMessage(), "\x45\122\x52\117\122");
        } catch (SPNameAlreadyInUseException $S5) {
            do_action("\155\x6f\x5f\x69\144\160\x5f\x73\x68\157\167\137\x6d\x65\x73\x73\141\x67\145", $S5->getMessage(), "\105\x52\x52\x4f\122");
        } catch (IssuerValueAlreadyInUseException $S5) {
            do_action("\155\157\x5f\151\144\160\137\x73\150\157\167\137\155\x65\163\163\141\147\x65", $S5->getMessage(), "\x45\x52\x52\117\122");
        } catch (InvalidEncryptionCertException $S5) {
            do_action("\155\157\x5f\x69\x64\x70\x5f\163\x68\157\167\137\155\145\x73\163\141\147\x65", $S5->getMessage(), "\x45\122\122\x4f\x52");
        } catch (InvalidOperationException $S5) {
            do_action("\155\x6f\137\151\x64\160\137\x73\x68\x6f\x77\137\155\145\x73\x73\x61\x67\x65", $S5->getMessage(), "\x45\x52\122\x4f\x52");
        } catch (InvalidMetaDataUrlException $S5) {
            do_action("\155\157\137\151\144\x70\x5f\x73\x68\157\x77\x5f\x6d\x65\x73\163\x61\x67\145", $S5->getMessage(), "\105\x52\122\117\122");
        } catch (InvalidMetaDataFileException $S5) {
            do_action("\x6d\x6f\x5f\151\x64\160\137\x73\x68\157\167\137\x6d\145\163\163\141\147\x65", $S5->getMessage(), "\105\x52\122\x4f\122");
        } catch (InvalidSPSSODescriptorException $S5) {
            do_action("\x6d\157\x5f\x69\144\x70\137\163\x68\157\x77\x5f\155\145\163\163\141\147\145", $S5->getMessage(), "\105\122\x52\x4f\122");
        } catch (\Exception $S5) {
            if (!MSI_DEBUG) {
                goto B9;
            }
            MoIDPUtility::mo_debug("\x45\x78\143\145\x70\164\151\157\x6e\x20\x4f\x63\x63\165\162\x72\x65\144\x20\144\x75\162\151\156\x67\x20\x53\x53\x4f\x20" . $S5);
            B9:
            wp_die($S5->getMessage());
        }
        qf:
    }
    public function route_post_data($C2)
    {
        switch ($C2) {
            case $this->funcs[0]:
                $this->handler->_mo_idp_save_new_sp($_POST);
                goto fw;
            case $this->funcs[1]:
                $this->handler->_mo_idp_edit_sp($_POST);
                goto fw;
            case $this->funcs[2]:
                $this->handler->_mo_sp_change_settings($_POST);
                goto fw;
            case $this->funcs[3]:
                $this->handler->mo_idp_delete_sp_settings($_POST);
                goto fw;
            case $this->funcs[4]:
                $this->idpSettingsHandler->mo_change_idp_entity_id($_POST);
                goto fw;
            case $this->funcs[5]:
                $this->handler->mo_idp_change_name_id($_POST);
                goto fw;
            case $this->funcs[6]:
                $this->supportHandler->_mo_idp_support_query($_POST);
                goto fw;
            case $this->funcs[7]:
                $this->feedbackHandler->_mo_send_feedback($_POST);
                goto fw;
            case $this->funcs[8]:
                $this->attrSettingsHandler->mo_idp_save_attr_settings($_POST);
                goto fw;
            case $this->funcs[9]:
                $this->attrSettingsHandler->mo_add_role_attribute($_POST);
                goto fw;
            case $this->funcs[10]:
                $this->attrSettingsHandler->mo_save_custom_idp_attr($_POST);
                goto fw;
            case $this->funcs[11]:
                $this->handler->show_sso_users($_POST);
                goto fw;
            case $this->funcs[12]:
                $this->metadataReaderHandler->handle_upload_metadata($_POST);
                goto fw;
            case $this->funcs[13]:
                $this->metadataReaderHandler->handle_edit_metadata($_POST);
                goto fw;
            case $this->funcs[14]:
                $this->customLoginURLHandler->handle_custom_login_url($_POST);
                goto fw;
            case $this->funcs[15]:
                $this->roleBasedSSOHandler->handle_role_based_sso($_POST);
                goto fw;
        }
        DB:
        fw:
    }
    public function changeSPInSession($y8)
    {
        MoIDPUtility::startSession();
        global $dbIDPQueries;
        $aw = $dbIDPQueries->get_sp_list();
        $_SESSION["\x53\x50"] = array_key_exists("\163\145\162\x76\x69\x63\145\x5f\x70\162\x6f\x76\x69\x64\x65\x72", $y8) && !MoIDPUtility::isBlank($y8["\163\145\162\x76\x69\x63\145\x5f\160\x72\157\166\x69\x64\145\x72"]) ? $y8["\x73\x65\x72\x76\151\x63\145\x5f\x70\x72\x6f\166\x69\144\x65\x72"] : (empty($aw) ? 1 : $aw[0]->id);
    }
}
