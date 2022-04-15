Database backup library
=======================
Library for database backup. To be able to use this service, it's required to have mysqldump installed on server,
because this library is actually calling mysqldump under the hood. Also, php bzip2 extension needs to be enabled, if we 
want to compress sql file.

How to use
----------
Intended to be used as a service.

    use Stojko\DbService\DbService;

    $dbConfig = [
        'hostname'  => 'mysql',
        'username'  => 'root',
        'password'  => 'test23',
        'database'  => 'test_database',
        'backupDir' => getcwd().'/backup/', // Full path of backup directory. Will be created, if it doesn't exist
        'days'      => 14,                  // How many days we want to keep backups. Default is 14.
        'bzip2'     => true,                // Compress backup with bzip2 compression. Default is false.
    ];

    $dbService = new DbService($dbConfig);
    $dbService->backupDb();

