<?php
require dirname(__DIR__) . '/config/bootstrap.php';
redirect(current_user() ? 'dashboard.php' : 'login.php');
