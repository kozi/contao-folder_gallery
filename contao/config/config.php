<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2015 Leo Feyer
 *
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2013-2015 <http://kozianka.de/>
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    folder-gallery
 * @license    LGPL 
 * @filesource
 */


$GLOBALS['FE_MOD']['miscellaneous']['folder_gallery']        = 'FolderGalleryModule';

$GLOBALS['BE_MOD']['content']['folder_gallery']              = array(
            'icon'       => 'system/modules/folder-gallery/assets/pictures.png',
            'tables'     => array('tl_folder_gallery_category', 'tl_folder_gallery'),
            'sync'       => array('FolderGallery', 'syncGalleryCategory'),
);



