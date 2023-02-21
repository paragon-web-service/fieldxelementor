<?php


namespace IDP\Helper\SAML2;

use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\SAMLUtilities;
class MetadataGenerator
{
    private $xml;
    private $issuer;
    private $samlLoginURL;
    private $wantAssertionSigned;
    private $x509Certificate;
    private $nameIdFormats;
    private $singleSignOnServiceURLs;
    private $singleLogoutServiceURLs;
    function __construct($QB, $Lo, $sh, $Au, $D7, $Qw, $Um)
    {
        $this->xml = new \DOMDocument("\x31\56\60", "\165\x74\146\55\70");
        $this->xml->preserveWhiteSpace = FALSE;
        $this->xml->formatOutput = TRUE;
        $this->issuer = $QB;
        $this->wantAssertionSigned = $Lo;
        $this->x509Certificate = $sh;
        $this->nameIDFormats = array("\x75\162\156\72\x6f\141\x73\x69\x73\x3a\156\141\155\x65\x73\x3a\x74\x63\72\123\101\x4d\x4c\x3a\61\56\61\72\156\x61\155\x65\x69\144\55\146\157\x72\155\141\164\x3a\145\155\x61\x69\x6c\x41\x64\144\x72\x65\x73\163", "\165\162\156\x3a\x6f\141\x73\151\x73\72\x6e\141\x6d\x65\x73\x3a\x74\143\72\x53\x41\115\114\x3a\61\56\x31\72\x6e\x61\x6d\145\x69\144\55\x66\157\x72\x6d\x61\x74\72\x75\156\163\160\x65\143\151\x66\151\x65\x64");
        $this->singleSignOnServiceURLs = array("\x75\x72\x6e\x3a\157\141\163\151\x73\72\156\x61\155\145\x73\72\x74\143\72\x53\101\x4d\x4c\72\x32\56\60\x3a\x62\151\x6e\144\151\x6e\147\x73\72\110\124\124\x50\x2d\120\117\123\x54" => $Au, "\x75\162\x6e\72\157\x61\x73\151\x73\x3a\156\x61\x6d\145\x73\72\164\143\72\x53\x41\115\114\72\x32\x2e\60\72\x62\x69\x6e\144\x69\156\147\x73\x3a\110\124\x54\x50\x2d\x52\x65\144\151\x72\x65\143\x74" => $D7);
        $this->singleLogoutServiceURLs = array("\x75\x72\x6e\x3a\x6f\141\163\151\163\x3a\x6e\x61\155\x65\x73\x3a\164\x63\x3a\x53\101\115\x4c\72\62\56\60\72\x62\151\156\144\151\156\x67\163\x3a\x48\124\124\x50\x2d\120\117\x53\124" => $Qw, "\x75\x72\x6e\x3a\157\141\163\151\x73\72\x6e\141\x6d\x65\163\72\164\x63\72\123\101\x4d\114\72\62\x2e\x30\x3a\x62\x69\x6e\x64\x69\x6e\x67\x73\x3a\x48\124\124\x50\x2d\x52\145\x64\x69\162\x65\x63\164" => $Um);
    }
    public function generateMetadata()
    {
        $cM = $this->createEntityDescriptorElement();
        $this->xml->appendChild($cM);
        $SO = $this->createIdpDescriptorElement();
        $cM->appendChild($SO);
        $xO = $this->createKeyDescriptorElement();
        $SO->appendChild($xO);
        $Su = $this->createSLOUrls();
        foreach ($Su as $kC) {
            $SO->appendChild($kC);
            S3:
        }
        pO:
        $Kx = $this->createNameIdFormatElements();
        foreach ($Kx as $n8) {
            $SO->appendChild($n8);
            EH:
        }
        Ah:
        $Oi = $this->createSSOUrls();
        foreach ($Oi as $Ub) {
            $SO->appendChild($Ub);
            Td:
        }
        ta:
        $AP = $this->createOrganizationElement();
        $Wa = $this->createContactPersonElement();
        $cM->appendChild($AP);
        $cM->appendChild($Wa);
        $s1 = $this->xml->saveXML();
        return $s1;
    }
    private function createEntityDescriptorElement()
    {
        $cM = $this->xml->createElementNS("\x75\x72\156\72\x6f\141\163\151\x73\x3a\156\x61\x6d\x65\x73\72\x74\x63\72\123\x41\x4d\114\72\62\x2e\60\x3a\155\145\164\141\144\141\x74\x61", "\105\156\164\151\164\x79\x44\145\x73\x63\x72\151\160\164\157\x72");
        $cM->setAttribute("\x65\156\x74\x69\x74\171\x49\104", $this->issuer);
        return $cM;
    }
    private function createIdpDescriptorElement()
    {
        $SO = $this->xml->createElementNS("\165\162\156\72\x6f\x61\x73\x69\163\x3a\x6e\x61\155\x65\163\x3a\x74\143\72\x53\x41\115\114\x3a\x32\x2e\60\72\155\x65\164\x61\x64\141\164\x61", "\111\104\x50\x53\x53\x4f\104\x65\163\x63\162\151\x70\x74\x6f\x72");
        $SO->setAttribute("\x57\141\156\x74\x41\x75\x74\150\156\122\145\x71\165\x65\x73\x74\x73\123\x69\147\x6e\145\144", $this->wantAssertionSigned);
        $SO->setAttribute("\160\x72\x6f\x74\x6f\x63\x6f\x6c\123\165\x70\160\x6f\x72\x74\x45\156\x75\x6d\x65\162\x61\x74\151\157\x6e", "\x75\162\x6e\x3a\157\141\163\151\163\72\x6e\141\155\x65\x73\72\164\x63\x3a\123\x41\x4d\114\x3a\x32\56\60\x3a\160\162\157\164\x6f\x63\x6f\x6c");
        return $SO;
    }
    private function createKeyDescriptorElement()
    {
        $xO = $this->xml->createElement("\x4b\145\x79\104\x65\x73\143\x72\151\x70\x74\157\162");
        $xO->setAttribute("\165\x73\x65", "\163\x69\x67\x6e\x69\156\147");
        $Zr = $this->generateKeyInfo();
        $xO->appendChild($Zr);
        return $xO;
    }
    private function generateKeyInfo()
    {
        $Zr = $this->xml->createElementNS("\x68\x74\x74\x70\72\x2f\57\167\x77\x77\56\167\x33\x2e\157\162\x67\57\62\x30\x30\60\57\60\71\x2f\170\155\154\x64\163\151\147\43", "\144\x73\72\113\x65\171\111\x6e\x66\x6f");
        $pE = $this->xml->createElementNS("\x68\164\x74\160\x3a\x2f\57\167\167\167\56\167\x33\x2e\157\162\147\57\62\60\x30\60\57\60\x39\x2f\x78\155\x6c\144\x73\151\x67\x23", "\x64\163\x3a\x58\65\x30\x39\104\141\164\141");
        $fh = SAMLUtilities::desanitize_certificate($this->x509Certificate);
        $j2 = $this->xml->createElementNS("\150\164\164\160\x3a\57\x2f\x77\167\167\56\167\x33\56\x6f\x72\147\x2f\x32\60\60\60\x2f\60\x39\57\170\155\154\144\x73\151\147\x23", "\144\163\72\x58\65\x30\x39\103\x65\x72\164\x69\146\x69\x63\141\164\x65", $fh);
        $pE->appendChild($j2);
        $Zr->appendChild($pE);
        return $Zr;
    }
    private function createNameIdFormatElements()
    {
        $Kx = array();
        foreach ($this->nameIDFormats as $pL) {
            array_push($Kx, $this->xml->createElementNS("\x75\162\156\72\157\141\x73\151\x73\x3a\x6e\141\155\x65\163\72\164\143\x3a\123\101\115\114\72\62\x2e\x30\x3a\x6d\145\164\x61\x64\141\x74\x61", "\116\x61\x6d\145\111\x44\106\157\x72\x6d\x61\164", $pL));
            VR:
        }
        Vd:
        return $Kx;
    }
    private function createSSOUrls()
    {
        $Oi = array();
        foreach ($this->singleSignOnServiceURLs as $YR => $L3) {
            $VY = $this->xml->createElementNS("\x75\x72\x6e\x3a\157\x61\x73\151\163\x3a\x6e\141\x6d\x65\163\x3a\164\143\72\x53\101\x4d\x4c\72\62\x2e\x30\x3a\155\x65\164\141\x64\x61\x74\x61", "\123\x69\156\147\x6c\x65\123\x69\147\156\117\x6e\x53\145\x72\166\x69\143\x65");
            $VY->setAttribute("\x42\151\x6e\x64\151\x6e\147", $YR);
            $VY->setAttribute("\114\x6f\x63\141\164\x69\157\156", $L3);
            array_push($Oi, $VY);
            Ll:
        }
        q2:
        return $Oi;
    }
    private function createSLOUrls()
    {
        $Su = array();
        foreach ($this->singleLogoutServiceURLs as $YR => $L3) {
            $kC = $this->xml->createElementNS("\x75\x72\156\x3a\x6f\141\x73\x69\163\x3a\x6e\141\x6d\145\x73\x3a\x74\143\x3a\123\x41\115\114\x3a\x32\x2e\60\x3a\155\145\164\141\144\x61\164\141", "\123\151\156\147\x6c\x65\x4c\157\147\x6f\165\x74\x53\145\x72\166\151\143\145");
            $kC->setAttribute("\102\x69\156\x64\x69\x6e\x67", $YR);
            $kC->setAttribute("\114\157\143\141\164\x69\157\x6e", $L3);
            array_push($Su, $kC);
            VQ:
        }
        HS:
        return $Su;
    }
    private function createRoleDescriptorElement()
    {
        $QM = $this->xml->createElement("\x52\x6f\x6c\x65\x44\145\x73\143\162\151\x70\164\157\x72");
        $QM->setAttributeNS("\x68\164\164\x70\x3a\57\57\167\x77\167\56\167\63\56\157\x72\x67\x2f\x32\x30\60\60\x2f\x78\x6d\154\156\163\57", "\170\x6d\154\x6e\163\x3a\x78\x73\x69", "\x68\x74\x74\x70\x3a\57\x2f\167\167\x77\56\x77\x33\56\x6f\x72\x67\57\62\x30\x30\x31\x2f\130\x4d\114\123\143\x68\145\155\x61\x2d\151\x6e\163\x74\x61\156\x63\145");
        $QM->setAttributeNS("\x68\164\164\x70\x3a\57\57\167\167\167\56\x77\63\56\x6f\x72\x67\x2f\x32\x30\60\x30\x2f\x78\x6d\154\156\x73\57", "\170\x6d\154\x6e\163\x3a\x66\145\x64", "\150\x74\164\160\x3a\57\57\x64\157\143\163\x2e\x6f\x61\x73\x69\x73\55\x6f\x70\145\x6e\56\x6f\162\147\x2f\x77\x73\x66\145\144\57\146\145\x64\145\x72\x61\164\x69\157\x6e\x2f\62\x30\x30\67\x30\x36");
        $QM->setAttribute("\x53\x65\162\166\151\143\x65\104\x69\163\160\154\x61\x79\x4e\141\x6d\145", "\x6d\x69\156\151\x4f\162\x61\156\x67\145\40\111\156\143");
        $QM->setAttribute("\x78\x73\x69\x3a\x74\171\x70\145", "\x66\145\x64\x3a\x53\x65\143\x75\162\x69\164\171\124\x6f\153\x65\x6e\123\145\162\166\x69\143\x65\x54\x79\160\145");
        $QM->setAttribute("\x70\x72\x6f\x74\157\143\x6f\x6c\x53\x75\160\x70\x6f\162\164\x45\156\165\x6d\145\x72\141\164\151\x6f\x6e", "\150\x74\x74\x70\x3a\57\x2f\x64\x6f\143\x73\56\x6f\x61\x73\x69\x73\55\157\160\x65\156\56\x6f\162\147\x2f\x77\x73\55\x73\170\57\167\163\55\164\x72\x75\x73\x74\x2f\62\x30\60\x35\61\x32\x20\150\x74\x74\160\72\x2f\57\x73\143\150\145\155\141\x73\56\170\x6d\154\x73\x6f\x61\x70\56\157\162\147\x2f\x77\163\57\x32\x30\x30\x35\x2f\60\62\57\164\162\x75\x73\164\40\150\164\164\x70\x3a\57\x2f\144\157\x63\x73\x2e\x6f\x61\163\x69\163\55\x6f\x70\x65\156\56\157\x72\x67\x2f\167\163\146\x65\x64\x2f\x66\x65\144\145\162\141\x74\151\x6f\x6e\x2f\62\60\x30\x37\x30\x36");
        return $QM;
    }
    private function createTokenTypesElement()
    {
        $Zu = $this->xml->createElement("\x66\x65\144\72\124\x6f\153\145\156\x54\x79\x70\x65\163\x4f\x66\146\x65\x72\x65\144");
        $LZ = $this->xml->createElement("\x66\145\x64\x3a\x54\x6f\x6b\x65\x6e\x54\171\x70\145");
        $LZ->setAttribute("\125\162\x69", "\x75\x72\x6e\x3a\157\141\163\x69\163\72\x6e\x61\155\x65\163\72\x74\143\x3a\123\101\115\114\x3a\61\56\x30\x3a\x61\x73\163\145\x72\x74\151\157\x6e");
        $Zu->appendChild($LZ);
        return $Zu;
    }
    private function createPassiveRequestEndpoints()
    {
        $fl = $this->xml->createElement("\x66\x65\x64\x3a\120\x61\x73\x73\151\166\x65\122\145\161\165\x65\163\x74\x6f\162\x45\x6e\144\x70\157\151\156\164");
        $Wj = $this->xml->createElementNS("\x68\164\x74\x70\72\57\x2f\167\x77\x77\56\x77\63\56\157\x72\147\57\62\60\60\x35\x2f\x30\x38\x2f\x61\144\x64\x72\145\x73\x73\x69\156\147", "\x61\x64\x3a\105\156\x64\160\157\151\156\x74\x52\145\146\x65\162\x65\x6e\x63\145");
        $Wj->appendChild($this->xml->createElement("\x41\144\x64\x72\145\163\163", $this->singleSignOnServiceURLs["\165\162\156\x3a\157\141\x73\151\x73\x3a\x6e\x61\155\x65\x73\72\164\143\72\123\101\x4d\x4c\72\62\x2e\x30\x3a\142\151\156\x64\x69\x6e\147\x73\x3a\x48\124\124\120\x2d\x50\x4f\123\124"]));
        $fl->appendChild($Wj);
        return $fl;
    }
    private function createOrganizationElement()
    {
        $AP = $this->xml->createElementNS("\x75\x72\156\72\157\x61\x73\151\x73\72\156\x61\x6d\x65\163\72\x74\143\x3a\x53\101\x4d\114\72\x32\56\x30\x3a\155\145\x74\141\144\x61\x74\141", "\x6d\144\x3a\x4f\x72\x67\141\156\x69\172\141\x74\x69\x6f\156");
        $Pk = $this->xml->createElementNS("\x75\162\156\x3a\x6f\x61\x73\151\x73\72\156\141\155\145\163\x3a\x74\143\x3a\123\101\115\x4c\72\x32\x2e\x30\x3a\155\145\x74\x61\x64\141\164\x61", "\155\x64\x3a\x4f\x72\147\x61\x6e\151\172\141\164\151\x6f\156\116\x61\x6d\x65", "\x6d\x69\156\151\117\162\x61\x6e\x67\145");
        $Pk->setAttribute("\170\155\x6c\72\x6c\x61\156\147", "\x65\x6e\55\x55\123");
        $Nk = $this->xml->createElementNS("\165\x72\156\72\157\x61\x73\151\163\72\156\141\x6d\145\x73\x3a\164\x63\x3a\123\101\x4d\x4c\x3a\x32\x2e\x30\x3a\x6d\x65\x74\141\144\x61\x74\x61", "\155\x64\x3a\117\162\x67\x61\x6e\x69\x7a\141\164\x69\157\x6e\104\x69\x73\160\x6c\141\x79\116\141\155\x65", "\x6d\x69\156\x69\117\x72\x61\x6e\x67\x65");
        $Nk->setAttribute("\170\155\154\72\x6c\x61\156\147", "\145\x6e\x2d\x55\123");
        $RB = $this->xml->createElementNS("\165\162\156\x3a\x6f\141\x73\151\x73\72\x6e\x61\155\x65\x73\72\x74\143\72\123\101\115\114\72\x32\56\x30\72\155\145\164\141\x64\141\x74\141", "\x6d\144\72\117\162\147\x61\156\x69\172\141\164\x69\x6f\x6e\x55\x52\114", "\150\164\x74\160\x73\x3a\x2f\57\x6d\x69\x6e\151\x6f\162\x61\x6e\147\x65\x2e\x63\x6f\x6d");
        $RB->setAttribute("\x78\x6d\x6c\x3a\x6c\x61\x6e\147", "\x65\x6e\x2d\125\123");
        $AP->appendChild($Pk);
        $AP->appendChild($Nk);
        $AP->appendChild($RB);
        return $AP;
    }
    private function createContactPersonElement()
    {
        $ry = $this->xml->createElementNS("\165\x72\x6e\72\157\141\x73\151\x73\x3a\x6e\x61\155\x65\163\72\x74\x63\x3a\123\101\x4d\114\72\x32\56\60\x3a\x6d\x65\164\x61\144\141\164\x61", "\155\x64\72\x43\157\156\x74\x61\143\x74\x50\x65\162\x73\157\x6e");
        $ry->setAttribute("\143\x6f\156\x74\141\143\x74\124\x79\x70\145", "\x74\x65\143\150\x6e\151\143\141\154");
        $Pk = $this->xml->createElementNS("\x75\162\x6e\72\x6f\x61\x73\151\x73\72\x6e\141\x6d\145\163\x3a\x74\x63\x3a\123\101\x4d\x4c\72\62\56\60\x3a\x6d\x65\x74\x61\x64\141\164\x61", "\155\144\72\107\151\x76\145\x6e\x4e\141\155\x65", "\x6d\151\156\151\x4f\x72\x61\156\x67\145");
        $xt = $this->xml->createElementNS("\x75\162\x6e\x3a\x6f\141\x73\x69\163\72\x6e\141\155\145\x73\72\164\143\x3a\123\x41\x4d\x4c\72\62\x2e\60\72\155\x65\164\x61\x64\x61\164\141", "\155\x64\72\x53\x75\162\116\x61\155\145", "\x53\x75\x70\160\x6f\162\x74");
        $QV = $this->xml->createElementNS("\x75\162\156\x3a\x6f\141\163\x69\x73\72\156\141\155\x65\163\72\164\143\x3a\x53\x41\x4d\114\72\x32\x2e\60\72\155\145\x74\x61\144\141\x74\x61", "\155\x64\72\x45\155\x61\151\154\101\x64\144\162\145\163\x73", "\151\x6e\146\x6f\x40\170\x65\143\165\162\x69\x66\x79\56\x63\x6f\x6d");
        $ry->appendChild($Pk);
        $ry->appendChild($xt);
        $ry->appendChild($QV);
        return $ry;
    }
}
