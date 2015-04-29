<?php
return array(

    'module_class'    => 'Module\Sections\Main',
    'module_file'     => 'class.sections.php',
    'module_name'     => 'Sections',

    'abstract'      => false,
    'dependencies'  => array(),
    'events'        => array('routerRouteEvent', 'layoutLoadEvent', 'modelLoadEvent'),

    'name'          => 'FuzeWorks Sections',
    'description'   => 'Submodules for FuzeWorks',
    'author'        => 'TechFuze',
    'version'       => '1.0.0',
    'website'       => 'http://fuzeworks.techfuze.net/',

    'date_created'  => '29-04-2015',
    'date_updated'  => '29-04-2015',
);
