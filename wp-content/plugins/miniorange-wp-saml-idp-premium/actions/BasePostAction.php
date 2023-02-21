<?php


namespace IDP\Actions;

use IDP\Helper\Traits\Instance;
abstract class BasePostAction
{
    use Instance;
    protected $_nonce;
    function __construct()
    {
        add_action("\x61\x64\x6d\x69\x6e\137\x69\156\x69\x74", array($this, "\x68\141\x6e\x64\x6c\145\x5f\160\157\163\x74\137\144\x61\164\141"), 1);
    }
    abstract function handle_post_data();
    abstract function route_post_data($C2);
}
