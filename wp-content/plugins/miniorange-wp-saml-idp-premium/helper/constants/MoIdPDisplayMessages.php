<?php


namespace IDP\Helper\Constants;

class MoIdPDisplayMessages
{
    private $message;
    private $type;
    function __construct($hW, $ZV)
    {
        $this->_message = $hW;
        $this->_type = $ZV;
        add_action("\141\x64\x6d\x69\x6e\137\x6e\157\164\151\x63\145\163", array($this, "\162\x65\x6e\144\x65\162"));
    }
    function render()
    {
        switch ($this->_type) {
            case "\x43\125\123\124\117\x4d\x5f\115\105\x53\123\x41\x47\x45":
                echo $this->_message;
                goto KL;
            case "\116\x4f\x54\111\103\x45":
                echo "\74\144\151\166\40\x73\164\x79\154\x65\75\x22\x6d\x61\x72\x67\151\156\x2d\x74\157\160\x3a\61\45\73\x22\40\x63\x6c\x61\x73\163\75\42\151\x73\x2d\144\151\x73\x6d\151\163\163\151\142\154\145\x20\156\x6f\164\151\x63\145\40\x6e\x6f\164\151\x63\x65\x2d\167\x61\x72\156\151\x6e\x67\x22\x3e\x20\x3c\x70\76" . $this->_message . "\74\57\x70\76\x20\74\57\x64\151\166\x3e";
                goto KL;
            case "\105\x52\122\x4f\122":
                echo "\x3c\x64\151\x76\x20\x20\x73\164\171\154\145\75\x22\x6d\x61\162\147\x69\x6e\55\164\x6f\x70\72\61\x25\x3b\x22\x20\143\x6c\x61\163\x73\x3d\x22\156\157\x74\151\x63\145\x20\156\157\164\x69\x63\x65\x2d\145\162\162\x6f\162\x20\x69\x73\55\x64\x69\x73\x6d\x69\163\163\x69\x62\154\145\42\x3e\40\x3c\x70\x3e" . $this->_message . "\x3c\x2f\x70\76\40\74\57\x64\151\166\76";
                goto KL;
            case "\x53\125\103\x43\x45\x53\x53":
                echo "\74\x64\x69\166\x20\40\x73\x74\x79\x6c\x65\x3d\x22\155\x61\x72\147\x69\156\x2d\164\157\x70\72\x31\x25\73\42\40\x63\x6c\x61\163\x73\75\x22\156\x6f\x74\x69\x63\x65\x20\156\157\x74\x69\x63\145\55\x73\x75\143\x63\145\x73\163\40\x69\163\x2d\x64\151\163\155\151\163\163\151\142\x6c\145\x22\76\40\x3c\160\x3e" . $this->_message . "\x3c\57\160\x3e\40\74\57\x64\151\166\x3e";
                goto KL;
        }
        xJ:
        KL:
    }
}
