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
System::loadLanguageFile('tl_module');

$GLOBALS['TL_DCA']['tl_content']['palettes']['folder_gallery'] = '{type_legend},type,headline;{folder_gallery_legend},
    folder_gallery_category,folder_gallery_gallery,folder_gallery_gallery_pp,folder_gallery_gallery_limit,folder_gallery_gallery_template,folder_gallery_gallery_order,;
    {protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['folder_gallery_category'] = [
    'label' 		=> &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_category'],
    'exclude'		=> true,
    'inputType'		=> 'select',
    'foreignKey'    => 'tl_folder_gallery_category.title',
    'eval'			=> ['mandatory' => true , 'tl_class' => 'w50', 'submitOnChange' => true],
    'sql'           => "int(10) unsigned NOT NULL",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['folder_gallery_gallery'] = [
    'label' 		   => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery'],
    'exclude'		   => true,
    'inputType'		   => 'select',
    'options_callback' => ['tl_content_folder_gallery', 'getFolderGalleries'],
    'foreignKey'       => 'tl_folder_gallery.title',
    'eval'			   => ['mandatory' => true, 'tl_class'=>'w50'],
    'sql'              => "int(10) unsigned NOT NULL",
];



$GLOBALS['TL_DCA']['tl_content']['fields']['folder_gallery_gallery_pp'] = [
    'label'         => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_pp'],
    'exclude'       => true,
    'inputType'		=> 'text',
    'default'		=> 12,
    'eval'          => ['mandatory' => true, 'tl_class'=>'w50', 'rgxp' => 'digit'],
    'sql'           => "smallint(5) unsigned NOT NULL default '12'"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['folder_gallery_gallery_limit'] = [
    'label'         => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_limit'],
    'exclude'       => true,
    'inputType'		=> 'text',
    'default'		=> 0,
    'eval'          => ['mandatory' => true, 'tl_class'=>'w50', 'rgxp' => 'digit'],
    'sql'           => "smallint(5) unsigned NOT NULL default '0'"
];


$GLOBALS['TL_DCA']['tl_content']['fields']['folder_gallery_gallery_template'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_template'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'default'                 => 'fg_gallery_default',
    'options_callback'        => ['tl_content_folder_gallery', 'getFolderGalleryTemplatesGallery'],
    'eval'                    => ['mandatory' => true, 'tl_class'=>'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''",
];


$GLOBALS['TL_DCA']['tl_content']['fields']['folder_gallery_gallery_order'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_order'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'default'                 => 'name DESC',
    'options'                 => &$GLOBALS['TL_LANG']['tl_module']['folder_gallery_gallery_order_options'],
    'eval'                    => ['mandatory' => true, 'tl_class'=>'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''",
];

class tl_content_folder_gallery extends Backend
{
    public function getFolderGalleryTemplatesGallery(DataContainer $dc)
    {
        return Controller::getTemplateGroup('fg_gallery_');
    }

    public function getFolderGalleries(DataContainer $dc)
    {
        $result = $this->Database->prepare('SELECT * FROM tl_folder_gallery WHERE pid=? ORDER BY title ASC')
            ->execute($dc->activeRecord->folder_gallery_category);
        $galleries = [];
        while($result->next())
        {
            $row            = $result->row();
            $id             = $row['id'];
            $label          = $row['title'].' ('.Date::parse('F Y', $row['datim']).')';
            $galleries[$id] = $label;
        }
        return $galleries;

    }

}





