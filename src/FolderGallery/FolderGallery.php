<?php namespace FolderGallery;

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

use FolderGallery\Models\FolderGalleryModel;
use FolderGallery\Models\FolderGalleryCategoryModel;

class FolderGallery extends \System
{
    public function __construct()
    {
        parent::__construct();
        $this->import('Database');
    }

    public function cleanup(\DataContainer $dc) {

        // Sync file system
        \Dbafs::syncFiles();

        doCleanup();
    }

    public function syncGalleryCategory(\DataContainer $dc)
    {

        $catObj  = FolderGalleryCategoryModel::findByPk($dc->id);
        $rootObj = \FilesModel::findByUuid($catObj->root_folder);

        $existing_galleries = [];
        $result = $this->Database->prepare('SELECT * FROM tl_folder_gallery WHERE pid = ?')
            ->execute($catObj->id);

        while($result->next())
        {
            $existing_galleries[$result->folder] = $result->row();
        }

        $objSubfiles = \FilesModel::findMultipleByBasepath($rootObj->path.'/');

        while ($objSubfiles->next())
        {
            if (array_key_exists($objSubfiles->path, $existing_galleries))
            {
                unset($existing_galleries[$objSubfiles->path]);
            }
            else if ($objSubfiles->type === 'folder' && $this->hasImages($objSubfiles))
            {
                $alias    = standardize(\String::restoreBasicEntities($objSubfiles->name));
                $objAlias = $this->Database->prepare('SELECT id FROM tl_folder_gallery WHERE alias = ?')
                ->execute($alias);

                if ($objAlias->numRows === 1)
                {
                    $alias .= '-' . time();
                }

                $gal = [
                    'tstamp'   => time(),
                    'pid'      => $catObj->id,
                    'title'    => $objSubfiles->name,
                    'alias'    => $alias,
                    'folder'   => $objSubfiles->path,
                    'uuid'     => $objSubfiles->uuid,
                    'datim'    => $this->getDateFromImage($objSubfiles)
                ];
                $this->Database->prepare("INSERT INTO tl_folder_gallery %s")->set($gal)->execute();
            }
        }
        $this->cleanup($existing_galleries);

        \Controller::redirect(\Environment::get('script').'?do=folder_gallery&table=tl_folder_gallery&id='.$catObj->id);
    }

    private function hasImages($objFile) {

        if ($objFile->type === 'file')
        {
            return false;
        }
        $objChild  = \FilesModel::findMultipleFilesByFolder($objFile->path);
        if ($objChild === null)
        {
            return false;
        }

        while ($objChild->next())
        {
            if ($objChild->type === 'file')
            {
                $objFile = new \File($objChild->path, true);
                if ($objFile->isGdImage)
                {
                    return true;
                }
            }
        }
        return false;
    }

    private function getDateFromImage($objFile)
    {
        $withExif   = function_exists('exif_read_data');
        $objChild   = \FilesModel::findMultipleByBasepath($objFile->path);

        // Versuche es bei X Bildern die EXIF-Daten auszulesen
        $tryCount   = 3;

        if ($objChild === null)
        {
            return time();
        }

        while ($objChild->next())
        {
            if ($objChild->type === 'file' && $withExif)
            {
                $objFile = new \File($objChild->path, true);
                if ($objFile->isGdImage && $tryCount > 0)
                {
                    $tryCount--;
                    $exifData = exif_read_data(TL_ROOT.'/'.$objChild->path);
                    if ($exifData !== false && array_key_exists('DateTimeOriginal', $exifData))
                    {
                        return strtotime($exifData['DateTimeOriginal']);
                    }
                    return time();
                }
            }
        }
        return time();

    }

    private function doCleanup()
    {
        // Delete or move galleries
        $categories = [];
        $catObj     = FolderGalleryCategoryModel::findAll();
        while ($catObj->next())
        {
            $rootObj = \FilesModel::findByUuid($catObj->root_folder);
            $categories[$catObj->id] = $rootObj->path;
        }
        $galObj = FolderGalleryModel::findAll();
        if ($galObj === null)
        {
            return false;
        }

        while ($galObj->next())
        {
            $fileObj = \FilesModel::findByUuid($galObj->uuid);
            if ($galObj->folder != $fileObj->path)
            {
                //  echo '<pre>'.$galObj->folder.' != '.$fileObj->path.'<br></pre>';
                $moved = false;
                foreach($categories as $pid => $basepath)
                {
                    // var_dump($pid,$basepath, $fileObj->path, strpos($fileObj->path, $basepath));

                    if (strpos($fileObj->path, $basepath) === 0)
                    {
                        // MOVE
                        $moved = true;
                        $this->Database->prepare("UPDATE tl_folder_gallery
                        SET pid = ?, folder = ? WHERE id = ?")
                            ->execute($pid, $fileObj->path, $galObj->id);
                    }
                }
                if (!$moved)
                {
                    // DELETE
                    $this->Database->prepare("DELETE FROM tl_folder_gallery WHERE id = ?")
                        ->execute($galObj->id);
                }
            }
        }
    }
}