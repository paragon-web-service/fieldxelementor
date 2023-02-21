<?php


namespace IDP\Helper\Utilities;

use IDP\Helper\Traits\Instance;
final class TabDetails
{
    use Instance;
    public $_tabDetails;
    public $_parentSlug;
    private function __construct()
    {
        $mW = MoIDPUtility::micr();
        $this->_parentSlug = "\151\x64\x70\137\163\145\164\x74\151\156\x67\163";
        $this->_tabDetails = [Tabs::PROFILE => new PluginPageDetails("\x53\x41\115\x4c\x20\x49\104\120\x20\x2d\x20\101\143\143\x6f\x75\x6e\x74", "\x69\144\x70\137\x70\162\157\146\x69\x6c\x65", !$mW ? "\x41\143\x63\157\x75\x6e\164\40\x53\x65\164\x75\x70" : "\125\x73\145\x72\40\x50\x72\157\146\x69\154\x65", !$mW ? "\x41\143\x63\x6f\165\x6e\x74\40\x53\x65\164\165\160" : "\120\x72\157\x66\151\x6c\x65", "\x54\150\x69\163\40\124\x61\142\x20\143\157\156\164\x61\x69\156\163\x20\x79\x6f\165\162\40\x50\x72\157\x66\151\154\x65\40\x69\156\146\157\x72\x6d\141\x74\x69\x6f\156\x2e\40\x49\146\x20\171\157\x75\40\x68\141\x76\145\x6e\x27\164\x20\x72\145\x67\151\x73\x74\x65\x72\145\144\x20\164\150\x65\x6e\40\x79\x6f\x75\x20\143\141\x6e\40\144\x6f\40\x73\157\40\146\x72\157\x6d\x20\150\145\x72\x65\56"), Tabs::IDP_CONFIG => new PluginPageDetails("\x53\101\x4d\x4c\x20\111\x44\120\x20\55\x20\103\x6f\x6e\146\x69\147\x75\x72\x65\40\111\x44\120", "\x69\x64\x70\x5f\143\157\156\146\x69\147\165\162\x65\137\x69\x64\160", "\123\145\x72\x76\x69\x63\145\40\120\x72\157\166\151\144\x65\162\x73", "\123\x65\162\166\151\x63\145\40\x50\162\x6f\166\151\144\145\x72\x73", "\124\x68\x69\163\x20\124\141\142\40\x69\x73\40\164\x68\145\x20\163\145\143\x74\151\x6f\x6e\x20\x77\x68\x65\162\x65\40\171\x6f\165\x20\103\157\156\x66\151\147\x75\162\145\40\x79\x6f\x75\x72\x20\x53\145\162\166\151\143\145\x20\120\162\x6f\x76\x69\144\x65\162\x27\163\x20\x64\145\164\x61\151\154\x73\40\x6e\x65\145\144\x65\x64\x20\x66\x6f\x72\40\x53\x53\117\x2e"), Tabs::METADATA => new PluginPageDetails("\123\x41\x4d\114\40\111\104\120\x20\x2d\x20\x4d\145\x74\x61\x64\x61\164\x61", "\x69\144\160\137\x6d\x65\x74\141\144\x61\164\x61", "\x49\104\x50\40\115\x65\164\141\x64\x61\164\x61", "\111\x44\x50\x20\115\145\x74\141\144\141\164\x61", "\x54\150\x69\x73\40\x54\141\x62\40\151\x73\x20\167\x68\145\x72\145\40\171\x6f\x75\40\x77\x69\x6c\154\40\x66\151\x6e\144\40\x69\156\146\157\x72\x6d\x61\164\151\x6f\156\40\164\x6f\40\x70\165\x74\40\151\x6e\x20\x79\157\x75\x72\40\x53\x65\162\166\151\143\145\40\x50\162\157\x76\x69\x64\145\x72\x27\163\x20\x63\157\x6e\146\151\x67\x75\x72\141\164\151\157\x6e\40\160\x61\147\x65\56"), Tabs::SIGN_IN_SETTINGS => new PluginPageDetails("\123\101\115\114\x20\x49\104\x50\40\55\40\x53\x69\147\x6e\x49\156\x20\123\x65\164\164\x69\x6e\x67\163", "\151\144\160\x5f\x73\151\147\x6e\x69\x6e\137\163\145\164\x74\x69\156\x67\x73", "\x53\123\117\x20\117\160\164\x69\x6f\156\x73", "\x53\x53\117\x20\x4f\160\164\x69\157\x6e\163", "\x54\x68\x69\x73\40\124\141\142\40\x69\x73\40\167\x68\x65\x72\145\40\171\x6f\x75\40\167\151\154\x6c\40\146\151\x6e\144\40\x53\x68\157\x72\x74\103\x6f\144\145\x20\141\156\x64\40\111\x64\x50\40\x49\156\x69\164\x69\x61\164\x65\144\x20\114\x69\x6e\x6b\x73\x20\x66\x6f\x72\40\x53\x53\x4f\x2e"), Tabs::ATTR_SETTINGS => new PluginPageDetails("\x53\x41\x4d\114\x20\x49\104\120\40\55\40\101\x74\164\x72\x69\142\165\x74\145\40\123\x65\164\x74\151\156\147\163", "\x69\x64\x70\137\x61\x74\164\162\137\x73\145\164\x74\x69\156\147\163", "\101\164\x74\162\x69\142\x75\164\145\57\x52\x6f\x6c\x65\40\115\141\x70\160\x69\156\x67", "\x41\164\164\x72\151\x62\165\x74\145\57\122\157\154\x65\40\115\x61\160\x70\151\x6e\147", "\x54\x68\151\163\x20\x54\141\x62\40\x69\163\40\167\150\145\162\145\x20\x79\157\165\x20\x63\157\x6e\146\x69\147\x75\x72\145\x20\164\150\x65\40\x55\163\x65\x72\40\x41\x74\x74\162\x69\x62\165\x74\x65\163\x20\x61\156\x64\x20\122\x6f\154\x65\40\164\150\x61\x74\40\171\x6f\165\x20\x77\x61\x6e\x74\x20\x74\x6f\x20\x73\x65\x6e\144\x20\157\165\164\x20\x74\157\40\x79\x6f\x75\162\x20\x53\145\162\166\151\x63\145\40\120\x72\x6f\166\x69\x64\145\x72\x2e"), Tabs::LICENSE => new PluginPageDetails("\x53\x41\x4d\x4c\x20\111\104\120\40\x2d\x20\114\151\143\145\156\163\x65", "\151\x64\x70\137\165\160\x67\x72\141\x64\145\x5f\163\145\164\x74\x69\156\147\163", "\x4c\151\143\x65\x6e\163\x65", "\125\160\x67\x72\141\x64\x65\40\120\x6c\141\156\163", "\124\x68\151\163\40\x54\141\142\x20\144\x65\x74\x61\151\154\163\x20\x61\154\154\x20\164\x68\x65\x20\160\x6c\x75\x67\151\x6e\40\x70\154\x61\x6e\x73\x20\141\156\144\x20\164\x68\145\151\162\x20\x64\x65\164\x61\x69\x6c\x73\x20\x61\154\157\x6e\147\40\167\x69\164\150\x20\x74\150\x65\x69\162\x20\165\x70\x67\x72\x61\x64\145\40\x6c\151\x6e\x6b\x73\56"), Tabs::SUPPORT => new PluginPageDetails("\x53\x41\x4d\x4c\40\111\x44\120\x20\55\40\123\x75\x70\x70\x6f\162\x74", "\x69\144\160\x5f\x73\165\x70\160\x6f\x72\164", "\x53\x75\x70\x70\x6f\162\164", "\x53\165\x70\x70\x6f\162\x74", "\131\157\x75\40\143\141\x6e\40\165\x73\x65\x20\x74\x68\145\x20\x66\157\162\155\40\150\x65\162\145\x20\164\x6f\40\x67\x65\164\40\151\x6e\x20\x74\157\x75\x63\x68\x20\167\x69\x74\150\40\x75\x73\40\x66\x6f\x72\40\x61\x6e\171\x20\153\x69\x6e\144\x20\157\x66\40\163\x75\x70\160\157\x72\164\56")];
    }
}
