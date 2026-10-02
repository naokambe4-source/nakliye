<?php
/**
 * Giriş: kurulmamışsa sihirbaza, kurulmuşsa panele yönlendirir.
 */

define( 'NKLS', true );
require __DIR__ . '/lib.php';

header( 'Location: ' . ( nkls_installed() ? 'admin.php' : 'install.php' ) );
exit;
