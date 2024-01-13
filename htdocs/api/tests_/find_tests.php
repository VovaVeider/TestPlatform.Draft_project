<?php
require_once '../auth/middleware.php';
$payload = rights_auth_check(['admin','user','guest']);