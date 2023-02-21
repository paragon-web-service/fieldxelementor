<?php


namespace IDP\Handler;

use IDP\Exception\RequiredFieldsException;
use IDP\Exception\InvalidMetaDataFileException;
use IDP\Exception\InvalidMetaDataUrlException;
use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Database\MoDbQueries;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\SAML2\MetadataReader;
use IDP\Helper\Utilities\SAMLUtilities;
class MetadataReaderHandler extends SPSettingsUtility
{
    use Instance;
    private function __construct()
    {
    }
    public function handle_upload_metadata($y8)
    {
        if (!(isset($_FILES["\155\x65\x74\x61\x64\141\164\141\137\146\x69\x6c\145"]) || isset($y8["\x6d\x65\164\141\x64\x61\x74\x61\137\x75\162\154"]))) {
            goto Lg;
        }
        if (!empty($_FILES["\155\145\164\141\x64\x61\x74\x61\137\x66\151\154\145"]["\x74\155\x70\x5f\x6e\141\x6d\145"])) {
            goto QW;
        }
        $L3 = filter_var($y8["\155\145\x74\x61\144\141\164\x61\x5f\165\162\154"], FILTER_SANITIZE_URL);
        $IY = curl_init();
        curl_setopt($IY, CURLOPT_URL, $L3);
        curl_setopt($IY, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($IY, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($IY, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IY, CURLOPT_SSL_VERIFYHOST, false);
        $bo = curl_exec($IY);
        curl_close($IY);
        goto A0;
        QW:
        $bo = @file_get_contents($_FILES["\155\x65\x74\141\144\x61\x74\x61\137\x66\151\x6c\145"]["\x74\155\x70\x5f\x6e\x61\155\145"]);
        A0:
        $this->upload_metadata($bo, $y8);
        Lg:
    }
    public function handle_edit_metadata($y8)
    {
        if (!(isset($_FILES["\x6d\145\x74\141\144\x61\164\141\137\146\x69\154\145"]) || isset($y8["\155\145\164\141\x64\x61\x74\141\x5f\165\x72\154"]))) {
            goto y6;
        }
        if (!empty($_FILES["\155\x65\164\141\x64\x61\164\141\x5f\146\151\x6c\145"]["\164\155\x70\x5f\156\141\155\x65"])) {
            goto VC;
        }
        $L3 = filter_var($y8["\155\145\x74\141\144\x61\164\141\137\x75\x72\154"], FILTER_SANITIZE_URL);
        $IY = curl_init();
        curl_setopt($IY, CURLOPT_URL, $L3);
        curl_setopt($IY, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($IY, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($IY, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($IY, CURLOPT_SSL_VERIFYHOST, false);
        $bo = curl_exec($IY);
        curl_close($IY);
        goto Dv;
        VC:
        $bo = @file_get_contents($_FILES["\x6d\145\x74\141\144\141\164\141\137\x66\x69\154\x65"]["\164\x6d\160\x5f\x6e\x61\x6d\x65"]);
        Dv:
        $this->edit_metadata($bo, $y8);
        y6:
    }
    private function upload_metadata($bo, $y8)
    {
        $HN = set_error_handler(array($this, "\x68\x61\156\x64\154\145\130\x6d\154\x45\162\x72\x6f\162"));
        $i2 = new \DOMDocument();
        $i2->loadXML($bo);
        restore_error_handler();
        $bh = $i2->firstChild;
        if (!empty($bh)) {
            goto Ku;
        }
        if (empty($_FILES["\x6d\x65\164\141\x64\141\x74\x61\x5f\146\151\154\145"]["\x74\x6d\160\x5f\x6e\141\x6d\145"])) {
            goto rD;
        }
        throw new InvalidMetaDataFileException(MoIDPMessages::showMessage("\111\x4e\x56\x41\x4c\x49\x44\x5f\115\x45\124\101\104\x41\124\101\x5f\x46\111\114\105"));
        rD:
        if (empty($y8["\155\x65\x74\x61\x64\x61\x74\141\137\x75\x72\154"])) {
            goto XR;
        }
        throw new InvalidMetaDataUrlException();
        XR:
        goto w1;
        Ku:
        $s1 = new MetadataReader($i2);
        $Zp = $s1->getServiceProviders();
        if (!(empty($Zp) && !empty($_FILES["\x6d\145\x74\141\x64\x61\x74\x61\137\x66\151\154\x65"]["\x74\x6d\x70\x5f\156\141\x6d\x65"]))) {
            goto fR;
        }
        throw new InvalidMetaDataFileException(MoIDPMessages::showMessage("\x49\x4e\126\x41\x4c\111\104\x5f\x4d\x45\124\101\x44\x41\x54\x41\137\106\111\x4c\x45"));
        fR:
        if (!(empty($Zp) && !empty($y8["\x6d\145\164\141\x64\141\164\x61\137\x75\162\154"]))) {
            goto Ep;
        }
        throw new InvalidMetaDataUrlException();
        Ep:
        $this->_mo_idp_save_new_sp($Zp[0], $y8);
        w1:
    }
    private function edit_metadata($bo, $y8)
    {
        $HN = set_error_handler(array($this, "\x68\141\156\144\154\x65\x58\x6d\154\x45\x72\162\157\162"));
        $i2 = new \DOMDocument();
        $i2->loadXML($bo);
        restore_error_handler();
        $bh = $i2->firstChild;
        if (!empty($bh)) {
            goto pI;
        }
        if (empty($_FILES["\x6d\145\x74\x61\144\x61\x74\141\137\146\151\154\x65"]["\164\155\x70\137\x6e\x61\155\x65"])) {
            goto vA;
        }
        throw new InvalidMetaDataFileException(MoIDPMessages::showMessage("\x49\116\126\101\x4c\111\x44\137\115\105\124\x41\x44\x41\x54\101\x5f\x46\x49\114\105"));
        vA:
        if (empty($y8["\155\145\164\x61\144\141\x74\x61\x5f\165\x72\154"])) {
            goto N4;
        }
        throw new InvalidMetaDataUrlException();
        N4:
        goto We;
        pI:
        $s1 = new MetadataReader($i2);
        $Zp = $s1->getServiceProviders();
        if (!(empty($Zp) && !empty($_FILES["\155\145\164\141\x64\x61\x74\x61\137\146\x69\x6c\x65"]["\x74\155\x70\137\156\141\x6d\145"]))) {
            goto K4;
        }
        throw new InvalidMetaDataFileException(MoIDPMessages::showMessage("\x49\x4e\x56\x41\114\111\x44\137\115\105\124\x41\x44\x41\124\x41\137\x46\111\x4c\105"));
        K4:
        if (!(empty($Zp) && !empty($y8["\155\145\164\x61\144\x61\x74\141\137\x75\162\154"]))) {
            goto I8;
        }
        throw new InvalidMetaDataUrlException();
        I8:
        $this->_mo_idp_edit_sp($Zp[0], $y8);
        We:
    }
    public function handleXmlError($Bt, $P2, $u1, $fg)
    {
        if ($Bt == E_WARNING && substr_count($P2, "\x44\x4f\x4d\104\157\x63\165\x6d\x65\156\164\x3a\72\154\157\141\x64\x58\115\x4c\x28\x29") > 0) {
            goto t9;
        }
        return false;
        goto yW;
        t9:
        return;
        yW:
    }
    public function _mo_idp_save_new_sp($j4, $y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        if (!(MoIDPUtility::isBlank($y8["\x73\141\155\154\x5f\163\x65\x72\x76\151\143\145\x5f\x6d\145\x74\141\x64\141\x74\x61\x5f\x70\162\157\166\151\144\x65\x72"]) || MoIDPUtility::isBlank($j4->entityID) || MoIDPUtility::isBlank($j4->acsUrl) || MoIDPUtility::isBlank($j4->nameID))) {
            goto G2;
        }
        throw new RequiredFieldsException();
        G2:
        $kl = $fL = array();
        $iv = $kl["\x6d\157\137\x69\x64\x70\137\163\160\x5f\156\141\155\145"] = $fL["\x6d\157\137\x69\144\160\x5f\163\x70\x5f\x6e\x61\155\x65"] = sanitize_text_field($y8["\x73\x61\x6d\154\x5f\x73\145\162\x76\151\143\x65\x5f\x6d\x65\164\141\144\x61\x74\141\137\160\162\157\x76\x69\x64\x65\x72"]);
        $QB = $fL["\x6d\x6f\137\x69\144\x70\137\163\160\137\x69\x73\163\x75\145\x72"] = sanitize_text_field($j4->entityID);
        $this->checkIssuerAlreadyInUse($QB, NULL, $iv);
        $this->checkNameAlreaydInUse($iv);
        $fL = $this->collectData($j4, $fL);
        $nx = $dbIDPQueries->insert_sp_data($fL);
        do_action("\x6d\157\137\151\x64\160\137\163\150\x6f\x77\x5f\x6d\x65\x73\x73\141\x67\x65", MoIDPMessages::showMessage("\x53\105\124\124\x49\x4e\107\x53\x5f\x53\x41\x56\105\104"), "\x53\x55\103\103\105\123\x53");
    }
    public function _mo_idp_edit_sp($j4, $y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        if (!(MoIDPUtility::isBlank($y8["\x73\141\x6d\154\x5f\163\145\x72\x76\151\x63\x65\x5f\x6d\x65\x74\141\144\x61\164\x61\x5f\160\162\157\166\x69\144\x65\x72"]) || MoIDPUtility::isBlank($j4->entityID) || MoIDPUtility::isBlank($j4->acsUrl) || MoIDPUtility::isBlank($j4->nameID))) {
            goto T1;
        }
        throw new RequiredFieldsException();
        T1:
        $this->checkIfValidServiceProvider($y8, TRUE, "\163\x65\x72\166\x69\143\145\137\x70\162\x6f\166\151\144\145\x72\x5f");
        $kl = $fL = array();
        $e6 = $kl["\151\x64"] = $y8["\x73\145\x72\x76\151\x63\x65\x5f\160\162\157\x76\x69\x64\x65\x72\x5f"];
        $iv = $fL["\x6d\157\x5f\151\144\160\137\163\x70\x5f\156\141\x6d\145"] = sanitize_text_field($y8["\x73\x61\x6d\x6c\137\163\145\162\x76\x69\x63\145\137\x6d\145\x74\141\x64\x61\x74\x61\137\x70\x72\x6f\x76\x69\x64\145\162"]);
        $QB = $fL["\x6d\x6f\137\151\x64\160\x5f\163\x70\x5f\x69\163\x73\165\x65\x72"] = sanitize_text_field($j4->entityID);
        $this->checkIssuerAlreadyInUse($QB, NULL, $iv);
        $this->checkNameAlreaydInUse($iv);
        $fL = $this->collectData($j4, $fL);
        $nx = $dbIDPQueries->update_sp_data($fL, $kl);
        do_action("\155\157\x5f\151\x64\x70\137\163\x68\x6f\167\x5f\x6d\145\x73\163\141\147\145", MoIDPMessages::showMessage("\123\x45\x54\x54\x49\x4e\x47\123\137\123\x41\126\x45\104"), "\123\x55\103\103\105\x53\123");
    }
    private function collectData($j4, $fL)
    {
        $fL["\155\x6f\137\x69\x64\x70\x5f\141\x63\163\137\165\x72\x6c"] = sanitize_text_field($j4->acsUrl);
        $fL["\x6d\157\137\151\x64\x70\x5f\156\x61\155\x65\x69\x64\137\146\157\162\x6d\x61\164"] = sanitize_text_field($j4->nameID);
        $fL["\x6d\x6f\x5f\x69\144\x70\x5f\160\162\157\x74\157\143\x6f\x6c\x5f\x74\x79\160\145"] = sanitize_text_field("\123\101\x4d\x4c");
        $Ix = isset($j4->signingCertificate[0]) ? SAMLUtilities::sanitize_certificate(trim($j4->signingCertificate[0])) : NULL;
        $KG = isset($j4->encryptionCertificate[0]) ? SAMLUtilities::sanitize_certificate(trim($j4->encryptionCertificate[0])) : NULL;
        $Hi = NULL;
        $SX = isset($j4->sloBindingType) ? sanitize_text_field($j4->sloBindingType) : "\x48\x74\164\x70\122\145\144\151\x72\145\x63\164";
        $sB = isset($j4->logoutDetails[$SX]) ? sanitize_text_field($j4->logoutDetails[$SX]) : NULL;
        if ($SX == "\x48\124\124\120\55\x50\117\123\124") {
            goto Gn;
        }
        if ($SX == "\110\x54\124\120\x2d\122\x65\x64\x69\x72\x65\143\164") {
            goto Ua;
        }
        goto Sn;
        Gn:
        $SX = "\110\x74\x74\160\120\157\163\164";
        goto Sn;
        Ua:
        $SX = "\x48\x74\164\x70\122\145\x64\151\162\x65\143\164";
        Sn:
        $fL["\155\x6f\137\151\144\x70\x5f\154\157\147\157\x75\x74\137\x75\162\154"] = $sB;
        $fL["\155\157\x5f\151\144\160\x5f\143\145\x72\164"] = $Ix;
        $fL["\155\x6f\x5f\x69\144\160\x5f\x63\x65\162\164\137\x65\156\x63\x72\171\x70\x74"] = $KG;
        $fL["\x6d\157\137\151\x64\160\137\144\x65\x66\141\x75\x6c\x74\137\x72\145\154\141\x79\x53\x74\x61\x74\x65"] = $Hi;
        $fL["\155\157\x5f\151\144\x70\x5f\154\x6f\147\157\x75\164\x5f\x62\x69\x6e\144\151\156\x67\x5f\164\x79\160\145"] = $SX;
        $fL["\x6d\x6f\x5f\x69\144\160\x5f\x72\x65\x73\160\x6f\x6e\163\x65\137\163\151\x67\x6e\145\x64"] = isset($j4->assertionSigned) && strcmp($j4->assertionSigned, "\164\x72\x75\x65") != 0 ? 1 : NULL;
        $fL["\x6d\x6f\137\151\144\x70\x5f\x61\x73\163\145\x72\164\151\x6f\x6e\137\163\151\147\x6e\x65\x64"] = isset($j4->assertionSigned) && strcmp($j4->assertionSigned, "\x74\162\x75\x65") == 0 ? 1 : NULL;
        $fL["\x6d\x6f\137\151\144\160\x5f\x65\156\143\x72\171\160\164\145\144\137\x61\x73\163\145\x72\164\x69\157\x6e"] = NULL;
        $this->checkIfValidEncryptionCertProvided($fL["\x6d\x6f\137\151\x64\160\x5f\x65\x6e\143\x72\x79\160\x74\145\x64\137\141\x73\x73\x65\162\164\x69\x6f\156"], $fL["\155\157\x5f\x69\x64\160\137\143\x65\162\164\x5f\x65\156\x63\162\171\160\x74"]);
        return $fL;
    }
}
