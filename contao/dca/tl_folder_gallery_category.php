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

$GLOBALS['TL_DCA']['tl_folder_gallery_category'] = array(

// Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'closed'                      => false,
        'ctable'                      => array('tl_folder_gallery'),
        'notEditable'                 => false,
        'sql' => array(
            'keys' => array('id' => 'primary')
        )
    ),


    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('title ASC'),
            'flag'                    => 1,
            'panelLayout'             => 'filter, search, limit'
        ),

        'label' => array
        (
            'fields'                  => array('title', 'root_folder'),
            'showColumns'             => true,
            'label_callback'          => array('tl_folder_gallery_category', 'labelCallback')
        ),
        'operations' => array
        (
            'entries' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery_category']['galleries'],
                'href'                => 'table=tl_folder_gallery',
                'icon'                => 'tablewizard.gif'
            ),
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery_category']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'header.gif',
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery_category']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_folder_gallery_category']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            )
        )

    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend}, title, root_folder'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
        ),
        'title' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery_category']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
            'sql'                     => "varchar(255) NOT NULL default ''",
        ),

        'root_folder' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery_category']['root_folder'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => false,
            'flag'                    => 4,
            'inputType'               => 'fileTree',
            'eval'                    => array('mandatory'=>true, 'fieldType'=>'radio', 'files' => false),
            'sql'                     => "binary(16) NULL",
        ),

    ) //fields

);

class tl_folder_gallery_category extends Backend {

    public function __construct() {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function labelCallback($row, $label, DataContainer $dc, $args = null) {
        if ($args === null) {
            return $label;
        }
        $objFile  = \FilesModel::findByUuid($row['root_folder']);
        $args[1]  = ($objFile !== null) ? $objFile->path : $args[1];
        return $args;
    }

}

