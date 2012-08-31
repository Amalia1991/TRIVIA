<?php
session_start();
require_once 'BOOTLOADER.php';

/*
 * application for testing
 */
ROUTER::setServerRoot('tests');

if(!ROUTER::deploy())
    {
        echo '404';
    }