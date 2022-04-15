<?php

namespace Stojko\DbService;

use DateTime;
use Exception;
use Stojko\DbService\Dto\BackupFileDto;
use Stojko\DbService\Dto\ServiceConfigDto;

class DbService
{
	/** @var ServiceConfigDto  */
	private $serviceConfig;

	/** @var BackupFileDto */
	private $backupFile;

	public function __construct(array $dbConfig)
	{
		$this->serviceConfig = new ServiceConfigDto();
		$this->serviceConfig->username  = $dbConfig['username'];
		$this->serviceConfig->password  = $dbConfig['password'];
		$this->serviceConfig->database  = $dbConfig['database'];
		$this->serviceConfig->hostname  = $dbConfig['hostname'];
		$this->serviceConfig->backupDir = $dbConfig['backupDir'];
		$this->serviceConfig->days      = $dbConfig['days'] ?? 14;
		$this->serviceConfig->bzip2     = $dbConfig['bzip2'];
		$this->backupFile = new BackupFileDto();
	}

	/**
	 * @throws Exception
	 */
	public function backupDb():void
	{
		$this->createBackupDirIfNotExist();
		$this->backup();
		$this->bzip2();
		$this->deleteOldFiles();
	}

	private function createBackupDirIfNotExist(): void
	{
		if (!file_exists($this->serviceConfig->backupDir)) {
			mkdir($this->serviceConfig->backupDir);
		}
	}

	private function backup(): void
	{
		$this->backupFile->name = $this->serviceConfig->database.'_backup_'.(new DateTime())->format('Ymd_his').'.sql';
		$this->backupFile->fullPath = $this->serviceConfig->backupDir . $this->backupFile->name;
		exec("mysqldump -u{$this->serviceConfig->username} -p{$this->serviceConfig->password} -h{$this->serviceConfig->hostname} {$this->serviceConfig->database} > {$this->backupFile->fullPath}", $output);
	}

	private function bzip2(): void
	{
		if ($this->serviceConfig->bzip2 === true) {
			$bzipFilePath = $this->backupFile->fullPath.'.bz2';
			$bz = bzopen($bzipFilePath, "w") or die("Couldn't open $bzipFilePath for writing!");
			$sqlFile = fopen($this->backupFile->fullPath, "r") or die("Can't open backup file {$this->backupFile->fullPath} for reading!");
			while (($line = fgets($sqlFile, 10240)) !== false) {
				bzwrite($bz, $line);
			}
			if (!feof($sqlFile)) {
				echo "Error: it should be eof detected but it isn't ?! Unexpected fgets() fail!";
			}
			fclose($sqlFile);
			bzclose($bz);
			unlink($this->backupFile->fullPath);
		}
	}

	/**
	 * @throws Exception
	 */
	private function deleteOldFiles(): void
	{
		$lastDayForKeep = new DateTime();
		$lastDayForKeep->modify("-".$this->serviceConfig->days.' day');
		$files = scandir($this->serviceConfig->backupDir);
		foreach ($files as $key => $value) {
			if ($value === '.' || $value === '..') {
				unset($files[$key]);
				continue;
			}
			$dt = new DateTime();
			$dt->setTimestamp(filemtime($this->serviceConfig->backupDir.$value));
			if ($dt < $lastDayForKeep) {
				unlink($this->serviceConfig->backupDir.$value);
			}
		}
	}
}