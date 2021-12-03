<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;

class StorageTree extends Storage
{
	public int $tree_id;			// TODO: Rename "treeID"	>>	"tree_id"
	public int $parent_id;			// TODO: Rename "parentID"	>>	"parent_id"
	public int $owner_id;			// TODO: Rename "ownerID"	>>	"owner_id"

	public string $name;
	public string $name_url;		// TODO: Rename "nameUrl"	>>	"name_url"

	public Carbon $date_create;
	public Carbon $date_download;
	public Carbon $date_delete;
	public Carbon $date_modify;


	public function __construct()
	{
		parent::__construct();
	}

	public function load(int $tree_id)
	{
		$this->tree_id = $tree_id;

		// TODO: Load process here

		return $this->tree_id;
	}

	public function save()
	{
		// TODO: Save process here

		// TABLE STRUCTURE:
		/*$createFolderTable = [
		//	"treeID"		=> null,		// INT(11) UNSIGNED, AUTO_INCREMENT
			"parentID"		=> $parentId,	// INT(11)
			"ownerID"		=> $ownerId,	// INT(11)
			"name"			=> $name,		// VARCHAR(255)
			"nameUrl"		=> \Nette\Utils\Strings::Webalize($name),	// VARCHAR(255)
		//	"date_create"	=> null,		// TIMESTAMP, DEFAULT current_timestamp()
		//	"date_download"	=> null,		// TIMESTAMP, DEFAULT NULL
		//	"date_delete"	=> null,		// TIMESTAMP, DEFAULT NULL
		//	"date_modify"	=> null,		// TIMESTAMP, ON UPDATE current_timestamp()
		];*/

		return $this->tree_id;
	}

	/**
	 * Creates a folder
	 * @param string	$name		New folder name
	 * @param int		$parentId	ID of parrent folder
	 * @param int		$ownerId	ID of user who own this folder
	 * 
	 * @return string|null			Returns treeID of the new folder or null when error is occoured.
	 */
	public function createFolder(string $name, int $parentId = 0, int $ownerId = 0)
	{
		bdump([
			"name" => $name,
			"parentId" => $parentId,
			"ownerId" => $ownerId,
		], "StorageTree::createFolder");

		// TODO: VALIDATE INPUT DATA HERE!!!

		$return = $this->db->query("INSERT INTO storage_tree ?", [
			//	"treeID"		=> null,		// INT(11) UNSIGNED, AUTO_INCREMENT
				"parentID"		=> $parentId,	// INT(11)
				"ownerID"		=> $ownerId,	// INT(11)
				"name"			=> $name,		// VARCHAR(255)
				"nameUrl"		=> \Nette\Utils\Strings::Webalize($name),	// VARCHAR(255)
			//	"date_create"	=> null,		// TIMESTAMP, DEFAULT current_timestamp()
			//	"date_download"	=> null,		// TIMESTAMP, DEFAULT NULL
			//	"date_delete"	=> null,		// TIMESTAMP, DEFAULT NULL
			//	"date_modify"	=> null,		// TIMESTAMP, ON UPDATE current_timestamp()
			]);

		return ($return ? $this->db->getInsertId() : null);
	}

	public function renameFolder(int $treeID, string $name) //, int $parentId = 0, int $ownerId = null)
	{
		return true;
	}

	public function deleteFolder(string $name, int $parentId = 0, int $ownerId = null)
	{
		return true;
	}

	public function getFolderOwnerID(string $treeID)
	{
		return null;
	}

	public function getFolderParentID(string $treeID)
	{
		return null;
	}

	public function getFolderTreeID(string $name, int $ownerId = null)
	{
		return null;
	}

	public function getFolderName(string $name, int $parentId = 0, int $ownerId = null): string
	{
		return "null";
	}

	public function getFolderPath(string $name, int $parentId = 0, int $ownerId = null): string
	{
		return "/folder/path/is/this";
	}

	public function getFolderUrl(string $name, int $parentId = 0, int $ownerId = null): string
	{
		return "/folder/path/is/this";
	}

}

/*
CREATE TABLE `storage_tree` (
	`treeID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parentID` INT(11) NOT NULL DEFAULT '0',
	`ownerID` INT(11) NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`nameUrl` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`date_create` TIMESTAMP NOT NULL DEFAULT current_timestamp() COMMENT 'Datum vytvoreni',
	`date_download` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum stazeni',
	`date_delete` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum smazani',
	`date_modify` TIMESTAMP NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Datum zmeny',
	PRIMARY KEY (`treeID`) USING BTREE,
	UNIQUE INDEX `parentID_ownerID_nameUrl` (`parentID`, `ownerID`, `nameUrl`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
*/