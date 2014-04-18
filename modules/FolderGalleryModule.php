<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2013-2014 Leo Feyer
 *
 *
 * PHP version 5
 * @copyright  Martin Kozianka 2013-2014 <http://kozianka.de/>
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    folder_gallery
 * @license    LGPL
 * @filesource
 */

class FolderGalleryModule extends Module {
    public static $sorting        = array('title_asc', 'title_desc', 'datim_asc', 'datim_desc', 'rand');
    private static $fileTypes     = " (tl_files.extension = 'png' OR tl_files.extension = 'jpg' OR tl_files.extension = 'jpeg' OR tl_files.extension = 'gif')";
    private static $filesInFolder = " tl_files.type='file' AND tl_files.path LIKE ? AND tl_files.path NOT LIKE ?";
    protected $strTemplate        = 'mod_folder_gallery';
    private   $jumpToRow          = null;

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


        $order   = $this->folder_gallery_category_order;
        $order   = str_replace('datim', '(datim+0)', $order);
        $order   = str_replace(array('_desc', '_asc','rand'), array(' DESC',' ASC','RAND()'), $order);
        $order   = str_replace(array('_desc', '_asc','rand'), array(' DESC',' ASC','RAND()'), $order);
        $orderBy = ' ORDER BY '.$order;

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

            $imgObj                  = \FilesModel::findByUuid($gallery['poster_image']);
            $images                  = self::getImagesFromGallery($gallery);
            $imgCount                = count($images);
            if ($imgCount > 0) {
                $images_rand_index       = mt_rand(0, ($imgCount-1));
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
        $imgObj           = \FilesModel::findByUuid($gallery['poster_image']);


        $order   = $this->folder_gallery_gallery_order;
        $order   = str_replace('datim', 'tstamp', $order);
        $orderBy = str_replace(array('_desc', '_asc','rand'), array(' DESC',' ASC','RAND()'), $order);


        $offset     = ($this->page === 0) ? 0 : (($this->page-1) * $this->folder_gallery_gallery_pp);
        $total      = 0;



        $arrOptions = array('column' => array(self::$filesInFolder.' AND '.self::$fileTypes));
        $objChild   = \FilesModel::findMultipleFilesByFolder($gallery['folder'], $arrOptions);
        if ($objChild !== null) {
            $total = $objChild->count();
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
            'column' => array(self::$filesInFolder.' AND '.self::$fileTypes)
        );
        $objChild = \FilesModel::findMultipleFilesByFolder($gallery['folder'], $arrOptions);

        $images   = array();
        if ($objChild === null) {
            return $images;
        }
        $i = 0;
        while ($objChild->next()) {
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
        return $images;
    }
}