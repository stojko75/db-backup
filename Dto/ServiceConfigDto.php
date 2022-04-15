<?php
namespace Stojko\DbService\Dto;

class ServiceConfigDto
{
	/** @var string */
	public $username;

	/** @var string */
	public $password;

	/** @var string */
	public $hostname;

	/** @var string */
	public $database;

	/** @var string Full path of backup directory. Will be created, if it doesn't exist */
	public $backupDir;

	/** @var int How many days we want to keep backups */
	public $days;

	/** @var bool */
	public $bzip2;
}