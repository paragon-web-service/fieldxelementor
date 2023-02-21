<?php


namespace IDP\Helper\Utilities;

final class MenuItems
{
    private $_callback;
    private $_menuLogo;
    private $_tabDetails;
    private $_parentSlug;
    function __construct($Eu)
    {
        $this->_callback = [$Eu, "\x6d\x6f\137\x73\x70\137\x73\x65\x74\164\x69\x6e\147\x73"];
        $this->_menuLogo = MSI_ICON;
        $JB = TabDetails::instance();
        $this->_tabDetails = $JB->_tabDetails;
        $this->_parentSlug = $JB->_parentSlug;
        $this->addMainMenu();
        $this->addSubMenus();
    }
    private function addMainMenu()
    {
        add_menu_page("\123\x41\115\114\40\111\104\120", "\x57\157\x72\x64\120\x72\145\x73\163\x20\x49\104\x50", "\x6d\x61\156\x61\147\x65\137\x6f\160\x74\151\x6f\156\x73", $this->_parentSlug, $this->_callback, $this->_menuLogo);
    }
    private function addSubMenus()
    {
        foreach ($this->_tabDetails as $qk) {
            add_submenu_page($this->_parentSlug, $qk->_pageTitle, $qk->_menuTitle, "\155\x61\x6e\x61\147\145\x5f\157\160\x74\x69\x6f\156\x73", $qk->_menuSlug, $this->_callback);
            Lw:
        }
        jN:
    }
}
