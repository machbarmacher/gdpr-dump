# GDPR Dump

A drop-in replacement for mysqldump that optionally sanitizes DB fields for better GDPR conformity.

It is based on the [ifsnop/mysqldump\-php](https://github.com/ifsnop/mysqldump-php) library, 
and can in principle dump any database that PDO supports. 

## How to use

```
$ ../vendor/bin/mysqldump drupal --host=mariadb --user=drupal --password=EEmnKMSWVS6dKCni users_field_data --gdpr-expressions='{"users_field_data":{"name":"uid","mail":"uid","pass":"\"\""}}' --debug-sql
...
--
-- Dumping data for table `users_field_data`
--

/* SELECT `uid`,`langcode`,`preferred_langcode`,`preferred_admin_langcode`,uid as name,"" as pass,uid as mail,`timezone`,`status`,`created`,`changed`,`access`,`login`,uid as init,`default_langcode` FROM `users_field_data` */

INSERT INTO `users_field_data` VALUES (0,'en','en',NULL,'0','','0','',0,1523397207,1523397207,0,0,'0',1);
INSERT INTO `users_field_data` VALUES (1,'en','en',NULL,'1','','1','UTC',1,1523397207,1523397207,0,0,'1',1);
```

The fields to obfuscate are passed via a `--gdpr-expressions` parameter.
Note that we use `uid` expression to satisfy unique keys.

The same without obfuscation:

```
$ ../vendor/bin/mysqldump drupal --host=mariadb --user=drupal --password=EEmnKMSWVS6dKCni users_field_data --debug-sql
...
--
-- Dumping data for table `users_field_data`
--

/* SELECT `uid`,`langcode`,`preferred_langcode`,`preferred_admin_langcode`,`name`,`pass`,`mail`,`timezone`,`status`,`created`,`changed`,`access`,`login`,`init`,`default_langcode` FROM `users_field_data` */

INSERT INTO `users_field_data` VALUES (0,'en','en',NULL,'',NULL,NULL,'',0,1523397207,1523397207,0,0,NULL,1);
INSERT INTO `users_field_data` VALUES (1,'en','en',NULL,'admin','$S$Eb6kZl.9OFjoa69Z05pzUhaZJ6vpKaGZVpnjAxxLJ7ip0zOwanEV','admin@example.com','UTC',1,1523397207,1523397207,0,0,'admin@example.com',1);
```
## Use with drush

As this mimicks mysqldump, it can be use with drush, backup_migrate and any tool that uses mysqldump.
Drush example:

```
$ export PATH=/var/www/html/vendor/bin:$PATH
$ which mysqldump
/var/www/html/vendor/bin/mysqldump
$ drush sql-dump --tables-list=users_field_data --extra-dump=$'--gdpr-expressions=\'{"users_field_data":{"name":"uid","mail":"uid","init":"uid","pass":"\\"\\""}}\' --debug-sql'
```

## Status and further development

Currently this is a proof of concept to spark a community process.
Especially the `--gdpr-expressions` option is neither handy to write for humans, nor does it scale well.
Here we might need better options.