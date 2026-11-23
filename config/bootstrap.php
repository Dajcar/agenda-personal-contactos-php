<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/helpers.php';
require_once dirname(__DIR__) . '/src/security.php';
require_once dirname(__DIR__) . '/src/database.php';
require_once dirname(__DIR__) . '/src/validators.php';
require_once dirname(__DIR__) . '/src/auth.php';
require_once dirname(__DIR__) . '/src/contacts.php';

send_security_headers();
start_secure_session();
