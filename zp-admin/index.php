<?php

require_once "../main.php";

if (! $zp->isAdmin()) {
    zpage::redirect("/zp-admin/login.php");
}

?>

    Zeropress <?php print $zp->getVersion() ?> admin dashboard.
