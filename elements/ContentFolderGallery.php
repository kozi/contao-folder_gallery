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


/**
 * Class ContentFolderGallery
 *
 * Front end content element "folder_gallery".
 * @copyright  Martin Kozianka 2013 <http://kozianka.de/>
 * @author     Martin Kozianka <http://kozianka.de/>
 * @package    folder_gallery
 */
class ContentFolderGallery extends ContentElement {
	protected $strTemplate = 'ce_folder_gallery';

    public function generate() {
		return parent::generate();
	}

	protected function compile() {
		global $objPage;

        $strTemplate          = 'fg_gallery_default';
        $objTemplate          = new \FrontendTemplate($strTemplate);
        $objTemplate->gallery = array();

        $objTemplate->gallery['details'] = 'DETAILS';

        $this->Template->headline = 'HOHOHO'.$this->headline; // see #1603
        $this->Template->content  = $objTemplate->parse();
	}
}
