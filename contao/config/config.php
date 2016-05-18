<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2016 Leo Feyer
 *
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2013-2016 <http://kozianka.de/>
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    folder_gallery
 * @license    LGPL 
 * @filesource
 */

$GLOBALS['FE_MOD']['miscellaneous']['folder_gallery'] = '\FolderGallery\Modules\FolderGalleryModule';

$GLOBALS['TL_MODELS']['tl_folder_gallery']            = '\FolderGallery\Models\FolderGalleryModel';
$GLOBALS['TL_MODELS']['tl_folder_gallery_category']   = '\FolderGallery\Models\FolderGalleryCategoryModel';

$GLOBALS['BE_MOD']['content']['folder_gallery']       = [
    'icon'       => 'system/modules/folder_gallery/assets/pictures.png',
    'tables'     => ['tl_folder_gallery_category', 'tl_folder_gallery'],
    'sync'       => ['\FolderGallery\FolderGallery',  'syncGalleryCategory'],
    'cleanup'    => ['\FolderGallery\FolderGallery', 'cleanup'],
];



