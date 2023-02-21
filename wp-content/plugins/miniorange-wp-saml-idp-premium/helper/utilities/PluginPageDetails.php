<?php


namespace IDP\Helper\Utilities;

class PluginPageDetails
{
    function __construct($pf, $cQ, $If, $KK, $HM)
    {
        $this->_pageTitle = $pf;
        $this->_menuSlug = $cQ;
        $this->_menuTitle = $If;
        $this->_tabName = $KK;
        $this->_description = $HM;
    }
    public $_pageTitle;
    public $_menuSlug;
    public $_menuTitle;
    public $_tabName;
    public $_description;
}
