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

class FolderGalleryModule extends Module {
    private static $fileTypes  = " AND (tl_files.extension = 'png' OR tl_files.extension = 'jpg' OR tl_files.extension = 'jpeg' OR tl_files.extension = 'gif')";
    protected $strTemplate     = 'mod_folder_gallery';
    private   $jumpToRow       = null;

    public function generate() {
        global $objPage;

        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### FOLDER_GALLERY_MODULE ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }

        $this->jumpToRow = $objPage->row();
        if ($this->jumpTo !== 0) {
            $objJumpToPage = \PageModel::findPublishedById($this->jumpTo);
            if ($objJumpToPage !== null) {
                $this->jumpToRow = $objJumpToPage->row();
            }
        }

        return parent::generate();
    }
    protected function compile() {
        $this->gallery = \Input::get('gallery') ?: null;

        if ($this->gallery === null) {
            $this->categoryView();
        }
        else {
            $this->galleryView();
        }
    }

    private function categoryView() {
        $objTemplate   = new \FrontendTemplate($this->folder_gallery_category_template);
        $id            = 'page_fgc'.$this->id;
        $this->page    = \Input::get($id) ?: 0;

        $offset = ($this->page === 0) ? 0 : (($this->page-1) * $this->folder_gallery_category_pp);
        $total  = 0;
        $result = $this->Database->prepare('SELECT COUNT(*) AS count FROM tl_folder_gallery WHERE pid = ?')
            ->execute($this->folder_gallery_category);
        if ($result->numRows === 1) {
            $total = $result->count;
        }

        // Add the pagination menu
        $objPagination           = new \Pagination($total,
            $this->folder_gallery_category_pp, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id);
        $objTemplate->pagination = $objPagination->generate("\n  ");

        $orderBy = ' ORDER BY '.str_replace(array('_desc', '_asc','rand'), array(' DESC',' ASC','RAND()'),
            $this->folder_gallery_category_order);


        $limit = $this->folder_gallery_category_pp;
        if ($this->folder_gallery_category_limit != 0 && $this->folder_gallery_category_limit < $this->folder_gallery_category_pp) {
            // if limit is smaller then perPage set offset to zero and overwrite limit
            $limit  = $this->folder_gallery_category_limit;
            $offset = 0;
        }


        $result = $this->Database->prepare('SELECT * FROM tl_folder_gallery WHERE pid = ?'.$orderBy)
            ->limit($limit, $offset)
            ->execute($this->folder_gallery_category);
        $galleryArray = array();
        $i = 0;

        while($result->next()) {
            $gallery                 = $result->row();
            $gallery['date']         = date($GLOBALS['TL_CONFIG']['dateFormat'], $gallery['datim']);
            $gallery['link']         = $this->generateFrontendUrl($this->jumpToRow, '/gallery/'.$gallery['alias']);
            $gallery['cssId']        = 'gallery'.$gallery['id'];
            $gallery['cssClass']     = (($i %2 === 1) ? 'odd' : 'even').(($i == 0) ? ' first':'');

            $imgObj                  = \FilesModel::findByPk($gallery['poster_image']);
            $images                  = self::getImagesFromGallery($gallery);

            if (count($images) > 0) {
                $images_rand_index       = mt_rand(0, (count($images)-1));
                $gallery['images']       = $images;
                $gallery['poster_image'] = ($imgObj !== null) ? $imgObj->path : $images[$images_rand_index]['path'];
            }
            else {
                $gallery['images']       = array();
                $gallery['poster_image'] = '';
            }

            $galleryArray[] = $gallery;
            $i++;
        }
        $galleryArray[($i-1)]['cssClass'] .= ' last';

        $objTemplate->galleries  = $galleryArray;
        $this->Template->content = $objTemplate->parse();
    }

    private function galleryView() {
        $objTemplate   = new \FrontendTemplate($this->folder_gallery_gallery_template);
        $id            = 'page_fgg'.$this->id;
        $this->page    = \Input::get($id) ?: 0;


        $result = $this->Database->prepare('SELECT * FROM tl_folder_gallery WHERE alias = ?')
            ->execute($this->gallery);
        if($result->numRows !== 1) {
            $this->Template->content = 'ERROR '.$this->gallery.' not found.';
            return false;
        }

        $gallery          = $result->row();
        $gallery['date']  = date($GLOBALS['TL_CONFIG']['dateFormat'], $gallery['datim']);
        $gallery['link']  = $this->generateFrontendUrl($this->jumpToRow, '/gallery/'.$gallery['alias']);
        $imgObj           = \FilesModel::findByPk($gallery['poster_image']);


        $orderBy    = str_replace(array('_desc', '_asc','rand'), array(' DESC',' ASC','RAND()'),
            $this->folder_gallery_gallery_order);
        $offset     = ($this->page === 0) ? 0 : (($this->page-1) * $this->folder_gallery_gallery_pp);
        $total      = 0;

        $result = $this->Database->prepare('SELECT COUNT(*) AS count FROM tl_files WHERE tl_files.pid = ?'.static::$fileTypes)
            ->execute($gallery['folder']);
        if ($result->numRows === 1) {
            $total = $result->count;
        }

        // Add the pagination menu
        $objPagination           = new \Pagination($total,
            $this->folder_gallery_gallery_pp, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id);
        $objTemplate->pagination = $objPagination->generate("\n  ");


        $limit = $this->folder_gallery_gallery_pp;
        if ($this->folder_gallery_gallery_limit != 0 && $this->folder_gallery_gallery_limit < $this->folder_gallery_gallery_pp) {
            // if limit is smaller then perPage set offset to zero and overwrite limit
            $limit  = $this->folder_gallery_gallery_limit;
            $offset = 0;
        }

        $images     = self::getImagesFromGallery($gallery, $orderBy, $limit, $offset);

        if (count($images) > 0) {
            $images_rand_index       = mt_rand(0, (count($images)-1));
            $gallery['images']       = $images;
            $gallery['poster_image'] = ($imgObj !== null) ? $imgObj->path : $images[$images_rand_index]['path'];
        }
        else {
            $gallery['images']       = array();
            $gallery['poster_image'] = '';
        }

        $objTemplate->gallery    = $gallery;
        $this->Template->content = $objTemplate->parse();
    }

    public static function getImagesFromGallery($gallery, $sorting = 'name ASC', $limit = 0, $offset = 0) {

        $arrOptions = array(
		    'limit'  => $limit,
            'offset' => $offset,
            'order'  => $sorting,
            'column' => array("tl_files.pid = ?".self::$fileTypes)
        );

        $objChild = \FilesModel::findBy('pid', $gallery['folder'], $arrOptions);
        $images   = array();
        if ($objChild === null) {
            return $images;
        }
        $i = 0;
        while ($objChild->next()) {
            if ($objChild->type === 'file') {
                $objFile = new \File($objChild->path, true);
                if ($objFile->isGdImage) {
                    $img = array(
                        'cssId'      => 'image'.$i,
                        'cssClass'   => ($i %2 === 0) ? 'odd' : 'even',
                        'link'       => '',
                        'path'       => $objChild->path,
                        'attr_title' => '', // TODO title from metadata
                    );
                    $images[] = $img;
                    $i++;
                }
            }
        }
        return $images;
    }
}