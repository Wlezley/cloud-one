<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Carbon\Carbon;

class StorageTree extends Storage
{
	/** @var Nette\Database\Explorer @inject */
	public $db;

	public int $tree_id;
	public int $parent_id;
	public int $owner_id;
	public string $name;
	public string $name_url;
	public Carbon $date_create;
	public Carbon $date_download;
	public Carbon $date_delete;
	public Carbon $date_modify;

	public bool $is_loaded;


	public function __construct(Nette\Database\Explorer $db)
	{
		$this->db = $db;
		//parent::__construct();

		$this->is_loaded = false;

		// if ($tree_id) {
		// 	$this->load($tree_id);
		// }

		$this->reset();
	}

	/** RESET folder data
	 * @return StorageTree
	 */
	public function reset(): StorageTree
	{
		$this->tree_id			= 0;
		$this->parent_id		= 0;
		$this->owner_id			= 0;
		$this->name				= "";
		$this->name_url			= "";
		$this->date_create		= Carbon::create();
		$this->date_download	= Carbon::create();
		$this->date_delete		= Carbon::create();
		$this->date_modify		= Carbon::create();
		$this->is_loaded		= false;

		return $this;
	}

	/** Load folder data
	 * @param int		$tree_id	ID of folder
	 * 
	 * @return StorageTree
	 */
	public function load(int $tree_id = 0): StorageTree
	{
		if ($tree_id == 0) {
			if (isset($this->tree_id)) {
				$tree_id = $this->tree_id;
			} else {
				return $this->reset();
			}
		}

		$row = $this->db->fetch("SELECT * FROM storage_tree WHERE tree_id = ?", $tree_id);

		if ($row) {
			$this->tree_id			= $row["tree_id"];
			$this->parent_id		= $row["parent_id"];
			$this->owner_id			= $row["owner_id"];
			$this->name				= $row["name"];
			$this->name_url			= $row["name_url"];
			$this->date_create		= Carbon::create($row["date_create"]->format("Y-m-d H:i:s.u"), "Europe/Prague");
			// $this->date_download	= Carbon::create($row["date_download"]->format("Y-m-d H:i:s.u"), "Europe/Prague");
			// $this->date_delete	= Carbon::create($row["date_delete"]->format("Y-m-d H:i:s.u"), "Europe/Prague");
			// $this->date_modify	= Carbon::create($row["date_modify"]->format("Y-m-d H:i:s.u"), "Europe/Prague");
			$this->is_loaded		= true;
		}

		return $this;
	}

	/** Save folder data
	 * @return bool
	 */
	public function save(): bool
	{
		$data = [
		//	"tree_id"		=> $tree_id === 0 ? null : $this->tree_id,			// INT(11) UNSIGNED, AUTO_INCREMENT
			"parent_id"		=> $this->parent_id,								// INT(11)
			"owner_id"		=> $this->owner_id,									// INT(11)
			"name"			=> $this->name,										// VARCHAR(255)
			"name_url"		=> \Nette\Utils\Strings::Webalize($this->name),		// VARCHAR(255)
			"date_create"	=> $this->date_create->format("Y-m-d H:i:s.u"),		// TIMESTAMP, DEFAULT current_timestamp()
			"date_download"	=> $this->date_download->format("Y-m-d H:i:s.u"),	// TIMESTAMP, DEFAULT NULL
			"date_delete"	=> $this->date_delete->format("Y-m-d H:i:s.u"),		// TIMESTAMP, DEFAULT NULL
			"date_modify"	=> $this->date_modify->format("Y-m-d H:i:s.u"),		// TIMESTAMP, ON UPDATE current_timestamp()
		];

		//bdump($data, "StorageTree::save() DATA");

		$result = $this->db->query("UPDATE storage_tree SET", $data, "WHERE tree_id = ?", $this->tree_id);

		return ($result->getRowCount() == 1);
	}

	/** Create new folder
	 * @todo VALIDATE INPUT DATA !!!
	 * 
	 * @param string	$name		New folder name
	 * @param int		$parent_id	ID of parrent folder (0 == ROOT folder)
	 * @param int		$owner_id	ID of user who own this folder
	 * 
	 * @return StorageTree			Returns tree_id of the new folder or null when error is occoured.
	 */
	public function create(string $name, int $parent_id = 0, int $owner_id = 0)
	{
		$data = [
			"parent_id"		=> $parent_id,								// INT(11)
			"owner_id"		=> $owner_id,								// INT(11)
			"name"			=> $name,									// VARCHAR(255)
			"name_url"		=> \Nette\Utils\Strings::Webalize($name),	// VARCHAR(255)
		];

		//bdump($data, "StorageTree::create(ARGS)");

		$result = $this->db->query("INSERT INTO storage_tree ?", $data);

		if ($result && $this->db->getInsertId()) {
			return $this->load((int)$this->db->getInsertId());
		}

		return $this;
	}

	/** Rename existing folder
	 * @param string	$name_new	New folder name
	 * 
	 * @return StorageTree|null
	 */
	public function rename(string $name_new): mixed
	{
		if (!$this->is_loaded || empty($name_new)) {
			return null;
		};

		$this->name = $name_new;
		$this->name_url = \Nette\Utils\Strings::Webalize($name_new);

		return $this->save() ? $this : null;
	}

	/** Delete existing folder
	 * @return StorageTree
	 */
	public function delete()
	{
		$this->db->query("DELETE FROM storage_tree WHERE tree_id = ?", $this->tree_id);
		$this->db->query("DELETE FROM storage_files WHERE tree_id = ?", $this->tree_id);

		return $this->reset();
	}

