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

$GLOBALS['TL_DCA']['tl_folder_gallery'] = [

// Config
    'config' => [
        'dataContainer'               => 'Table',
        'closed'                      => true,
        'notEditable'                 => false,
        'ptable'                      => 'tl_folder_gallery_category',
        'onload_callback' => [
            ['tl_folder_gallery', 'restrictToGalleryFolder']
        ],
        'sql' => [
            'keys' => ['id'  => 'primary', 'pid' => 'index']
        ]
    ],


// List
    'list' => [
        'sorting' => [
            'mode'                    => 2,
            'fields'                  => ['datim DESC'],
            'flag'                    => 1,
            'panelLayout'             => 'search, sort, limit',
            // 'child_record_callback'   => ['tl_folder_gallery', 'listGalleries']
        ],
        'label' => [
            'fields'                  => ['poster_image', 'title', 'folder', 'datim', 'details'],
            'showColumns'             => true,
            'label_callback'          => ['tl_folder_gallery', 'labelCallback']
        ],
        'global_operations' => [
            'cleanup' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery']['cleanup'],
                'href'                => 'key=cleanup',
                'class'               => 'header_cleanup',
            ],
            'sync' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery']['sync'],
                'href'                => 'key=sync',
                'class'               => 'header_sync',
            ],
            'all' => [
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ],
        ],
        'operations' => [
            'edit' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_folder_gallery']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif',
                'attributes'          => 'class="contextmenu"'
            ],
        ]
    ],

    // Palettes
    'palettes' => [
        'default'                     => '{title_legend}, folder, title, alias, datim, details, poster_image'
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid' => [
            'foreignKey'              => 'tl_folder_gallery_category.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => ['type'=>'belongsTo', 'load'=>'eager']
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
        ],
        'folder' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['folder'],
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>false, 'readonly' => true, 'tl_class'=>'long', 'disabled' => true],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'uuid' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['uuid'],
            'sql'                     => "binary(16) NULL",
        ],
        'title' => [
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'tl_class'=>'long'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['alias'],
            'exclude'                 => true,
            'search'                  => false,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'alias', 'unique'=>true, 'maxlength'=>128, 'tl_class'=>'w50'],
            'save_callback'           => [['tl_folder_gallery', 'generateAlias']],
            'sql'                     => "varbinary(128) NOT NULL default ''"
        ],
        'datim' => [
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery']['datim'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => true,
            'flag'                    => 8,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'datepicker'=>true, 'rgxp'=>'date', 'tl_class'=>'w50 wizard'],
            'sql'                     => "varchar(11) NOT NULL default ''"
        ],
        'details' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_folder_gallery']['details'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'		              => ['style' => 'height:64px;', 'mandatory'=>false],
            'sql'                     => "text NULL",
        ],
        'poster_image' => [
            'label'                   => $GLOBALS['TL_LANG']['tl_folder_gallery']['poster_image'],
            'exclude'                 => true,
            'search'                  => false,
            'sorting'                 => false,
            'flag'                    => 4,
            'inputType'               => 'fileTree',
            'eval'                    => ['mandatory'=>false, 'fieldType'=>'radio', 'files' => true, 'extensions' => 'png,jpg,jpeg,gif'],
            'sql'                     => "binary(16) NULL",
        ],

    ] //fields

];

class tl_folder_gallery extends Backend
{
    private $root_folder = null;

    public function labelCallback($row, $label, DataContainer $dc, $args = null) {

        $poster_path = 'system/modules/folder_gallery/assets/poster_default.png';
        $objFile     = \FilesModel::findByUuid($row['poster_image']);

        if($objFile !== null)
        {
            $poster_path = $objFile->path;
        }

        $args[0]    = sprintf('<img src="%s">', Image::get($poster_path, 64, 48, 'center_center'));
        $args[1]    = sprintf('%s <br><small>[%s]</small>', $row['title'], $row['alias']);

        if ($this->root_folder === null)
        {
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

    public function restrictToGalleryFolder(DataContainer $dc)
    {
        $result = $this->Database->prepare('SELECT tl_files.path AS path FROM tl_files, tl_folder_gallery'.
         ' WHERE tl_files.id = tl_folder_gallery.folder AND tl_folder_gallery.id = ?')->execute($dc->id);
         if ($result->numRows === 1)
         {
            $row  = $result->row();
            $GLOBALS['TL_DCA']['tl_folder_gallery']['fields']['poster_image']['eval']['path'] = $row['path'];
         }

    }

    public function generateAlias($varValue, DataContainer $dc) {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '')
        {
            $autoAlias = true;
            $varValue  = standardize(String::restoreBasicEntities($dc->activeRecord->title));
        }

        $objAlias = $this->Database->prepare('SELECT id FROM tl_folder_gallery WHERE alias = ?')
            ->execute($varValue);

        // Check whether the news alias exists
        if ($objAlias->numRows > 1 && !$autoAlias)
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias)
        {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }
}





