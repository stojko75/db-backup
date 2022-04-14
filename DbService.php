<?php

namespace Stojko\DbService;

use DateTime;
use Exception;

class DbService
{
	private $serviceConfig;

	public function __construct(array $dbConfig)
	{
		$this->serviceConfig = new ServiceConfigDto();
		$this->serviceConfig->username  = $dbConfig['username'];
		$this->serviceConfig->password  = $dbConfig['password'];
		$this->serviceConfig->database  = $dbConfig['database'];
		$this->serviceConfig->hostname  = $dbConfig['hostname'];
		$this->serviceConfig->backupDir = $dbConfig['backupDir'];
		$this->serviceConfig->days      = $dbConfig['days'] ?? 14;
	}

	/**
	 * @throws Exception
	 */
	public function backupDb():void
	{
		$this->createBackupDirIfNotExist();
		$backupFilePath = $this->serviceConfig->backupDir . $this->serviceConfig->database.'_backup_'.(new \DateTime())->format('Ymd_his').'.sql';
		exec("mysqldump -u{$this->serviceConfig->username} -p{$this->serviceConfig->password} -h{$this->serviceConfig->hostname} {$this->serviceConfig->database} > {$backupFilePath}", $output);
		$this->deleteOldFiles();
	}

	private function createBackupDirIfNotExist(): void
	{
		if (!file_exists($this->serviceConfig->backupDir)) {
			mkdir($this->serviceConfig->backupDir);
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