	// #######################################################################################################

	public function isLoaded(): bool
	{
		return $this->is_loaded;
	}

	public function getOwnerID(): int
	{
		return $this->owner_id;
	}

	public function getParentID(): int
	{
		return $this->parent_id;
	}

	public function getTreeID(): int
	{
		return $this->tree_id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getNameUrl(): string
	{
		if (empty($this->name_url)) {
			return \Nette\Utils\Strings::Webalize($this->name);
		}
		return $this->name_url;
	}

	// #######################################################################################################

	public function setOwnerID(int $owner_id, bool $save = false)
	{
		$this->owner_id = $owner_id;

		if ($save) {
			$this->save();
		}
	}

	public function setParentID(int $parent_id, bool $save = false)
	{
		$this->parent_id = $parent_id;

		if ($save) {
			$this->save();
		}
	}

	public function setTreeID(int $tree_id, bool $save = false)
	{
		$this->tree_id = $tree_id;

		if ($save) {
			$this->save();
		}
	}

	public function setName(string $name, bool $save = false)
	{
		$this->name = $name;
		$this->name_url = \Nette\Utils\Strings::Webalize($name);

		if ($save) {
			$this->save();
		}
	}

	// #######################################################################################################

	public function getPath() //: string
	{
		if (!$this->is_loaded && $this->owner_id == 0) {
			return false;
		}
		if ($this->tree_id == 0) {
			return "/";
		}

		$path = "/";
		$tree_id = $this->tree_id;

		do {
			$row = $this->db->fetch("SELECT parent_id, name_url FROM storage_tree WHERE tree_id = ? AND owner_id = ?", $tree_id, $this->owner_id);

			if (!$row || empty($row["name_url"])) {
				return false;
			}

			$path = "/" . $row["name_url"] . $path;
			$tree_id = $row["parent_id"];
		}
		while ($row["parent_id"] != 0 && $row);

		return $path;
	}

	public function getPathByTreeId(int $tree_id) //: string // TODO: $owner_id !!!
	{
		$path = "/"; // Default return

		if ($tree_id != 0) {
			do {
				$row = $this->db->fetch("SELECT parent_id, name_url FROM storage_tree WHERE tree_id = ?", $tree_id);

				if (!$row || empty($row["name_url"])) {
					return false;
				}

				$path = "/" . $row["name_url"] . $path;
				$tree_id = $row["parent_id"];
			}
			while ($row["parent_id"] != 0 && $row);
		}

		return $path;
	}

	public function getTreeIdByPath(string $path, int $owner_id) //: string // TODO: $owner_id !!!
	{
		$pathArray = explode("/", trim($path, "/"));
		bdump($pathArray, "URL / PATH ARRAY (StorageTree)");

		$owner_id = 1;
		$parent_id = 0;

		$treeMap = [];
		$lastPath = "";
		foreach ($pathArray as $key => $name_url) {
			$folder = $this->db->query('SELECT * FROM storage_tree WHERE owner_id = ? AND parent_id = ? AND name_url = ? LIMIT 1', $owner_id, $parent_id, $name_url);
			$folderInfo = $folder->fetch();

			if (!$folderInfo) {
				if (!empty($lastPath)) {
					return 0; // root directory
				}
				break;
			}

			$treeMap[$key] = [
				"parent_id" => $parent_id,
				"tree_id" => $folderInfo['tree_id'],
			];

			$lastPath .= $folderInfo['name_url'] . "/";
			$parent_id = $folderInfo['tree_id'];
		}
		bdump($treeMap, "URL / TREE MAP (StorageTree)");

		return $parent_id;
	}

	/** List of sub-folders in the folder */
	public function getTreeList(): array
	{
		if (!$this->is_loaded && $this->owner_id == 0) {
			return [];
		}

		$result = $this->db->query("SELECT * FROM storage_tree WHERE parent_id = ? AND owner_id = ?", $this->tree_id, $this->owner_id);

		$treeList = [];
		foreach ($result as $key => $folder) {
			$treeList[$key] = (array)$folder;
			$treeList[$key]['full_path'] = $this->getPathByTreeId($folder['tree_id']);
		}

		return $treeList;
	}

	/** List of files in the folder */
	public function getFileList(): array
	{
		if (!$this->is_loaded && $this->owner_id == 0) {
			return [];
		}

		$result = $this->db->query("SELECT * FROM storage_files WHERE tree_id = ? AND owner_id = ? ORDER BY fileName ASC", $this->tree_id, $this->owner_id);

		$fileList = [];
		foreach ($result as $key => $file) {
			$fileList[$key] = (array)$file;
			$fileList[$key]['tree_path'] = $this->getPathByTreeId($file['tree_id']);
		}

		return $fileList;
	}
}

/*
CREATE TABLE `storage_tree` (
	`tree_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`parent_id` INT(11) NOT NULL DEFAULT '0',
	`owner_id` INT(11) NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`name_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`date_create` TIMESTAMP NOT NULL DEFAULT current_timestamp() COMMENT 'Datum vytvoreni',
	`date_download` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum stazeni',
	`date_delete` TIMESTAMP NULL DEFAULT NULL COMMENT 'Datum smazani',
	`date_modify` TIMESTAMP NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Datum zmeny',
	PRIMARY KEY (`tree_id`) USING BTREE,
	UNIQUE INDEX `parent_id_owner_id_name_url` (`parent_id`, `owner_id`, `name_url`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
*/
