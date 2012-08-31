<?php
/*
 * TRIVIA framework
 * Main config file, bootloader
 *
 *
 */


define('TRIVIA_MAIN_DIR',__DIR__); //main - for autoloading
define('TRIVIA_FILECACHE_DIR',__DIR__.'/filecache'); //for filecache
define('TRIVIA_LOGS_DIR',__DIR__.'/logs'); //for logs

//including tools
require_once 'tools/DB.php';
require_once 'tools/MEMCACHED.php';
require_once 'tools/FILECACHE.php';
require_once 'tools/REDISKO.php';
require_once 'tools/ROUTER.php';


if(file_exists('/etc/vps'))
    {
        /*
         * this is a way to set up the enviroment for working server
         */
        define('DEVEL',false);
        ini_set('display_errors',false);


        DB::addLink('main','mysql://trivia:trivia@localhost/trivia');
        MEMCACHE::setPrefix('trivia');
    }
else
    {
        /*
         * development server
         * set the config options here
         */
        define('DEVEL',true);
        ini_set('display_errors',true);

        DB::addLink('main','mysql://trivia:trivia@localhost/trivia');
        MEMCACHE::setPrefix('trivia');
    }