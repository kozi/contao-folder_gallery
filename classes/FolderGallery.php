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

class FolderGallery extends \System {

    public function __construct() {
        parent::__construct();
        $this->import('Database');
    }

    public function syncGalleryCategory(DataContainer $dc) {

        // Sync file system
        Dbafs::syncFiles();

        $existing_galleries = array();
        $cat_id             = $dc->id;

        $result = $this->Database->prepare('SELECT folder FROM tl_folder_gallery WHERE pid = ?')
            ->execute($cat_id);
        while($result->next()) {
            $existing_galleries[] = $result->folder;
        }

        $result = $this->Database
            ->prepare('SELECT tl_folder_gallery_category.root_folder AS id, tl_files.path AS path'
                .' FROM tl_files, tl_folder_gallery_category WHERE tl_folder_gallery_category.id = ?'
                .' AND tl_folder_gallery_category.root_folder = tl_files.id')
            ->execute($cat_id);
        if ($result->numRows !== 1) {
            return false;
        }

        $root        = $result->row();
        $objSubfiles = \FilesModel::findMultipleByBasepath($root['path'].'/');

        while ($objSubfiles->next()) {

            if ($objSubfiles->type === 'folder' && !in_array($objSubfiles->id, $existing_galleries)
                && $this->hasImages($objSubfiles->id) ) {

                $alias    = standardize(String::restoreBasicEntities($objSubfiles->name));
                $objAlias = $this->Database->prepare('SELECT id FROM tl_folder_gallery WHERE alias = ?')
                ->execute($alias);
                if ($objAlias->numRows === 1) {
                    $alias .= '-' . time();
                }

                $gal = array(
                    'tstamp'   => time(),
                    'pid'      => $cat_id,
                    'title'    => $objSubfiles->name,
                    'alias'    => $alias,
                    'folder'   => $objSubfiles->id,
                    'datim'    => $this->getDateFromImage($objSubfiles->id)
                );
                $this->Database->prepare("INSERT INTO tl_folder_gallery %s")->set($gal)->execute();
            }
        }

        $this->cleanup($cat_id, $root['path']);
        Controller::redirect(Environment::get('script').'?do=folder_gallery&table=tl_folder_gallery&id='.$cat_id);
    }

    private function hasImages($folderId) {
        $objChild   = \FilesModel::findByPid($folderId);
        if ($objChild === null) {
            return false;
        }

        while ($objChild->next()) {
            if ($objChild->type === 'file') {
                $objFile = new \File($objChild->path, true);
                if ($objFile->isGdImage) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getDateFromImage($folderId) {
        $withExif   = function_exists('exif_read_data');
        $objChild   = \FilesModel::findByPid($folderId);

        if ($objChild === null) {
            return time();
        }
        while ($objChild->next()) {
            if ($objChild->type === 'file' && $withExif) {
                $objFile = new \File($objChild->path, true);
                if ($objFile->isGdImage) {
                    $exifData = exif_read_data(TL_ROOT.'/'.$objChild->path);
                    if ($exifData !== false && array_key_exists('DateTimeOriginal', $exifData)) {
                        return strtotime($exifData['DateTimeOriginal']);
                    }
                    return time();
                }
            }
        }
        return time();

    }

    private function cleanup($cat_id, $basepath) {

        $basepath = $basepath.'/';

        $result   = $this->Database->prepare('SELECT tl_folder_gallery.id AS id, tl_folder_gallery.folder AS folderId,
            tl_files.path AS path FROM tl_folder_gallery
            LEFT JOIN tl_files
            ON tl_folder_gallery.folder = tl_files.id
            WHERE tl_folder_gallery.pid = ?')
                ->execute($cat_id);

        $deleteIds = array();

        while($result->next()) {
            $row             = $result->row();
            $row['basebath'] = $basepath;

            $func = sprintf('FolderGallery::cleanup(%s, %s)', $cat_id, $basepath);

            if (strpos($row['path'], $basepath) !== 0) {
                // TODO -- write log entry
                \System::log("GALLERY DELETED - WRONG BASEPATH -- ".print_r($row, true), $func,TL_GENERAL);
                $deleteIds[] = $row['id'];
            }
            else if(!is_dir(TL_ROOT . '/' . $row['path'])) {
                \System::log("GALLERY DELETED - NO DIR -- ".print_r($row, true), $func, TL_GENERAL);
                $deleteIds[] = $row['id'];
            }
            else if(!$this->hasImages($row['folderId'])) {
                \System::log("GALLERY DELETED - NO IMAGES -- ".print_r($row, true), $func, TL_GENERAL);
                $deleteIds[] = $row['id'];
            }
        }
        if (count($deleteIds) > 0) {
            $this->Database->execute('DELETE FROM tl_folder_gallery WHERE id in ('.implode(',', $deleteIds).')');
        }

    }
}