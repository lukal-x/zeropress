<?php

require_once "main.php";

use Zeropress\Installer;

$installer = new Installer();

$configured = "v6.9.1";

if (!$installer->getCurrent() != $configured) {
    if ($installer->installVersion($configured)) {
        print "Installed!";
    }
}

?>

    Hello and welcome to Zeropress <?php print $zp->getVersion() ?>!
