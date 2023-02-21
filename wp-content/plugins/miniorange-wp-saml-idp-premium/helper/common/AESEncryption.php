<?php


class AESEncryption
{
    public static function encrypt_data($II, $xk)
    {
        $T7 = '';
        $rM = 0;
        pV:
        if (!($rM < strlen($II))) {
            goto Zb;
        }
        $S4 = substr($II, $rM, 1);
        $pY = substr($xk, $rM % strlen($xk) - 1, 1);
        $S4 = chr(ord($S4) + ord($pY));
        $T7 .= $S4;
        Nf:
        $rM++;
        goto pV;
        Zb:
        return base64_encode($T7);
    }
    public static function decrypt_data($II, $xk)
    {
        $T7 = '';
        $II = base64_decode($II);
        $rM = 0;
        Y8:
        if (!($rM < strlen($II))) {
            goto Rg;
        }
        $S4 = substr($II, $rM, 1);
        $pY = substr($xk, $rM % strlen($xk) - 1, 1);
        $S4 = chr(ord($S4) - ord($pY));
        $T7 .= $S4;
        TC:
        $rM++;
        goto Y8;
        Rg:
        return $T7;
    }
}
