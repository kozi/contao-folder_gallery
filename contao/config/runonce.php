<?php
	
class FolderGalleryRunonceJob extends Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->import('Database');
	}
	 
	public function run()
	{

		if (!$this->Database->tableExists('tl_folder_gallery_category'))
		{
			return false;
		}

		if (!$this->Database->tableExists('tl_folder_gallery'))
		{
			return false;
		}

		if ($this->Database->fieldExists('uuid', 'tl_folder_gallery'))
		{
			return false;
		}

		// Change column root_folder in tl_folder_gallery_category
		$this->Database->execute("ALTER TABLE `tl_folder_gallery_category` ADD `root_folder_uuid` BINARY(16) NULL"); 
		$this->Database->execute("UPDATE tl_folder_gallery_category,tl_files SET root_folder_uuid = tl_files.uuid WHERE tl_folder_gallery_category.root_folder = tl_files.id");
        $this->Database->execute("ALTER TABLE `tl_folder_gallery_category` DROP `root_folder`");
        $this->Database->execute("ALTER TABLE `tl_folder_gallery_category` CHANGE `root_folder_uuid` `root_folder` BINARY( 16 ) NULL");
 

		// Add column uuid in tl_folder_gallery
		$this->Database->execute("ALTER TABLE `tl_folder_gallery` ADD `uuid` BINARY(16) NULL"); 
		$this->Database->execute("UPDATE tl_folder_gallery, tl_files SET tl_folder_gallery.uuid = tl_files.uuid WHERE tl_folder_gallery.folder = tl_files.id");		

		// Change column folder in tl_folder_gallery
		$this->Database->execute("ALTER TABLE `tl_folder_gallery` CHANGE `folder` `folder` varchar(255) NOT NULL default ''");
		$this->Database->execute("UPDATE tl_folder_gallery, tl_files SET tl_folder_gallery.folder = tl_files.path WHERE tl_folder_gallery.uuid = tl_files.uuid");

		// Change column poster_image in tl_folder_gallery
		$this->Database->execute("ALTER TABLE `tl_folder_gallery` ADD `poster_image_uuid` BINARY(16) NULL"); 
		$this->Database->execute("UPDATE tl_folder_gallery,tl_files SET poster_image_uuid = tl_files.uuid WHERE tl_folder_gallery_category.poster_image = tl_files.id");
        $this->Database->execute("ALTER TABLE `tl_folder_gallery` DROP `poster_image`");
        $this->Database->execute("ALTER TABLE `tl_folder_gallery` CHANGE `poster_image_uuid` `poster_image` BINARY( 16 ) NULL");

		
		// Change column alias in tl_folder_gallery
		$this->Database->execute("ALTER TABLE `tl_folder_gallery` CHANGE `alias` `alias` varbinary(128) NOT NULL default ''");

	}
}

$folderGalleryRunonceJob = new FolderGalleryRunonceJob();
$folderGalleryRunonceJob->run();
