<?php


namespace IDP\Helper\Database;

use IDP\Helper\Traits\Instance;
require_once ABSPATH . "\x77\x70\x2d\x61\144\x6d\x69\x6e\57\x69\x6e\143\x6c\165\x64\x65\163\x2f\x75\x70\147\x72\141\144\x65\x2e\x70\150\x70";
class MoDbQueries
{
    use Instance;
    private $spDataTableName;
    private $spAttrTableName;
    private $userMetaTable;
    private function __construct()
    {
        global $wpdb;
        $this->spDataTableName = is_multisite() ? "\155\x6f\x5f\163\x70\137\x64\x61\164\141" : $wpdb->prefix . "\155\157\137\163\160\x5f\144\141\x74\141";
        $this->spAttrTableName = is_multisite() ? "\x6d\157\x5f\x73\x70\137\141\164\164\x72\151\x62\165\164\x65\x73" : $wpdb->prefix . "\x6d\157\x5f\163\x70\137\x61\x74\x74\x72\151\142\x75\x74\x65\163";
        $this->userMetaTable = $wpdb->base_prefix . "\x75\x73\x65\162\x6d\x65\164\141";
    }
    function generate_tables()
    {
        global $wpdb;
        $Sg = '';
        if (!$wpdb->has_cap("\x63\157\x6c\154\x61\x74\151\157\156")) {
            goto FZ;
        }
        if (empty($wpdb->charset)) {
            goto Wl;
        }
        $Sg .= "\x44\x45\106\x41\125\x4c\x54\40\103\110\x41\x52\101\x43\124\105\122\40\x53\105\124\x20{$wpdb->charset}";
        Wl:
        if (empty($wpdb->collate)) {
            goto rt;
        }
        $Sg .= "\x20\x43\117\114\x4c\x41\124\105\x20{$wpdb->collate}";
        rt:
        FZ:
        $sE = "\x43\122\x45\x41\x54\105\x20\x54\101\102\x4c\105\40" . $this->spDataTableName . "\40\50\xd\xa\40\x20\x20\x20\40\x20\40\x20\x20\40\x20\40\40\x20\40\x20\x20\40\40\x20\x69\144\40\x62\x69\147\151\156\164\50\62\60\51\x20\x4e\x4f\124\40\x4e\x55\114\x4c\40\141\x75\x74\157\137\x69\x6e\x63\x72\x65\x6d\x65\156\x74\x2c\xd\12\40\40\40\40\40\40\x20\x20\40\x20\x20\x20\40\40\x20\40\40\x20\40\x20\155\157\137\151\144\x70\137\x73\160\137\156\141\x6d\x65\x20\164\145\170\164\40\116\x4f\x54\40\x4e\x55\x4c\114\54\xd\12\x20\40\x20\x20\x20\40\x20\x20\40\x20\40\x20\x20\x20\x20\x20\x20\40\x20\40\155\157\x5f\151\x64\x70\137\x73\160\x5f\x69\x73\163\x75\145\162\40\x6c\x6f\156\147\164\145\x78\164\40\116\117\x54\x20\x4e\125\x4c\114\54\15\12\40\40\x20\40\x20\40\x20\x20\40\x20\40\40\40\x20\40\x20\40\40\x20\40\x6d\x6f\x5f\151\144\160\137\141\x63\163\x5f\x75\x72\x6c\x20\x6c\x6f\x6e\147\x74\x65\x78\164\x20\116\x4f\x54\x20\x4e\x55\114\x4c\x2c\15\12\40\40\x20\40\x20\x20\x20\x20\x20\40\x20\40\x20\40\40\40\40\40\x20\40\155\x6f\x5f\151\x64\x70\137\x63\x65\162\164\x20\x6c\x6f\x6e\147\164\145\x78\164\40\x4e\x55\114\114\x2c\15\xa\40\40\40\40\x20\40\40\x20\x20\x20\x20\x20\40\x20\40\x20\x20\40\x20\x20\x6d\157\x5f\x69\144\160\x5f\143\145\162\x74\x5f\x65\156\143\162\x79\x70\164\x20\154\157\x6e\x67\x74\x65\170\164\40\x4e\125\x4c\x4c\54\xd\xa\40\x20\40\40\x20\x20\x20\40\40\40\x20\x20\40\x20\40\40\x20\40\40\x20\155\157\x5f\x69\144\x70\x5f\156\141\x6d\x65\151\144\x5f\x66\x6f\162\155\141\x74\x20\x6c\157\x6e\x67\164\145\170\164\x20\116\117\x54\40\x4e\125\114\x4c\54\15\12\40\40\x20\x20\x20\x20\40\x20\x20\x20\40\40\x20\40\40\x20\40\40\40\x20\x6d\x6f\x5f\x69\144\x70\137\156\141\x6d\x65\x69\x64\x5f\141\164\x74\162\40\x76\x61\162\x63\150\141\x72\50\x35\x35\51\x20\x44\105\x46\x41\x55\x4c\x54\40\x27\145\155\x61\x69\154\x41\144\144\162\145\163\x73\47\40\x4e\x4f\124\40\x4e\x55\114\114\54\xd\12\40\40\x20\x20\x20\40\x20\x20\40\x20\40\40\x20\40\x20\40\40\40\40\40\155\157\x5f\x69\x64\x70\x5f\x72\145\x73\x70\157\156\163\x65\x5f\163\x69\147\x6e\x65\144\40\163\x6d\141\154\154\x69\x6e\x74\x20\116\x55\114\114\54\15\xa\40\40\40\40\40\x20\x20\40\x20\x20\40\x20\x20\x20\x20\x20\40\x20\40\40\x6d\x6f\x5f\x69\x64\x70\137\x61\163\163\x65\162\164\x69\x6f\156\137\x73\x69\x67\156\x65\x64\x20\x73\155\x61\x6c\x6c\151\x6e\164\x20\116\125\114\x4c\x2c\15\xa\40\x20\40\40\x20\40\x20\40\40\x20\40\x20\40\40\x20\40\40\40\40\x20\155\x6f\137\151\144\160\x5f\x65\x6e\143\162\x79\160\164\x65\x64\137\141\163\x73\145\162\x74\151\157\x6e\x20\x73\155\x61\154\154\151\156\x74\x20\116\x55\x4c\114\54\15\12\40\40\x20\40\40\40\40\x20\40\x20\40\x20\x20\40\40\x20\x20\40\x20\40\155\x6f\137\151\x64\x70\x5f\145\156\141\x62\154\145\x5f\147\x72\x6f\x75\160\137\155\x61\160\160\x69\x6e\147\x20\163\x6d\x61\x6c\154\151\x6e\164\x20\116\x55\114\114\54\15\12\x20\x20\40\40\40\x20\x20\x20\x20\x20\40\40\x20\x20\x20\x20\x20\40\40\40\x6d\157\x5f\x69\144\160\137\144\x65\x66\x61\165\154\164\x5f\x72\145\154\141\171\123\164\x61\164\145\40\154\x6f\x6e\x67\164\145\170\x74\40\116\125\x4c\x4c\x2c\15\xa\x20\x20\x20\40\40\40\40\x20\x20\x20\x20\x20\40\40\x20\40\40\x20\40\40\x6d\x6f\137\x69\x64\x70\x5f\154\x6f\147\157\x75\x74\137\x75\162\x6c\x20\154\157\x6e\x67\164\145\170\164\x20\116\125\114\114\54\15\xa\40\x20\x20\x20\x20\x20\x20\x20\40\x20\40\x20\x20\40\x20\40\x20\40\40\x20\x6d\157\137\151\x64\160\x5f\x6c\157\x67\157\x75\164\x5f\x62\x69\x6e\144\151\x6e\147\x5f\x74\x79\x70\x65\x20\166\141\162\x63\x68\141\x72\x28\61\x35\51\x20\104\x45\x46\x41\125\x4c\x54\x20\x27\110\x74\164\x70\122\x65\144\151\162\145\x63\x74\x27\x20\x4e\x4f\x54\x20\116\125\x4c\x4c\54\xd\12\x20\40\40\40\x20\x20\40\x20\x20\40\x20\x20\40\40\x20\40\x20\x20\x20\40\155\157\x5f\x69\144\160\x5f\x70\162\x6f\x74\x6f\x63\x6f\x6c\x5f\x74\x79\x70\x65\x20\x6c\x6f\156\x67\164\x65\x78\164\40\x4e\117\124\x20\116\125\x4c\114\54\xd\xa\40\40\x20\40\40\40\40\x20\x20\x20\40\x20\40\x20\40\x20\40\40\40\x20\x50\122\x49\x4d\101\122\x59\40\113\105\x59\40\x20\50\151\144\51\xd\xa\40\40\40\40\40\x20\40\x20\40\x20\40\40\x20\x20\x20\40\x29{$Sg}\x3b";
        $EZ = "\103\x52\105\101\x54\x45\40\x54\101\102\114\x45\x20" . $this->spAttrTableName . "\40\x28\15\12\40\x20\x20\x20\40\x20\40\40\x20\x20\x20\40\x20\x20\x20\x20\x20\x20\x20\40\x69\x64\x20\142\151\147\151\156\164\x28\62\x30\x29\x20\116\117\124\x20\x4e\x55\114\114\40\141\165\x74\x6f\137\x69\x6e\x63\162\145\155\x65\x6e\x74\54\15\12\40\x20\x20\x20\x20\40\x20\x20\x20\40\x20\x20\x20\x20\x20\40\40\40\40\40\155\157\137\163\x70\x5f\x69\x64\x20\142\151\x67\x69\x6e\x74\50\x32\x30\51\54\xd\12\x20\x20\40\x20\x20\40\x20\40\40\x20\40\40\x20\x20\x20\40\x20\40\x20\40\155\x6f\137\x73\160\x5f\141\164\164\162\137\x6e\141\155\145\x20\x6c\x6f\156\147\164\145\170\x74\x20\x4e\117\x54\40\x4e\125\x4c\114\x2c\xd\xa\x20\40\x20\40\40\40\40\x20\x20\40\40\40\x20\x20\x20\x20\40\x20\40\x20\155\157\x5f\163\x70\x5f\141\x74\164\162\137\166\141\154\x75\145\40\x6c\157\156\147\x74\x65\170\x74\40\x4e\117\124\x20\x4e\125\x4c\114\54\15\12\x20\40\x20\x20\40\40\40\40\40\x20\x20\40\x20\x20\x20\x20\x20\x20\40\x20\x6d\x6f\x5f\x61\x74\x74\162\137\164\x79\160\x65\40\163\x6d\141\x6c\x6c\151\156\164\40\x44\x45\x46\101\125\x4c\x54\40\x30\40\116\117\124\x20\116\x55\x4c\114\54\15\12\x20\x20\x20\x20\x20\40\40\40\40\x20\40\40\x20\40\40\40\x20\x20\x20\40\x50\x52\111\115\101\122\131\x20\x4b\x45\131\40\40\x28\x69\x64\x29\54\15\12\x20\x20\40\x20\x20\x20\x20\40\40\40\x20\40\40\x20\x20\40\40\40\40\40\106\117\122\x45\x49\x47\x4e\x20\113\x45\131\40\40\50\x6d\x6f\137\x73\160\137\x69\144\x29\40\x52\105\x46\105\122\x45\x4e\103\x45\x53\x20{$this->spDataTableName}\x20\x28\x69\144\51\xd\12\40\40\40\40\x20\40\x20\x20\x20\40\x20\40\x20\x20\x20\x20\51{$Sg}\73";
        dbDelta($sE);
        dbDelta($EZ);
    }
    function checkTablesAndRunQueries()
    {
        $sF = get_site_option("\x6d\157\x5f\x73\x61\155\154\137\151\x64\x70\x5f\x70\x6c\x75\147\x69\156\137\x76\145\x72\163\x69\157\156");
        if (!$sF) {
            goto C_;
        }
        if (!($sF < MSI_DB_VERSION)) {
            goto Y0;
        }
        update_site_option("\x6d\157\x5f\x73\141\155\154\x5f\x69\x64\160\137\x70\154\165\147\x69\x6e\x5f\x76\145\x72\163\151\157\156", MSI_DB_VERSION);
        Y0:
        $this->checkVersionAndUpdate($sF);
        goto iS;
        C_:
        update_site_option("\155\x6f\137\163\x61\155\154\137\151\x64\x70\137\x70\x6c\x75\x67\x69\x6e\x5f\166\145\162\163\x69\x6f\x6e", MSI_DB_VERSION);
        $this->generate_tables();
        if (!ob_get_contents()) {
            goto cf;
        }
        ob_clean();
        cf:
        iS:
    }
    function checkVersionAndUpdate($sF)
    {
        if (strcasecmp($sF, "\x31\56\60") == 0) {
            goto iH;
        }
        if (strcasecmp($sF, "\61\56\x30\56\x32") == 0) {
            goto TA;
        }
        if (strcasecmp($sF, "\x31\x2e\60\x2e\x34") == 0) {
            goto CV;
        }
        if (strcasecmp($sF, "\61\x2e\62") == 0) {
            goto Cw;
        }
        if (!(strcasecmp($sF, "\x31\x2e\x33") == 0)) {
            goto dm;
        }
        $this->mo_update_protocol_type();
        dm:
        goto x9;
        Cw:
        $this->mo_update_custom_attr();
        $this->mo_update_protocol_type();
        x9:
        goto Jl;
        CV:
        $this->mo_update_logout();
        $this->mo_update_custom_attr();
        $this->mo_update_protocol_type();
        Jl:
        goto nk;
        TA:
        $this->mo_update_logout();
        $this->mo_update_relay();
        $this->mo_update_custom_attr();
        $this->mo_update_protocol_type();
        nk:
        goto j_;
        iH:
        $this->mo_update_logout();
        $this->mo_update_cert();
        $this->mo_update_relay();
        $this->mo_update_custom_attr();
        $this->mo_update_protocol_type();
        j_:
    }
    function mo_update_protocol_type()
    {
        global $wpdb;
        $wpdb->query("\x41\114\x54\x45\x52\40\x54\101\x42\x4c\105\40" . $this->spDataTableName . "\x20\101\x44\104\x20\x43\117\x4c\x55\x4d\x4e\x20\155\157\x5f\151\x64\x70\x5f\x70\162\x6f\164\x6f\143\157\154\x5f\x74\171\x70\145\x20\x6c\157\x6e\x67\x74\145\x78\x74\40\x4e\117\124\40\x4e\125\x4c\114");
        $wpdb->query("\125\120\x44\x41\124\x45\x20" . $this->spDataTableName . "\40\123\x45\x54\40\x6d\x6f\x5f\151\x64\160\137\160\162\x6f\164\x6f\143\157\x6c\137\x74\x79\x70\145\40\75\40\x27\123\101\x4d\114\47");
    }
    function mo_update_logout()
    {
        global $wpdb;
        $wpdb->query("\x41\x4c\124\105\122\40\x54\101\102\114\x45\x20" . $this->spDataTableName . "\40\101\104\x44\40\103\117\114\x55\x4d\x4e\x20\x6d\x6f\137\151\x64\160\137\x6c\x6f\147\x6f\165\164\x5f\165\162\154\40\x6c\157\156\147\164\145\x78\x74\x20\x4e\x55\x4c\x4c");
        $wpdb->query("\x41\x4c\x54\105\122\x20\124\x41\x42\x4c\x45\40" . $this->spDataTableName . "\x20\101\x44\104\x20\103\x4f\x4c\x55\115\x4e\40\155\157\x5f\151\x64\160\x5f\x6c\157\x67\x6f\x75\x74\137\x62\151\x6e\144\x69\156\x67\x5f\164\171\160\x65\x20\166\x61\162\143\150\x61\x72\50\x31\x35\x29\40\104\105\x46\101\x55\x4c\124\x20\47\110\164\x74\x70\122\145\x64\151\x72\145\x63\x74\47\x20\116\x4f\x54\x20\x4e\x55\114\x4c");
    }
    function mo_update_cert()
    {
        global $wpdb;
        $wpdb->query("\101\114\124\x45\x52\40\124\101\102\x4c\105\40" . $this->spDataTableName . "\x20\x41\104\104\40\103\x4f\x4c\125\x4d\x4e\40\x6d\x6f\137\x69\144\160\x5f\x63\145\162\164\137\145\156\x63\162\x79\x70\164\40\x6c\157\156\147\x74\145\170\164\40\x4e\x55\114\x4c");
        $wpdb->query("\101\x4c\x54\105\x52\40\x54\101\x42\x4c\105\x20" . $this->spDataTableName . "\x20\x41\104\104\x20\103\117\114\125\x4d\116\40\155\x6f\137\151\x64\x70\x5f\x65\156\x63\x72\x79\160\164\x65\x64\x5f\141\163\163\145\162\164\x69\157\156\x20\163\x6d\x61\x6c\154\151\156\164\x20\x4e\125\x4c\x4c");
    }
    function mo_update_relay()
    {
        global $wpdb;
        $wpdb->query("\101\x4c\x54\105\x52\40\x54\101\102\x4c\x45\40" . $this->spDataTableName . "\40\x41\104\x44\40\103\x4f\x4c\125\x4d\116\x20\x6d\157\137\x69\x64\x70\137\144\x65\x66\x61\x75\154\164\137\x72\145\154\141\x79\123\x74\x61\x74\145\40\154\157\x6e\147\x74\145\x78\164\x20\x4e\x55\x4c\114");
    }
    function mo_update_custom_attr()
    {
        global $wpdb;
        $wpdb->query("\101\114\x54\x45\122\40\x54\x41\102\x4c\x45\x20" . $this->spAttrTableName . "\40\101\104\x44\x20\103\x4f\114\x55\115\x4e\40\155\157\137\x61\x74\164\162\137\164\171\160\145\x20\163\155\141\154\154\x69\156\x74\40\104\x45\x46\101\125\114\x54\40\60\x20\x4e\x4f\124\40\116\x55\114\x4c");
        $wpdb->update($this->spAttrTableName, array("\x6d\157\x5f\141\x74\x74\162\137\164\x79\160\145" => "\x31"), array("\155\157\137\x73\160\x5f\x61\x74\164\x72\x5f\156\x61\x6d\145" => "\x67\162\x6f\x75\160\x4d\x61\x70\x4e\x61\155\x65"));
    }
    function get_sp_list()
    {
        global $wpdb;
        return $wpdb->get_results("\123\105\x4c\x45\x43\124\x20\x2a\40\x46\122\x4f\x4d\x20" . $this->spDataTableName);
    }
    function get_sp_data($e6)
    {
        global $wpdb;
        return $wpdb->get_row("\123\x45\x4c\x45\103\124\x20\x2a\40\106\x52\117\115\x20" . $this->spDataTableName . "\x20\x57\x48\x45\x52\105\40\x69\x64\75" . $e6);
    }
    function get_sp_count()
    {
        global $wpdb;
        $Qf = "\123\105\x4c\x45\x43\124\x20\103\x4f\125\x4e\x54\50\52\x29\x20\106\x52\117\x4d\x20" . $this->spDataTableName;
        return $wpdb->get_var($Qf);
    }
    function get_sp_attributes($e6)
    {
        global $wpdb;
        return $wpdb->get_results("\x53\105\x4c\x45\103\124\x20\x2a\40\106\x52\x4f\x4d\40" . $this->spAttrTableName . "\40\x57\110\105\x52\105\40\x6d\x6f\137\163\160\x5f\x69\x64\x20\75\x20{$e6}\40\101\x4e\x44\x20\155\x6f\x5f\x73\x70\137\141\x74\164\162\137\156\x61\x6d\145\x20\x3c\x3e\x20\47\x67\162\157\165\x70\x4d\141\x70\x4e\x61\x6d\x65\x27\40\101\116\104\40\x6d\157\137\141\164\164\162\137\x74\x79\x70\x65\40\x3d\40\x30");
    }
    function get_sp_role_attribute($e6)
    {
        global $wpdb;
        return $wpdb->get_row("\123\105\x4c\105\x43\124\x20\52\40\x46\x52\x4f\115\40" . $this->spAttrTableName . "\x20\127\110\x45\122\x45\40\x6d\x6f\137\163\160\137\151\144\40\x3d\x20{$e6}\40\101\x4e\x44\x20\x6d\157\x5f\x73\x70\137\141\164\x74\x72\x5f\x6e\x61\155\145\40\75\40\x27\x67\x72\157\165\x70\x4d\141\160\x4e\x61\155\x65\47");
    }
    function get_all_sp_attributes($e6)
    {
        global $wpdb;
        return $wpdb->get_results("\123\x45\x4c\105\103\124\x20\52\x20\106\122\117\x4d\40" . $this->spAttrTableName . "\x20\x57\x48\x45\122\x45\40\x6d\157\137\163\x70\137\x69\x64\40\75\40{$e6}\40");
    }
    function get_sp_from_issuer($QB)
    {
        global $wpdb;
        return $wpdb->get_row("\x53\105\114\105\x43\124\x20\x2a\40\x46\122\117\x4d\40" . $this->spDataTableName . "\40\x57\x48\105\122\x45\40\155\x6f\137\x69\x64\160\137\163\x70\x5f\151\x73\163\165\145\162\x20\75\x20\47{$QB}\47");
    }
    function get_sp_from_name($Pk)
    {
        global $wpdb;
        return $wpdb->get_row("\x53\105\114\x45\x43\x54\x20\x2a\40\106\122\117\x4d\40" . $this->spDataTableName . "\x20\127\x48\105\122\105\40\x6d\157\x5f\151\x64\x70\x5f\163\x70\137\156\141\x6d\145\x20\75\x20\x27{$Pk}\x27");
    }
    function get_sp_from_acs($Z8)
    {
        global $wpdb;
        return $wpdb->get_row("\x53\x45\x4c\105\x43\124\x20\52\40\106\x52\117\x4d\40" . $this->spDataTableName . "\40\127\x48\x45\122\105\40\155\x6f\x5f\151\x64\x70\137\x61\143\x73\x5f\x75\x72\x6c\40\x3d\40\47{$Z8}\x27");
    }
    function insert_sp_data($fL)
    {
        global $wpdb;
        return $wpdb->insert($this->spDataTableName, $fL);
    }
    function update_sp_data($fL, $kl)
    {
        global $wpdb;
        $wpdb->update($this->spDataTableName, $fL, $kl);
    }
    function delete_sp($RV, $v0)
    {
        global $wpdb;
        $this->delete_sp_attributes($v0);
        $wpdb->delete($this->spDataTableName, $RV, $rD = null);
    }
    function delete_sp_attributes($u0)
    {
        global $wpdb;
        $wpdb->delete($this->spAttrTableName, $u0, $rD = null);
    }
    function insert_sp_attributes($qz)
    {
        global $wpdb;
        $wpdb->insert($this->spAttrTableName, $qz);
    }
    function get_custom_sp_attr($e6)
    {
        global $wpdb;
        return $wpdb->get_results("\123\x45\114\105\103\x54\x20\52\40\106\x52\117\x4d\40" . $this->spAttrTableName . "\x20\127\110\105\122\x45\40\155\x6f\137\163\160\x5f\151\144\40\x3d\x20{$e6}\40\x41\x4e\x44\x20\155\x6f\137\x61\164\164\162\x5f\x74\x79\160\x65\40\x3d\x20\62");
    }
    function get_users()
    {
        global $wpdb;
        return $wpdb->get_var("\x53\x45\114\105\x43\x54\x20\103\x4f\125\116\x54\50\52\51\40\106\122\x4f\x4d\x20" . $this->userMetaTable . "\40\127\110\x45\122\105\40\x6d\145\x74\141\x5f\x6b\145\x79\75\x27\x6d\157\x5f\x69\x64\x70\x5f\x75\x73\145\162\x5f\x74\171\x70\145\47");
    }
    function get_protocol()
    {
        global $wpdb;
        return $wpdb->get_results("\123\105\x4c\x45\x43\124\40\x6d\157\137\x69\144\160\x5f\x70\x72\157\164\x6f\x63\x6f\x6c\x5f\x74\171\160\145\40\106\x52\117\115\40" . $this->spDataTableName);
    }
    function getDistinctMetaAttributes()
    {
        global $wpdb;
        return $wpdb->get_results("\123\x45\x4c\x45\x43\x54\40\104\x49\123\124\x49\116\103\x54\x20\x6d\145\x74\x61\x5f\153\x65\x79\40\x46\122\117\x4d\x20" . $this->userMetaTable);
    }
}
