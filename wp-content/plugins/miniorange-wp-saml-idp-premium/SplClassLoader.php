<?php


namespace IDP;

final class SplClassLoader
{
    private $_fileExtension = "\56\x70\x68\160";
    private $_namespace;
    private $_includePath;
    private $_namespaceSeparator = "\134";
    public function __construct($kH = null, $Oq = null)
    {
        $this->_namespace = $kH;
        $this->_includePath = $Oq;
    }
    public function register()
    {
        spl_autoload_register(array($this, "\154\x6f\x61\x64\x43\154\x61\x73\163"));
    }
    public function unregister()
    {
        spl_autoload_unregister(array($this, "\154\x6f\141\x64\x43\x6c\x61\x73\163"));
    }
    public function loadClass($C6)
    {
        if (!(null === $this->_namespace || $this->_namespace . $this->_namespaceSeparator === substr($C6, 0, strlen($this->_namespace . $this->_namespaceSeparator)))) {
            goto Rh;
        }
        $X8 = '';
        $q1 = '';
        if (!(false !== ($NK = strripos($C6, $this->_namespaceSeparator)))) {
            goto G6;
        }
        $q1 = strtolower(substr($C6, 0, $NK));
        $C6 = substr($C6, $NK + 1);
        $X8 = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $q1) . DIRECTORY_SEPARATOR;
        G6:
        $X8 .= str_replace("\137", DIRECTORY_SEPARATOR, $C6) . $this->_fileExtension;
        $X8 = str_replace("\x69\x64\160", MSI_NAME, $X8);
        require ($this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '') . $X8;
        Rh:
    }
}
