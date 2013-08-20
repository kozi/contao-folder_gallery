<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 *
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2013 <http://kozianka.de/>
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    folder_gallery
 * @license    LGPL
 * @filesource
 */



$GLOBALS['TL_DCA']['tl_module']['palettes']['folder_gallery'] = '{title_legend},name,headline,type;
    {folder_gallery_legend},folder_gallery_category,
    folder_gallery_category_template,folder_gallery_gallery_template,
    folder_gallery_category_pp,folder_gallery_gallery_pp,
    folder_gallery_gallery_limit,folder_gallery_category_limit,
    folder_gallery_category_order,folder_gallery_gallery_order;
    {redirect_legend},jumpTo;';

$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_category'] = array(
	'label' 		=> &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_category'],
	'exclude'		=> true,
	'inputType'		=> 'select',
    'foreignKey'    => 'tl_folder_gallery_category.title',
	'eval'			=> array('mandatory' => true),
	'sql'           => "int(10) unsigned NOT NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_category_pp'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_category_pp'],
    'exclude'       => true,
    'inputType'		=> 'text',
    'default'		=> 12,
    'eval'          => array('mandatory' => true, 'tl_class'=>'w50', 'rgxp' => 'digit'),
    'sql'           => "smallint(5) unsigned NOT NULL default '12'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_gallery_pp'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_pp'],
    'exclude'       => true,
    'inputType'		=> 'text',
    'default'		=> 12,
    'eval'          => array('mandatory' => true, 'tl_class'=>'w50', 'rgxp' => 'digit'),
    'sql'           => "smallint(5) unsigned NOT NULL default '12'"
);


$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_category_limit'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_category_limit'],
    'exclude'       => true,
    'inputType'		=> 'text',
    'default'		=> 0,
    'eval'          => array('mandatory' => true, 'tl_class'=>'w50', 'rgxp' => 'digit'),
    'sql'           => "smallint(5) unsigned NOT NULL default '0'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_gallery_limit'] = array(
    'label'         => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_limit'],
    'exclude'       => true,
    'inputType'		=> 'text',
    'default'		=> 0,
    'eval'          => array('mandatory' => true, 'tl_class'=>'w50', 'rgxp' => 'digit'),
    'sql'           => "smallint(5) unsigned NOT NULL default '0'"
);



$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_gallery_template'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_template'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'default'                 => 'fg_gallery_default',
    'options_callback'        => array('tl_module_folder_gallery', 'getFolderGalleryTemplatesGallery'),
    'eval'                    => array('mandatory' => true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_category_template'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_category_template'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'default'                 => 'fg_category_default',
    'options_callback'        => array('tl_module_folder_gallery', 'getFolderGalleryTemplatesCategory'),
    'eval'                    => array('mandatory' => true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_category_order'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_category_order'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'default'                 => 'name DESC',
    'options'                 => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_category_order_options'],
    'eval'                    => array('mandatory' => true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['folder_gallery_gallery_order'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_order'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'default'                 => 'name DESC',
    'options'                 => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_order_options'],
    'eval'                    => array('mandatory' => true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''",
);

class tl_module_folder_gallery extends Backend {

    public function getFolderGalleryTemplatesCategory(DataContainer $dc) {
        return Controller::getTemplateGroup('fg_category_');
    }

    public function getFolderGalleryTemplatesGallery(DataContainer $dc) {
        return Controller::getTemplateGroup('fg_gallery_');
    }

}

