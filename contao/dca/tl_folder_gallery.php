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

$GLOBALS['TL_DCA']['tl_folder_gallery'] = array(

// Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'closed'                      => true,
        'notEditable'                 => false,
        'ptable'                      => 'tl_folder_gallery_category',
        'onload_callback' => array
        (
            array('tl_folder_gallery', 'restrictToGalleryFolder')
        ),
        'sql' => array(
            'keys' => array
            (
                'id'  => 'primary',
                'pid' => 'index'
            )
        )
    ),


// List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('datim DESC'),
            'flag'                    => 1,
            'panelLayout'             => 'search, sort, limit',
            // 'child_record_callback'   => array('tl_folder_gallery', 'listGalleries')
        ),
        'label' => array
        (
            'fields'                  => array('poster_image', 'title', 'folder', 'datim', 'details'),
            'showColumns'             => true,
            'label_callback'          => array('tl_folder_gallery', 'labelCallback')
        ),
        'global_operations' => array
        (
            'sync' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery']['sync'],
                'href'                => 'key=sync',
                'class'               => 'header_sync',
            ),
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),

        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif',
                'attributes'          => 'class="contextmenu"'
            ),
        )

    ),



    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend}, folder, title, alias, datim, details, poster_image'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'foreignKey'              => 'tl_folder_gallery_category.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
        ),
        'folder' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['folder'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>false, 'readonly' => true, 'tl_class'=>'long', 'disabled' => true),
            'sql'                     => "varchar(255) NOT NULL default ''",
        ),
        'uuid' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['uuid'],
            'sql'                     => "binary(16) NULL",
        ),
        'title' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'tl_class'=>'long'),
            'sql'                     => "varchar(255) NOT NULL default ''",
        ),
        'alias' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['alias'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'alias', 'unique'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'save_callback' => array
            (
                array('tl_folder_gallery', 'generateAlias')
            ),
            'sql'                     => "varbinary(128) NOT NULL default ''"
        ),
        'datim' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery']['datim'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => true,
            'flag'                    => 8,
            'inputType'               => 'text',
            'eval'                    => array('mandatory' => true, 'datepicker'=>true, 'rgxp'=>'date', 'tl_class'=>'w50 wizard'),
            'sql'                     => "varchar(11) NOT NULL default ''"
        ),
        'details' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['details'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'		              => array('style' => 'height:64px;', 'mandatory'=>false),
            'sql'                     => "text NULL",
        ),
        'poster_image' => array
        (
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery']['poster_image'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 4,
            'inputType'               => 'fileTree',
            'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio', 'files' => true, 'extensions' => 'png,jpg,jpeg,gif'),
            'sql'                     => "binary(16) NULL",
        ),

    ) //fields

);

class tl_folder_gallery extends Backend {
    private $root_folder = null;

    public function __construct() {
        parent::__construct();

    }

    public function labelCallback($row, $label, DataContainer $dc, $args = null) {

        $poster_path = 'system/modules/folder_gallery/assets/poster_default.png';
        $objFile     = \FilesModel::findByUuid($row['poster_image']);
        if($objFile !== null) {
            $poster_path = $objFile->path;
        }

        $args[0]    = sprintf('<img src="%s">', Image::get($poster_path, 64, 48, 'center_center'));
        $args[1]    = sprintf('%s <br><small>[%s]</small>', $row['title'], $row['alias']);


        if ($this->root_folder === null) {
            $catObj  = \FolderGalleryCategoryModel::findByPk($row['pid']);
            $rootObj = \FilesModel::findByUuid($catObj->root_folder);
            $this->root_folder = $rootObj->path;
        }
        $args[2]    = str_replace($this->root_folder, '&hellip;', $row['folder']);

        $args[3]    = Date::parse('d.m.Y', $row['datim']);

        $details    = ($row['details']) ? String::substrHtml($row['details'], 32) : $args[4];
        $args[4]    = (strlen($row['details']) > 32)? $details.'&hellip;' : $details;

        return $args;
    }

    public function restrictToGalleryFolder(DataContainer $dc) {
        $result = $this->Database->prepare('SELECT tl_files.path AS path FROM tl_files, tl_folder_gallery'.
         ' WHERE tl_files.id = tl_folder_gallery.folder AND tl_folder_gallery.id = ?')->execute($dc->id);
         if ($result->numRows === 1) {
            $row  = $result->row();
            $GLOBALS['TL_DCA']['tl_folder_gallery']['fields']['poster_image']['eval']['path'] = $row['path'];
         }

    }

    public function generateAlias($varValue, DataContainer $dc) {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '') {
            $autoAlias = true;
            $varValue  = standardize(String::restoreBasicEntities($dc->activeRecord->title));
        }

        $objAlias = $this->Database->prepare('SELECT id FROM tl_folder_gallery WHERE alias = ?')
            ->execute($varValue);

        // Check whether the news alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }
}





