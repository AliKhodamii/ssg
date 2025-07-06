<?php
ini_set('session.gc_maxlifetime', 604800); // 7 days
session_set_cookie_params(604800);         // 7 days for the cookie
session_start();
session_destroy();
header("location: ../");
exit();
