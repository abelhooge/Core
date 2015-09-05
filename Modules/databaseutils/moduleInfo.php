<?php
return array(

    'module_class'    => 'Module\DatabaseUtils\Main',
    'module_file'     => 'class.model.php',
    'module_name'     => 'DatabaseUtils',

    'abstract'      => false,
    'dependencies'  => array('core/database'),
    'events'        => array(),
    'sections'      => array(),
    'aliases'       => array('techfuze/databaseutils'),

    'name'          => 'FuzeWorks Database Utilities',
    'description'   => 'Automatically build SQL queries using methods in this class',
    'author'        => 'core',
    'version'       => '1.0.0',
    'website'       => 'http://fuzeworks.techfuze.net/',

    'date_created'  => '29-04-2015',
    'date_updated'  => '29-04-2015',

    'enabled'       => true,
);
