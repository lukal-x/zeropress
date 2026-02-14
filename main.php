<?php

// ZeroPress, a minimal WordPress clone implemented in Zerolith
// @todo dashboard at the wp-admin URL
// @todo post creation
// @todo post rendering

require_once "zerolith/zl_init.php";

use Zeropress\Engine;

session_start();
$zp = new Engine(bin2hex(random_bytes(32)), $_COOKIE);
