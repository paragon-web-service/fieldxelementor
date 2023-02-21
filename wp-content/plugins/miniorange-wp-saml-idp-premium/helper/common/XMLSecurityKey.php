<?php


namespace RobRichards\XMLSecLibs;

use DOMElement;
use Exception;
class XMLSecurityKey
{
    const TRIPLEDES_CBC = "\150\x74\x74\x70\72\57\x2f\167\167\167\56\167\63\56\157\x72\147\57\x32\60\x30\61\x2f\60\x34\57\x78\x6d\154\x65\156\x63\43\164\x72\x69\x70\154\x65\144\x65\163\55\143\142\x63";
    const AES128_CBC = "\x68\164\164\x70\72\x2f\57\x77\167\x77\56\x77\x33\56\157\x72\x67\x2f\62\60\x30\61\57\x30\x34\57\x78\x6d\x6c\145\x6e\143\43\x61\145\x73\x31\x32\x38\55\143\x62\x63";
    const AES192_CBC = "\x68\164\164\x70\x3a\x2f\x2f\x77\x77\167\x2e\167\63\56\x6f\x72\147\x2f\62\60\x30\61\57\60\x34\x2f\x78\x6d\154\145\156\x63\43\141\145\163\61\71\62\55\x63\142\143";
    const AES256_CBC = "\150\x74\x74\160\72\57\x2f\x77\167\167\56\167\x33\56\157\x72\x67\57\x32\60\60\x31\x2f\x30\x34\57\170\x6d\154\x65\156\143\43\x61\x65\x73\x32\65\x36\55\x63\x62\x63";
    const AES128_GCM = "\x68\x74\x74\x70\72\x2f\x2f\x77\167\167\x2e\x77\x33\56\x6f\x72\147\x2f\x32\60\60\71\x2f\x78\155\x6c\x65\156\143\x31\x31\x23\141\145\x73\61\62\70\x2d\x67\x63\x6d";
    const AES192_GCM = "\x68\164\x74\x70\x3a\x2f\57\x77\x77\x77\56\x77\x33\x2e\157\162\x67\x2f\x32\x30\x30\x39\57\170\155\154\145\156\143\x31\x31\x23\141\145\163\61\x39\x32\x2d\147\x63\x6d";
    const AES256_GCM = "\150\x74\x74\x70\72\x2f\57\x77\x77\x77\56\167\x33\x2e\157\x72\147\57\x32\60\60\71\x2f\170\155\x6c\x65\x6e\143\x31\x31\43\x61\x65\x73\62\x35\66\55\147\143\x6d";
    const RSA_1_5 = "\150\164\164\x70\72\57\57\x77\167\x77\56\167\x33\56\157\x72\147\57\62\60\x30\61\57\60\64\57\x78\x6d\154\145\x6e\x63\43\x72\x73\x61\55\x31\137\x35";
    const RSA_OAEP_MGF1P = "\150\x74\164\x70\72\x2f\57\167\x77\167\x2e\167\63\56\x6f\x72\147\57\62\x30\60\61\57\x30\64\x2f\x78\x6d\x6c\145\156\x63\43\162\x73\141\x2d\157\141\145\x70\x2d\155\x67\x66\61\160";
    const RSA_OAEP = "\150\x74\164\160\x3a\x2f\x2f\x77\x77\167\56\x77\x33\56\157\162\147\57\x32\x30\x30\x39\57\170\155\154\145\x6e\143\x31\61\x23\162\163\141\55\157\141\145\x70";
    const DSA_SHA1 = "\x68\164\x74\x70\72\57\57\167\x77\x77\x2e\167\63\56\x6f\162\147\x2f\62\60\60\x30\57\60\x39\57\170\155\154\x64\163\151\147\x23\144\163\141\x2d\163\x68\141\61";
    const RSA_SHA1 = "\150\164\164\x70\72\57\57\x77\167\x77\x2e\167\63\56\157\162\x67\x2f\x32\60\60\x30\57\60\71\57\170\x6d\x6c\144\x73\x69\x67\43\162\163\141\x2d\163\x68\x61\61";
    const RSA_SHA256 = "\x68\x74\164\160\x3a\57\57\x77\x77\x77\x2e\167\x33\x2e\157\162\x67\57\x32\x30\x30\61\57\60\64\57\x78\x6d\x6c\x64\163\151\x67\55\155\157\x72\145\43\162\x73\141\55\163\x68\141\62\65\x36";
    const RSA_SHA384 = "\x68\x74\164\160\72\x2f\57\x77\x77\167\56\167\63\x2e\x6f\162\x67\57\62\x30\60\x31\x2f\60\64\57\x78\x6d\154\x64\163\151\147\x2d\155\157\x72\145\43\x72\x73\141\x2d\x73\x68\141\x33\70\64";
    const RSA_SHA512 = "\150\164\x74\x70\72\57\57\x77\167\167\56\167\x33\x2e\x6f\x72\147\57\x32\60\60\61\x2f\60\x34\57\x78\x6d\x6c\x64\x73\x69\147\x2d\155\157\162\145\x23\162\163\x61\x2d\163\150\x61\x35\61\x32";
    const HMAC_SHA1 = "\150\164\x74\x70\72\x2f\57\x77\167\167\56\167\x33\56\157\162\x67\57\x32\x30\x30\60\x2f\x30\71\x2f\x78\x6d\154\144\x73\x69\x67\43\150\x6d\141\x63\x2d\x73\150\x61\x31";
    const AUTHTAG_LENGTH = 16;
    private $cryptParams = array();
    public $type = 0;
    public $key = null;
    public $passphrase = '';
    public $iv = null;
    public $name = null;
    public $keyChain = null;
    public $isEncrypted = false;
    public $encryptedCtx = null;
    public $guid = null;
    private $x509Certificate = null;
    private $X509Thumbprint = null;
    public function __construct($ZV, $Ly = null)
    {
        switch ($ZV) {
            case self::TRIPLEDES_CBC:
                $this->cryptParams["\154\151\x62\162\141\162\x79"] = "\157\160\x65\156\163\163\154";
                $this->cryptParams["\143\151\160\x68\145\162"] = "\144\x65\x73\x2d\145\144\145\63\55\x63\142\x63";
                $this->cryptParams["\x74\x79\x70\x65"] = "\x73\171\155\x6d\145\164\162\151\143";
                $this->cryptParams["\155\145\164\x68\157\144"] = "\150\164\x74\x70\72\57\x2f\x77\x77\167\56\167\x33\x2e\x6f\162\x67\57\x32\60\x30\61\57\60\64\x2f\x78\x6d\154\x65\x6e\x63\x23\x74\x72\x69\x70\154\145\x64\145\163\x2d\143\142\143";
                $this->cryptParams["\153\x65\x79\x73\x69\172\x65"] = 24;
                $this->cryptParams["\x62\154\157\143\x6b\163\151\172\145"] = 8;
                goto Qd;
            case self::AES128_CBC:
                $this->cryptParams["\x6c\151\142\162\x61\162\x79"] = "\157\160\x65\156\x73\x73\154";
                $this->cryptParams["\x63\x69\x70\x68\x65\x72"] = "\141\145\x73\x2d\61\x32\x38\x2d\x63\x62\x63";
                $this->cryptParams["\164\171\x70\145"] = "\x73\x79\155\x6d\145\x74\x72\x69\143";
                $this->cryptParams["\x6d\145\x74\x68\157\x64"] = "\150\x74\x74\x70\72\57\57\167\167\x77\x2e\167\63\x2e\x6f\162\x67\x2f\x32\x30\60\x31\x2f\60\64\57\170\x6d\154\145\x6e\x63\x23\x61\x65\x73\61\62\70\x2d\x63\x62\x63";
                $this->cryptParams["\153\145\171\x73\151\x7a\145"] = 16;
                $this->cryptParams["\x62\x6c\157\x63\153\x73\151\x7a\x65"] = 16;
                goto Qd;
            case self::AES192_CBC:
                $this->cryptParams["\154\151\142\x72\x61\162\x79"] = "\157\x70\145\156\163\163\x6c";
                $this->cryptParams["\143\x69\x70\150\x65\x72"] = "\x61\145\x73\55\x31\x39\62\x2d\143\x62\x63";
                $this->cryptParams["\164\x79\x70\x65"] = "\x73\x79\155\x6d\145\x74\162\151\x63";
                $this->cryptParams["\x6d\x65\x74\x68\x6f\144"] = "\150\x74\x74\160\x3a\57\57\167\167\x77\x2e\x77\x33\x2e\x6f\x72\x67\57\x32\x30\60\61\57\60\64\x2f\170\x6d\154\145\156\143\43\141\145\x73\x31\71\62\55\143\x62\x63";
                $this->cryptParams["\153\145\171\163\x69\172\x65"] = 24;
                $this->cryptParams["\142\x6c\157\x63\153\x73\x69\172\x65"] = 16;
                goto Qd;
            case self::AES256_CBC:
                $this->cryptParams["\154\x69\x62\162\x61\162\x79"] = "\157\x70\x65\x6e\x73\x73\x6c";
                $this->cryptParams["\x63\x69\160\150\x65\x72"] = "\x61\x65\x73\55\x32\x35\66\x2d\x63\x62\143";
                $this->cryptParams["\164\x79\160\145"] = "\163\171\155\155\145\x74\162\151\143";
                $this->cryptParams["\x6d\145\164\x68\x6f\144"] = "\150\164\x74\160\72\x2f\57\x77\x77\167\56\x77\63\56\157\162\x67\x2f\62\60\x30\61\57\x30\64\x2f\x78\155\154\145\x6e\143\x23\x61\x65\163\62\x35\x36\55\x63\142\143";
                $this->cryptParams["\153\x65\171\x73\151\x7a\145"] = 32;
                $this->cryptParams["\142\x6c\x6f\143\x6b\163\x69\x7a\145"] = 16;
                goto Qd;
            case self::AES128_GCM:
                $this->cryptParams["\x6c\x69\x62\162\141\x72\x79"] = "\157\x70\x65\156\163\x73\154";
                $this->cryptParams["\143\x69\x70\150\x65\x72"] = "\x61\145\163\55\61\62\70\55\147\x63\x6d";
                $this->cryptParams["\x74\x79\160\x65"] = "\163\171\x6d\x6d\x65\164\162\x69\x63";
                $this->cryptParams["\155\145\164\150\157\144"] = "\150\164\164\160\72\57\x2f\167\x77\167\56\x77\x33\x2e\157\x72\x67\x2f\62\60\60\71\57\170\x6d\x6c\145\156\143\x31\61\43\x61\x65\163\x31\62\x38\x2d\147\143\x6d";
                $this->cryptParams["\x6b\x65\x79\x73\151\172\145"] = 16;
                $this->cryptParams["\x62\x6c\x6f\143\x6b\x73\x69\172\x65"] = 16;
                goto Qd;
            case self::AES192_GCM:
                $this->cryptParams["\x6c\x69\x62\162\141\162\x79"] = "\x6f\160\145\156\163\x73\154";
                $this->cryptParams["\x63\151\x70\x68\145\x72"] = "\141\x65\163\x2d\61\71\x32\55\147\143\155";
                $this->cryptParams["\164\x79\160\x65"] = "\163\x79\x6d\155\145\164\162\x69\143";
                $this->cryptParams["\155\x65\x74\150\x6f\x64"] = "\x68\164\x74\x70\72\57\57\167\x77\167\x2e\167\x33\x2e\157\162\147\x2f\62\60\60\71\x2f\x78\x6d\x6c\x65\156\x63\x31\61\43\x61\145\x73\x31\71\62\55\x67\x63\x6d";
                $this->cryptParams["\153\x65\x79\163\151\x7a\145"] = 24;
                $this->cryptParams["\x62\154\157\x63\x6b\163\151\172\x65"] = 16;
                goto Qd;
            case self::AES256_GCM:
                $this->cryptParams["\x6c\x69\142\x72\141\x72\x79"] = "\157\x70\145\x6e\163\163\x6c";
                $this->cryptParams["\x63\151\x70\x68\x65\x72"] = "\x61\x65\163\55\62\65\66\x2d\x67\x63\155";
                $this->cryptParams["\x74\x79\x70\x65"] = "\x73\x79\x6d\155\145\x74\x72\151\143";
                $this->cryptParams["\155\145\x74\x68\157\144"] = "\150\x74\x74\x70\x3a\x2f\57\167\167\167\x2e\x77\x33\x2e\x6f\x72\x67\x2f\62\x30\x30\71\x2f\170\155\154\145\x6e\143\x31\61\x23\x61\x65\x73\62\65\66\x2d\147\x63\155";
                $this->cryptParams["\x6b\x65\171\163\x69\x7a\145"] = 32;
                $this->cryptParams["\142\x6c\157\143\153\x73\x69\172\145"] = 16;
                goto Qd;
            case self::RSA_1_5:
                $this->cryptParams["\x6c\151\x62\162\x61\162\x79"] = "\x6f\x70\x65\156\163\x73\x6c";
                $this->cryptParams["\160\141\144\144\x69\x6e\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\155\x65\x74\x68\157\x64"] = "\x68\164\x74\x70\x3a\x2f\57\x77\x77\167\56\167\63\x2e\x6f\162\147\57\x32\x30\x30\x31\x2f\x30\64\x2f\x78\x6d\154\145\156\x63\x23\x72\x73\x61\x2d\61\x5f\x35";
                if (!(is_array($Ly) && !empty($Ly["\x74\171\160\145"]))) {
                    goto Oi;
                }
                if (!($Ly["\164\171\x70\145"] == "\x70\165\x62\x6c\x69\x63" || $Ly["\x74\x79\x70\145"] == "\160\162\151\166\141\x74\145")) {
                    goto eQ;
                }
                $this->cryptParams["\x74\x79\x70\145"] = $Ly["\164\x79\x70\145"];
                goto Qd;
                eQ:
                Oi:
                throw new Exception("\103\x65\x72\x74\x69\146\151\x63\x61\x74\145\40\42\164\171\x70\145\x22\x20\x28\160\x72\151\x76\x61\x74\x65\x2f\160\165\x62\x6c\151\143\51\40\x6d\x75\163\164\x20\142\x65\x20\160\141\x73\163\x65\144\40\166\151\141\40\160\141\162\x61\155\145\x74\145\x72\x73");
            case self::RSA_OAEP_MGF1P:
                $this->cryptParams["\154\151\x62\162\x61\162\171"] = "\157\160\145\x6e\x73\x73\154";
                $this->cryptParams["\x70\141\x64\144\151\x6e\147"] = OPENSSL_PKCS1_OAEP_PADDING;
                $this->cryptParams["\155\x65\x74\150\157\x64"] = "\150\164\x74\x70\72\x2f\x2f\167\x77\x77\56\x77\63\56\157\162\147\x2f\62\x30\60\x31\x2f\60\64\x2f\x78\x6d\154\145\x6e\x63\x23\x72\x73\x61\x2d\x6f\141\145\160\55\155\147\146\61\x70";
                $this->cryptParams["\x68\x61\163\150"] = null;
                if (!(is_array($Ly) && !empty($Ly["\164\x79\x70\145"]))) {
                    goto xi;
                }
                if (!($Ly["\x74\x79\x70\145"] == "\x70\165\142\154\151\x63" || $Ly["\164\171\160\145"] == "\160\162\151\166\141\x74\145")) {
                    goto EA;
                }
                $this->cryptParams["\164\x79\160\x65"] = $Ly["\x74\x79\x70\x65"];
                goto Qd;
                EA:
                xi:
                throw new Exception("\103\x65\162\164\x69\146\151\143\141\164\x65\40\42\x74\x79\x70\145\x22\40\50\x70\x72\151\x76\141\x74\145\x2f\x70\165\x62\x6c\151\x63\x29\40\x6d\x75\163\164\x20\142\x65\x20\160\141\x73\x73\x65\x64\x20\x76\151\141\x20\160\141\x72\141\155\x65\x74\x65\162\163");
            case self::RSA_OAEP:
                $this->cryptParams["\x6c\151\142\x72\141\x72\171"] = "\x6f\x70\145\x6e\x73\163\x6c";
                $this->cryptParams["\160\141\x64\x64\x69\156\147"] = OPENSSL_PKCS1_OAEP_PADDING;
                $this->cryptParams["\155\x65\x74\150\157\144"] = "\150\x74\164\160\72\57\x2f\167\x77\167\56\167\x33\x2e\x6f\162\147\x2f\62\60\60\x39\57\170\155\x6c\145\x6e\x63\61\61\43\x72\163\141\x2d\x6f\141\145\x70";
                $this->cryptParams["\150\x61\x73\x68"] = "\150\164\x74\x70\x3a\57\x2f\167\x77\167\56\x77\x33\56\x6f\x72\147\x2f\x32\x30\60\71\x2f\x78\x6d\x6c\x65\156\x63\61\x31\43\155\x67\146\x31\163\150\141\61";
                if (!(is_array($Ly) && !empty($Ly["\164\171\160\x65"]))) {
                    goto mL;
                }
                if (!($Ly["\x74\x79\x70\145"] == "\160\x75\142\x6c\151\x63" || $Ly["\164\171\160\x65"] == "\x70\162\x69\x76\x61\164\145")) {
                    goto eL;
                }
                $this->cryptParams["\164\171\160\145"] = $Ly["\164\x79\160\145"];
                goto Qd;
                eL:
                mL:
                throw new Exception("\x43\145\162\x74\151\146\x69\x63\141\x74\x65\x20\42\164\x79\x70\145\x22\x20\50\160\162\x69\x76\141\164\145\57\x70\x75\142\154\151\143\x29\40\155\165\163\164\x20\x62\x65\x20\x70\141\163\163\145\144\x20\x76\151\141\x20\160\141\x72\x61\x6d\x65\x74\145\162\163");
            case self::RSA_SHA1:
                $this->cryptParams["\154\x69\x62\162\141\x72\x79"] = "\x6f\160\145\156\163\163\154";
                $this->cryptParams["\x6d\x65\164\150\157\x64"] = "\x68\x74\164\160\72\x2f\57\x77\167\x77\x2e\x77\63\x2e\157\162\147\57\62\60\x30\x30\57\60\x39\57\170\x6d\154\x64\x73\x69\x67\43\x72\x73\141\x2d\x73\x68\x61\61";
                $this->cryptParams["\160\x61\144\x64\151\156\x67"] = OPENSSL_PKCS1_PADDING;
                if (!(is_array($Ly) && !empty($Ly["\x74\x79\160\x65"]))) {
                    goto x0;
                }
                if (!($Ly["\x74\171\x70\x65"] == "\160\x75\x62\154\151\143" || $Ly["\164\171\x70\x65"] == "\x70\162\151\166\x61\x74\145")) {
                    goto d5;
                }
                $this->cryptParams["\164\x79\x70\145"] = $Ly["\x74\x79\160\x65"];
                goto Qd;
                d5:
                x0:
                throw new Exception("\x43\x65\162\164\x69\146\151\143\141\164\x65\x20\x22\x74\171\160\x65\x22\40\50\x70\162\x69\166\x61\164\x65\57\160\165\142\154\x69\x63\51\40\x6d\x75\x73\x74\x20\142\145\40\160\x61\163\163\x65\144\40\166\x69\x61\x20\x70\141\162\x61\155\x65\164\145\162\x73");
            case self::RSA_SHA256:
                $this->cryptParams["\x6c\x69\x62\162\141\x72\171"] = "\x6f\160\x65\156\163\x73\x6c";
                $this->cryptParams["\155\x65\164\150\157\144"] = "\150\164\x74\x70\x3a\57\x2f\x77\167\167\x2e\x77\x33\56\157\162\x67\x2f\62\60\60\x31\x2f\60\64\57\170\155\154\x64\163\x69\x67\55\x6d\x6f\x72\x65\43\x72\163\x61\55\163\150\141\62\x35\66";
                $this->cryptParams["\160\x61\x64\x64\x69\x6e\x67"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\144\151\147\145\163\x74"] = "\x53\110\101\62\65\66";
                if (!(is_array($Ly) && !empty($Ly["\164\x79\160\x65"]))) {
                    goto gK;
                }
                if (!($Ly["\164\171\160\145"] == "\160\x75\x62\x6c\x69\x63" || $Ly["\x74\171\x70\x65"] == "\160\162\x69\166\141\164\x65")) {
                    goto TP;
                }
                $this->cryptParams["\x74\x79\160\145"] = $Ly["\x74\171\x70\145"];
                goto Qd;
                TP:
                gK:
                throw new Exception("\x43\145\162\x74\151\146\x69\143\141\164\145\x20\42\x74\x79\x70\x65\x22\x20\x28\160\x72\x69\x76\x61\x74\x65\57\160\x75\x62\x6c\151\x63\x29\40\155\x75\163\x74\40\x62\x65\40\x70\x61\163\163\x65\x64\40\166\151\x61\x20\x70\141\162\x61\x6d\145\x74\x65\x72\x73");
            case self::RSA_SHA384:
                $this->cryptParams["\154\x69\x62\162\x61\x72\171"] = "\157\160\145\156\163\x73\154";
                $this->cryptParams["\155\145\164\x68\x6f\144"] = "\150\x74\x74\160\x3a\57\57\x77\x77\x77\56\167\63\x2e\157\x72\147\x2f\x32\60\x30\61\x2f\60\x34\57\170\x6d\154\144\163\151\147\x2d\x6d\157\x72\x65\43\162\x73\141\x2d\163\x68\141\x33\x38\x34";
                $this->cryptParams["\160\141\x64\x64\151\156\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x64\151\x67\145\163\164"] = "\123\110\x41\63\x38\64";
                if (!(is_array($Ly) && !empty($Ly["\x74\x79\160\x65"]))) {
                    goto XA;
                }
                if (!($Ly["\164\x79\160\145"] == "\160\x75\142\154\151\x63" || $Ly["\164\x79\x70\x65"] == "\160\162\x69\166\x61\164\145")) {
                    goto q7;
                }
                $this->cryptParams["\x74\171\x70\x65"] = $Ly["\164\x79\160\x65"];
                goto Qd;
                q7:
                XA:
                throw new Exception("\x43\145\162\164\151\146\x69\x63\141\x74\145\x20\x22\x74\x79\160\145\x22\x20\50\x70\162\x69\x76\141\x74\145\57\160\165\x62\154\x69\143\x29\40\x6d\x75\x73\x74\40\142\145\x20\x70\141\163\163\145\x64\40\x76\x69\141\x20\160\x61\162\141\x6d\145\164\145\x72\x73");
            case self::RSA_SHA512:
                $this->cryptParams["\x6c\151\x62\162\141\x72\x79"] = "\157\x70\x65\x6e\163\163\154";
                $this->cryptParams["\155\145\x74\x68\x6f\144"] = "\150\x74\164\160\x3a\57\57\x77\167\167\x2e\167\x33\x2e\157\162\x67\57\62\60\60\x31\x2f\x30\x34\x2f\x78\x6d\154\144\x73\151\x67\x2d\155\x6f\162\145\x23\x72\x73\141\x2d\163\150\141\65\61\x32";
                $this->cryptParams["\x70\141\x64\x64\151\x6e\147"] = OPENSSL_PKCS1_PADDING;
                $this->cryptParams["\x64\151\147\x65\163\x74"] = "\123\x48\101\65\x31\x32";
                if (!(is_array($Ly) && !empty($Ly["\x74\x79\160\x65"]))) {
                    goto vj;
                }
                if (!($Ly["\x74\x79\x70\x65"] == "\x70\x75\142\x6c\x69\143" || $Ly["\x74\x79\x70\145"] == "\x70\162\151\166\141\x74\x65")) {
                    goto oF;
                }
                $this->cryptParams["\164\x79\x70\145"] = $Ly["\x74\171\x70\145"];
                goto Qd;
                oF:
                vj:
                throw new Exception("\103\x65\162\164\x69\146\151\x63\141\164\x65\40\x22\164\x79\160\x65\x22\x20\x28\160\162\151\x76\x61\164\145\x2f\160\165\x62\154\151\x63\x29\x20\155\165\163\x74\x20\x62\x65\40\x70\x61\x73\x73\145\x64\x20\166\x69\141\40\160\x61\162\141\x6d\x65\164\145\162\163");
            case self::HMAC_SHA1:
                $this->cryptParams["\x6c\151\142\x72\141\162\x79"] = $ZV;
                $this->cryptParams["\x6d\x65\x74\x68\x6f\x64"] = "\x68\x74\164\x70\72\57\57\167\x77\167\x2e\167\63\56\x6f\162\x67\57\62\60\60\x30\x2f\60\x39\57\x78\x6d\154\144\x73\x69\147\43\x68\x6d\141\x63\55\163\150\141\61";
                goto Qd;
            default:
                throw new Exception("\x49\x6e\166\x61\x6c\151\144\x20\x4b\145\171\40\x54\171\x70\x65");
        }
        tX:
        Qd:
        $this->type = $ZV;
    }
    public function getSymmetricKeySize()
    {
        if (isset($this->cryptParams["\x6b\x65\x79\x73\151\172\145"])) {
            goto au;
        }
        return null;
        au:
        return $this->cryptParams["\x6b\x65\171\163\151\172\x65"];
    }
    public function generateSessionKey()
    {
        if (isset($this->cryptParams["\153\x65\171\x73\x69\172\x65"])) {
            goto Mp;
        }
        throw new Exception("\125\x6e\153\x6e\157\x77\156\x20\x6b\145\x79\x20\x73\x69\x7a\x65\x20\146\x6f\x72\x20\164\x79\x70\145\40\42" . $this->type . "\42\56");
        Mp:
        $X6 = $this->cryptParams["\153\x65\x79\x73\151\172\145"];
        $xO = openssl_random_pseudo_bytes($X6);
        if (!($this->type === self::TRIPLEDES_CBC)) {
            goto VZ;
        }
        $rM = 0;
        iZ:
        if (!($rM < strlen($xO))) {
            goto At;
        }
        $wD = ord($xO[$rM]) & 0xfe;
        $Gj = 1;
        $cu = 1;
        id:
        if (!($cu < 8)) {
            goto QF;
        }
        $Gj ^= $wD >> $cu & 1;
        Y1:
        $cu++;
        goto id;
        QF:
        $wD |= $Gj;
        $xO[$rM] = chr($wD);
        yH:
        $rM++;
        goto iZ;
        At:
        VZ:
        $this->key = $xO;
        return $xO;
    }
    public static function getRawThumbprint($Ix)
    {
        $kF = explode("\xa", $Ix);
        $fL = '';
        $Rz = false;
        foreach ($kF as $sd) {
            if (!$Rz) {
                goto Uk;
            }
            if (!(strncmp($sd, "\55\x2d\55\x2d\55\105\x4e\104\x20\x43\x45\x52\124\x49\x46\x49\x43\101\x54\105", 20) == 0)) {
                goto TM;
            }
            goto a0;
            TM:
            $fL .= trim($sd);
            goto Ws;
            Uk:
            if (!(strncmp($sd, "\x2d\x2d\x2d\x2d\x2d\102\105\107\111\116\x20\103\105\122\124\111\x46\x49\x43\x41\x54\x45", 22) == 0)) {
                goto ZS;
            }
            $Rz = true;
            ZS:
            Ws:
            DE:
        }
        a0:
        if (empty($fL)) {
            goto FF;
        }
        return strtolower(sha1(base64_decode($fL)));
        FF:
        return null;
    }
    public function loadKey($xO, $iC = false, $BE = false)
    {
        if ($iC) {
            goto cT;
        }
        $this->key = $xO;
        goto Wo;
        cT:
        $this->key = file_get_contents($xO);
        Wo:
        if ($BE) {
            goto oG;
        }
        $this->x509Certificate = null;
        goto t6;
        oG:
        $this->key = openssl_x509_read($this->key);
        openssl_x509_export($this->key, $Vn);
        $this->x509Certificate = $Vn;
        $this->key = $Vn;
        t6:
        if (!($this->cryptParams["\x6c\151\x62\162\x61\162\171"] == "\157\160\145\x6e\163\x73\x6c")) {
            goto U0;
        }
        switch ($this->cryptParams["\x74\x79\x70\145"]) {
            case "\160\x75\x62\154\x69\143":
                if (!$BE) {
                    goto Ly;
                }
                $this->X509Thumbprint = self::getRawThumbprint($this->key);
                Ly:
                $this->key = openssl_get_publickey($this->key);
                if ($this->key) {
                    goto DS;
                }
                throw new Exception("\x55\x6e\x61\x62\x6c\145\x20\x74\157\40\145\170\164\162\141\x63\164\40\160\x75\142\154\x69\143\40\153\145\171");
                DS:
                goto Dk;
            case "\x70\162\151\166\141\164\x65":
                $this->key = openssl_get_privatekey($this->key, $this->passphrase);
                goto Dk;
            case "\x73\171\x6d\155\x65\164\162\x69\x63":
                if (!(strlen($this->key) < $this->cryptParams["\x6b\145\171\x73\151\x7a\x65"])) {
                    goto g0;
                }
                throw new Exception("\x4b\x65\171\x20\x6d\x75\163\164\x20\143\157\156\164\x61\x69\x6e\x20\141\x74\40\154\x65\141\163\x74\x20" . $this->cryptParams["\153\145\x79\x73\151\172\145"] . "\40\x63\150\x61\162\141\143\164\x65\162\163\40\x66\157\162\40\164\x68\x69\x73\x20\143\151\x70\x68\145\x72\x2c\x20\x63\157\x6e\x74\x61\151\x6e\163\x20" . strlen($this->key));
                g0:
                goto Dk;
            default:
                throw new Exception("\125\x6e\153\156\157\167\x6e\x20\164\171\x70\x65");
        }
        Nv:
        Dk:
        U0:
    }
    private function padISO10126($fL, $Dg)
    {
        if (!($Dg > 256)) {
            goto ZW;
        }
        throw new Exception("\102\x6c\x6f\143\x6b\40\163\151\172\145\x20\x68\151\x67\x68\145\162\x20\x74\150\141\156\40\62\65\x36\x20\156\x6f\x74\40\141\154\154\x6f\167\x65\x64");
        ZW:
        $ht = $Dg - strlen($fL) % $Dg;
        $eK = chr($ht);
        return $fL . str_repeat($eK, $ht);
    }
    private function unpadISO10126($fL)
    {
        $ht = substr($fL, -1);
        $VB = ord($ht);
        return substr($fL, 0, -$VB);
    }
    private function encryptSymmetric($fL)
    {
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cryptParams["\143\151\x70\x68\x65\162"]));
        $qV = null;
        if (in_array($this->cryptParams["\143\x69\x70\x68\x65\x72"], ["\x61\145\163\x2d\61\x32\x38\x2d\x67\143\155", "\x61\145\163\55\61\71\62\55\147\x63\x6d", "\x61\145\163\55\x32\x35\x36\55\147\x63\155"])) {
            goto JI;
        }
        $fL = $this->padISO10126($fL, $this->cryptParams["\x62\154\x6f\x63\x6b\163\151\172\145"]);
        $QN = openssl_encrypt($fL, $this->cryptParams["\143\151\x70\x68\x65\162"], $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        goto R1;
        JI:
        if (!(version_compare(PHP_VERSION, "\67\x2e\x31\56\60") < 0)) {
            goto UP;
        }
        throw new Exception("\120\x48\120\40\x37\56\61\x2e\60\40\x69\163\40\162\x65\x71\x75\151\x72\145\144\x20\x74\157\40\x75\163\145\x20\101\105\x53\40\107\103\115\x20\141\154\x67\157\162\x69\x74\150\x6d\163");
        UP:
        $qV = openssl_random_pseudo_bytes(self::AUTHTAG_LENGTH);
        $QN = openssl_encrypt($fL, $this->cryptParams["\x63\151\x70\150\x65\x72"], $this->key, OPENSSL_RAW_DATA, $this->iv, $qV);
        R1:
        if (!(false === $QN)) {
            goto Sj;
        }
        throw new Exception("\106\x61\151\154\165\x72\x65\x20\145\156\143\x72\171\x70\164\151\156\147\x20\x44\141\x74\x61\40\50\x6f\160\x65\156\163\163\x6c\40\163\171\x6d\155\145\x74\x72\x69\x63\x29\x20\x2d\x20" . openssl_error_string());
        Sj:
        return $this->iv . $QN . $qV;
    }
    private function decryptSymmetric($fL)
    {
        $dC = openssl_cipher_iv_length($this->cryptParams["\143\151\x70\150\145\162"]);
        $this->iv = substr($fL, 0, $dC);
        $fL = substr($fL, $dC);
        $qV = null;
        if (in_array($this->cryptParams["\x63\151\x70\150\145\162"], ["\141\145\163\55\61\62\x38\55\147\143\155", "\141\145\163\x2d\61\x39\x32\55\147\143\155", "\141\145\163\55\62\65\x36\55\x67\x63\x6d"])) {
            goto H7;
        }
        $d0 = openssl_decrypt($fL, $this->cryptParams["\x63\x69\160\150\145\x72"], $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        goto n_;
        H7:
        if (!(version_compare(PHP_VERSION, "\x37\56\x31\56\60") < 0)) {
            goto q1;
        }
        throw new Exception("\x50\x48\x50\40\67\x2e\x31\x2e\x30\40\x69\x73\x20\162\x65\x71\165\x69\162\x65\x64\40\x74\x6f\x20\165\x73\145\40\x41\105\x53\x20\107\103\x4d\40\x61\154\147\157\162\x69\x74\150\155\x73");
        q1:
        $v7 = 0 - self::AUTHTAG_LENGTH;
        $qV = substr($fL, $v7);
        $fL = substr($fL, 0, $v7);
        $d0 = openssl_decrypt($fL, $this->cryptParams["\143\x69\x70\x68\145\x72"], $this->key, OPENSSL_RAW_DATA, $this->iv, $qV);
        n_:
        if (!(false === $d0)) {
            goto UX;
        }
        throw new Exception("\x46\x61\x69\x6c\165\x72\145\40\144\145\x63\x72\x79\x70\164\151\x6e\147\40\x44\x61\x74\141\x20\x28\157\x70\145\156\163\x73\154\x20\163\171\x6d\x6d\145\164\x72\151\143\51\x20\x2d\x20" . openssl_error_string());
        UX:
        return null !== $qV ? $d0 : $this->unpadISO10126($d0);
    }
    private function encryptPublic($fL)
    {
        if (openssl_public_encrypt($fL, $QN, $this->key, $this->cryptParams["\160\x61\x64\x64\x69\156\147"])) {
            goto Sm;
        }
        throw new Exception("\x46\141\151\154\x75\x72\x65\x20\145\156\x63\162\171\160\x74\151\156\x67\x20\x44\141\x74\x61\40\x28\x6f\160\145\x6e\163\x73\154\40\160\x75\142\x6c\151\x63\x29\40\x2d\x20" . openssl_error_string());
        Sm:
        return $QN;
    }
    private function decryptPublic($fL)
    {
        if (openssl_public_decrypt($fL, $d0, $this->key, $this->cryptParams["\x70\141\144\x64\151\156\x67"])) {
            goto xa;
        }
        throw new Exception("\106\141\x69\x6c\x75\x72\x65\x20\x64\145\x63\162\x79\160\164\151\156\x67\40\x44\141\x74\x61\x20\50\157\x70\x65\156\163\163\154\40\x70\x75\142\x6c\151\x63\x29\x20\x2d\x20" . openssl_error_string());
        xa:
        return $d0;
    }
    private function encryptPrivate($fL)
    {
        if (openssl_private_encrypt($fL, $QN, $this->key, $this->cryptParams["\x70\141\x64\144\151\156\x67"])) {
            goto e7;
        }
        throw new Exception("\x46\141\151\154\165\162\145\x20\145\x6e\x63\x72\x79\x70\x74\x69\156\x67\40\104\141\164\141\x20\x28\x6f\160\145\x6e\x73\x73\154\x20\160\x72\x69\166\x61\x74\x65\51\x20\x2d\40" . openssl_error_string());
        e7:
        return $QN;
    }
    private function decryptPrivate($fL)
    {
        if (openssl_private_decrypt($fL, $d0, $this->key, $this->cryptParams["\x70\x61\144\x64\151\x6e\x67"])) {
            goto P7;
        }
        throw new Exception("\106\x61\x69\154\x75\162\x65\x20\x64\145\143\162\x79\x70\164\x69\x6e\x67\40\x44\x61\x74\x61\40\50\157\160\x65\x6e\x73\163\x6c\x20\160\162\x69\166\x61\164\145\x29\40\x2d\40" . openssl_error_string());
        P7:
        return $d0;
    }
    private function signOpenSSL($fL)
    {
        $B5 = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\144\151\147\145\163\164"])) {
            goto tP;
        }
        $B5 = $this->cryptParams["\x64\151\x67\145\163\x74"];
        tP:
        if (openssl_sign($fL, $c2, $this->key, $B5)) {
            goto pk;
        }
        throw new Exception("\106\141\151\x6c\165\162\145\x20\123\151\x67\156\151\156\x67\x20\x44\x61\x74\x61\x3a\40" . openssl_error_string() . "\40\55\x20" . $B5);
        pk:
        return $c2;
    }
    private function verifyOpenSSL($fL, $c2)
    {
        $B5 = OPENSSL_ALGO_SHA1;
        if (empty($this->cryptParams["\x64\x69\x67\145\x73\x74"])) {
            goto L2;
        }
        $B5 = $this->cryptParams["\144\x69\147\145\163\164"];
        L2:
        return openssl_verify($fL, $c2, $this->key, $B5);
    }
    public function encryptData($fL)
    {
        if (!($this->cryptParams["\x6c\x69\x62\162\141\x72\171"] === "\x6f\160\x65\x6e\x73\163\x6c")) {
            goto ep;
        }
        switch ($this->cryptParams["\164\171\x70\145"]) {
            case "\163\171\155\155\x65\x74\x72\x69\143":
                return $this->encryptSymmetric($fL);
            case "\x70\x75\142\x6c\x69\x63":
                return $this->encryptPublic($fL);
            case "\160\162\151\166\141\164\x65":
                return $this->encryptPrivate($fL);
        }
        Qm:
        DV:
        ep:
    }
    public function decryptData($fL)
    {
        if (!($this->cryptParams["\x6c\x69\142\162\141\x72\171"] === "\157\x70\x65\156\x73\x73\x6c")) {
            goto RB;
        }
        switch ($this->cryptParams["\164\x79\x70\145"]) {
            case "\x73\x79\x6d\155\145\x74\162\151\143":
                return $this->decryptSymmetric($fL);
            case "\160\165\x62\154\151\143":
                return $this->decryptPublic($fL);
            case "\160\162\x69\x76\x61\164\x65":
                return $this->decryptPrivate($fL);
        }
        f7:
        sS:
        RB:
    }
    public function signData($fL)
    {
        switch ($this->cryptParams["\x6c\151\x62\x72\141\x72\x79"]) {
            case "\157\160\145\156\x73\163\x6c":
                return $this->signOpenSSL($fL);
            case self::HMAC_SHA1:
                return hash_hmac("\163\150\141\x31", $fL, $this->key, true);
        }
        YQ:
        S2:
    }
    public function verifySignature($fL, $c2)
    {
        switch ($this->cryptParams["\x6c\151\142\162\x61\x72\x79"]) {
            case "\157\160\x65\x6e\x73\163\154":
                return $this->verifyOpenSSL($fL, $c2);
            case self::HMAC_SHA1:
                $E8 = hash_hmac("\163\150\x61\x31", $fL, $this->key, true);
                return strcmp($c2, $E8) == 0;
        }
        ED:
        eY:
    }
    public function getAlgorith()
    {
        return $this->getAlgorithm();
    }
    public function getAlgorithm()
    {
        return $this->cryptParams["\x6d\x65\x74\x68\157\144"];
    }
    public static function makeAsnSegment($ZV, $II)
    {
        switch ($ZV) {
            case 0x2:
                if (!(ord($II) > 0x7f)) {
                    goto Vv;
                }
                $II = chr(0) . $II;
                Vv:
                goto XH;
            case 0x3:
                $II = chr(0) . $II;
                goto XH;
        }
        oa:
        XH:
        $kd = strlen($II);
        if ($kd < 128) {
            goto Lq;
        }
        if ($kd < 0x100) {
            goto QX;
        }
        if ($kd < 0x10000) {
            goto Sp;
        }
        $Qr = null;
        goto uw;
        Sp:
        $Qr = sprintf("\45\x63\x25\x63\x25\x63\x25\x63\x25\x73", $ZV, 0x82, $kd / 0x100, $kd % 0x100, $II);
        uw:
        goto gT;
        QX:
        $Qr = sprintf("\x25\x63\45\x63\x25\143\45\163", $ZV, 0x81, $kd, $II);
        gT:
        goto vX;
        Lq:
        $Qr = sprintf("\x25\x63\x25\x63\x25\x73", $ZV, $kd, $II);
        vX:
        return $Qr;
    }
    public static function convertRSA($mQ, $j1)
    {
        $B2 = self::makeAsnSegment(0x2, $j1);
        $QP = self::makeAsnSegment(0x2, $mQ);
        $Ws = self::makeAsnSegment(0x30, $QP . $B2);
        $VS = self::makeAsnSegment(0x3, $Ws);
        $TX = pack("\x48\52", "\63\x30\60\x44\x30\66\x30\71\62\x41\70\66\64\70\x38\66\x46\67\x30\104\60\x31\x30\x31\x30\x31\x30\x35\x30\60");
        $cq = self::makeAsnSegment(0x30, $TX . $VS);
        $Td = base64_encode($cq);
        $w0 = "\x2d\55\x2d\55\x2d\x42\105\x47\111\116\x20\120\125\x42\114\111\x43\40\x4b\x45\x59\55\x2d\55\x2d\x2d\12";
        $v7 = 0;
        Wy:
        if (!($FL = substr($Td, $v7, 64))) {
            goto Fq;
        }
        $w0 = $w0 . $FL . "\12";
        $v7 += 64;
        goto Wy;
        Fq:
        return $w0 . "\x2d\x2d\x2d\55\x2d\x45\116\104\x20\120\125\102\x4c\111\x43\x20\x4b\x45\x59\x2d\x2d\55\55\x2d\12";
    }
    public function serializeKey($EB)
    {
    }
    public function getX509Certificate()
    {
        return $this->x509Certificate;
    }
    public function getX509Thumbprint()
    {
        return $this->X509Thumbprint;
    }
    public static function fromEncryptedKeyElement(DOMElement $sU)
    {
        $h8 = new XMLSecEnc();
        $h8->setNode($sU);
        if ($td = $h8->locateKey()) {
            goto l4;
        }
        throw new Exception("\x55\x6e\141\x62\x6c\x65\x20\x74\x6f\40\x6c\157\x63\x61\164\x65\40\141\154\147\157\162\151\164\150\x6d\x20\x66\157\x72\x20\164\x68\x69\x73\x20\105\x6e\143\162\x79\x70\164\145\144\40\113\x65\171");
        l4:
        $td->isEncrypted = true;
        $td->encryptedCtx = $h8;
        XMLSecEnc::staticLocateKeyInfo($td, $sU);
        return $td;
    }
}
