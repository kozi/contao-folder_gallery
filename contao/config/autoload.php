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

ClassLoader::addClasses(array
(
    'FolderGallery'               => 'system/modules/folder_gallery/classes/FolderGallery.php',
	'FolderGalleryModule'         => 'system/modules/folder_gallery/modules/FolderGalleryModule.php',

    'FolderGalleryCategoryModel'  => 'system/modules/folder_gallery/models/FolderGalleryCategoryModel.php',
    'FolderGalleryModel'          => 'system/modules/folder_gallery/models/FolderGalleryModel.php',
));

TemplateLoader::addFiles(array
(
    'mod_folder_gallery'          => 'system/modules/folder_gallery/templates',
    'fg_category_default'         => 'system/modules/folder_gallery/templates',
    'fg_gallery_default'          => 'system/modules/folder_gallery/templates',
));
