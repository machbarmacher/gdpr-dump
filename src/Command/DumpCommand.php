<?php

namespace machbarmacher\GdprDump\Command;

use Ifsnop\Mysqldump\Mysqldump;
use machbarmacher\GdprDump\ConfigParser;
use machbarmacher\GdprDump\MysqldumpGdpr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command {
  protected function configure()
  {
    $this
      ->setName('mysqldump')
      ->setDescription('Dump a mysql database, with optionally sanitizing private data. See https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html')
      ->addArgument('db-name', InputArgument::REQUIRED, 'DB name.')
      ->addArgument('include-tables', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'Only include these tables, include all if empty')
      ->addOption('exclude-tables', NULL, InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'Exclude these tables, include all if empty, supports regexps')
      ->addOption('x-defaults', NULL, InputOption::VALUE_NONE, 'Implies --add-locks --disable-keys --extended-insert --hex-blob --no-autocommit --single-transaction.')
      ->addOption('result-file', 'r', InputOption::VALUE_OPTIONAL, 'Implies --add-locks --disable-keys --extended-insert --hex-blob --no-autocommit --single-transaction.')
      ->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'The connection user name.')
      ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'The connection password.')
      ->addOption('host', NULL, InputOption::VALUE_OPTIONAL, 'The connection host name.')
      ->addOption('port', 'P', InputOption::VALUE_OPTIONAL, 'The connection port number.')
      ->addOption('socket', 's', InputOption::VALUE_OPTIONAL, 'The connection socket.')
      ->addOption('db-type', NULL, InputOption::VALUE_OPTIONAL, 'The connection DB type. Options are: mysql (default), pgsql, sqlite, dblib.', 'mysql')
      ->addOption('defaults-file', NULL, InputOption::VALUE_OPTIONAL, 'An additional my.cnf file.')
      ->addOption('compress', NULL, InputOption::VALUE_OPTIONAL, 'Options: Gzip, Bzip2. Defaults to None.', 'None')
      ->addOption('init_commands', NULL, InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'DB Init commands.')
      ->addOption('no-data', NULL, InputOption::VALUE_NONE, 'Do not dump table contents.')
      ->addOption('reset-auto-increment', NULL, InputOption::VALUE_NONE, 'Removes the AUTO_INCREMENT option from the database definition. Useful when used with no-data, so when db is recreated, it will start from 1 instead of using an old value.')
      ->addOption('add-drop-database', NULL, InputOption::VALUE_NONE, 'Write a DROP DATABASE statement before each CREATE DATABASE statement. ')
      ->addOption('add-drop-table', NULL, InputOption::VALUE_NONE, 'Write a DROP TABLE statement before each CREATE TABLE statement.')
      ->addOption('add-drop-trigger', NULL, InputOption::VALUE_NONE, 'Write a DROP TRIGGER statement before each CREATE TRIGGER statement.')
      ->addOption('add-locks', NULL, InputOption::VALUE_NONE, 'Surround each table dump with LOCK TABLES and UNLOCK TABLES statements. This results in faster inserts when the dump file is reloaded.')
      ->addOption('complete-insert', NULL, InputOption::VALUE_NONE, 'Use complete INSERT statements that include column names.')
      ->addOption('default-character-set', NULL, InputOption::VALUE_OPTIONAL, 'Default charset. Defaults to utf8mb4.', 'utf8mb4')
      ->addOption('disable-keys', NULL, InputOption::VALUE_NONE, 'Adds disable-keys statements for faster dump execution. Defaults to on, use no-disable-keys to switch off.')
      ->addOption('extended-insert', 'e', InputOption::VALUE_NONE, 'Write INSERT statements using multiple-row syntax that includes several VALUES lists. This results in a smaller dump file and speeds up inserts when the file is reloaded. Defaults to on, use no-extended-insert to switch off.')
      ->addOption('events', NULL, InputOption::VALUE_NONE, 'Dump events from dumped databases	')
      ->addOption('hex-blob', NULL, InputOption::VALUE_NONE, 'Dump binary columns using hexadecimal notation.')
      ->addOption('net_buffer_length', NULL, InputOption::VALUE_OPTIONAL, 'Buffer size for TCP/IP and socket communication	')
      ->addOption('no-autocommit', NULL, InputOption::VALUE_NONE, 'Enclose the INSERT statements for each dumped table within SET autocommit = 0 and COMMIT statements.')
      ->addOption('no-create-info', NULL, InputOption::VALUE_NONE, 'Do not write CREATE DATABASE statements.')
      ->addOption('lock-tables', 'l', InputOption::VALUE_NONE, 'Lock all tables before dumping them.')
      ->addOption('routines', NULL, InputOption::VALUE_NONE, 'Dump stored routines (procedures and functions) from dumped databases.')
      ->addOption('single-transaction', NULL, InputOption::VALUE_NONE, 'Issue a BEGIN SQL statement before dumping data from server.')
      ->addOption('skip-triggers', NULL, InputOption::VALUE_NONE, 'Do not dump triggers.')
      ->addOption('skip-tz-utc', NULL, InputOption::VALUE_NONE, 'Turn off tz-utc.')
      ->addOption('skip-comments', NULL, InputOption::VALUE_NONE, 'Do not add comments to dump file.')
      ->addOption('skip-dump-date', NULL, InputOption::VALUE_NONE, 'Skip dump date to better compare dumps.')
      ->addOption('skip-definer', NULL, InputOption::VALUE_NONE, 'Omit DEFINER and SQL SECURITY clauses from the CREATE statements for views and stored programs.')
      ->addOption('where', NULL, InputOption::VALUE_OPTIONAL, 'Dump only rows selected by given WHERE condition.')
      ->addOption('gdpr-expressions', NULL, InputOption::VALUE_OPTIONAL, 'A json of gdpr sql-expressions keyed by table and column.')
      ->addOption('debug-sql', NULL, InputOption::VALUE_NONE, 'Add a comment with the dump sql.')
      // This seems NOT to work as documented.
      //->addOption('databases', NULL, InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY, 'Dump several databases. Normally, mysqldump treats the first name argument on the command line as a database name and following names as table names. With this option, it treats all name arguments as database names.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $user = $input->getOption('user');
    $password = $input->getOption('password');
    $dsn = $this->getDsn($input);
    $dumpSettings =
      $this->getXDefaults($input->getOption('x-defaults'))
      + $this->getDefaults($input->getOption('defaults-file'))
      + $input->getArguments()
      + $input->getOptions();
    if (!empty($dumpSettings['gdpr-expressions'])) {
      $dumpSettings['gdpr-expressions'] = json_decode($dumpSettings['gdpr-expressions'], TRUE);
    }
    $dumpSettings = array_intersect_key($dumpSettings, $this->getDumpSettingsDefault());
    $pdoSettings = [];
    $dumper = new MysqldumpGdpr($dsn, $user, $password, $dumpSettings, $pdoSettings);
    $dumper->start($input->getOption('result-file'));
  }

  protected function getDefaults($extraFile) {
    $defaultsFiles[] = '/etc/my.cnf';
    $defaultsFiles[] = '/etc/mysql/my.cnf';
    if ($extraFile) {
      $defaultsFiles[] = $extraFile;
    }
    if ($homeDir = getenv('MYSQL_HOME')) {
      $defaultsFiles[] = "$homeDir/.my.cnf";
      $defaultsFiles[] = "$homeDir/.mylogin.cnf";
    }

    $config = new ConfigParser();
    foreach ($defaultsFiles as $defaultsFile) {
      if (is_readable($defaultsFile)) {
        $config->addFile($defaultsFile);
      }
    }
    return $config->getFiltered(['client', 'mysqldump']);
  }

  protected function getDsn(InputInterface $input) {
    $dbName = $input->getArgument('db-name');
    $dbType = $input->getOption('db-type');
    $host = $input->getOption('host');
    $port = $input->getOption('port');
    $socket = $input->getOption('socket');
    $dsn = "$dbType:dbname=$dbName";
    if ($host) {
      $dsn .= ";host=$host";
    }
    if ($port) {
      $dsn .= ";port=$port";
    }
    if ($socket) {
      $dsn .= ";unix_socket=$socket";
    }
    return $dsn;
  }

  protected function getXDefaults($switch) {
    return !$switch ? [] : [
      'add-locks' => TRUE,
      'disable-keys' => TRUE,
      'extended-insert' => TRUE,
      'hex-blob' => TRUE,
      'no-autocommit' => TRUE,
      'single-transaction' => TRUE,
    ];
  }

  protected function getDumpSettingsDefault() {
    // Literal copy from \Ifsnop\Mysqldump\Mysqldump::__construct
    return array(
      'include-tables' => array(),
      'exclude-tables' => array(),
      'compress' => Mysqldump::NONE,
      'init_commands' => array(),
      'no-data' => array(),
      'reset-auto-increment' => FALSE,
      'add-drop-database' => FALSE,
      'add-drop-table' => FALSE,
      'add-drop-trigger' => TRUE,
      'add-locks' => TRUE,
      'complete-insert' => FALSE,
      'databases' => FALSE,
      'default-character-set' => Mysqldump::UTF8,
      'disable-keys' => TRUE,
      'extended-insert' => TRUE,
      'events' => FALSE,
      'hex-blob' => TRUE, /* faster than escaped content */
      'net_buffer_length' => 0,
      'no-autocommit' => TRUE,
      'no-create-info' => FALSE,
      'lock-tables' => TRUE,
      'routines' => FALSE,
      'single-transaction' => TRUE,
      'skip-triggers' => FALSE,
      'skip-tz-utc' => FALSE,
      'skip-comments' => FALSE,
      'skip-dump-date' => FALSE,
      'skip-definer' => FALSE,
      'where' => '',
      /* deprecated */
      'disable-foreign-keys-check' => TRUE
    ) + array(
        'gdpr-expressions' => NULL,
        'debug-sql' => FALSE,
    );
  }
}
