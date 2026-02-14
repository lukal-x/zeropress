<?php

// ZeroPress, a minimal WordPress clone implemented in Zerolith
// @todo dashboard at the wp-admin URL
// @todo post creation
// @todo post rendering

require_once "zerolith/zl_init.php";

use Zeropress\Engine;

session_start();

$requestId = bin2hex(random_bytes(32));
$zp = new Engine($requestId, $_COOKIE);
