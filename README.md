# GDPR Dump

A drop-in replacement for mysqldump that optionally sanitizes DB fields for better GDPR conformity.

It is based on the [ifsnop/mysqldump\-php](https://github.com/ifsnop/mysqldump-php) library, 
and can in principle dump any database that PDO supports. 

## How to use

There are presently two ways of manipulating data, 
the first is by manipulating the actual SQL queries that are run on the server (given by the gdpr-expressions path), 
and the second is by replacing column output before the dump is generated (given by the gdpr-replacements option).


```
$ ../vendor/bin/mysqldump drupal --host=mariadb --user=drupal --password=xxxxxxxx users_field_data --gdpr-expressions='{"users_field_data":{"name":"uid","mail":"uid","pass":"\"\""}}' --debug-sql
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
$ ../vendor/bin/mysqldump drupal --host=mariadb --user=drupal --password=xxxxxxxx users_field_data --debug-sql
...
--
-- Dumping data for table `users_field_data`
--

/* SELECT `uid`,`langcode`,`preferred_langcode`,`preferred_admin_langcode`,`name`,`pass`,`mail`,`timezone`,`status`,`created`,`changed`,`access`,`login`,`init`,`default_langcode` FROM `users_field_data` */

INSERT INTO `users_field_data` VALUES (0,'en','en',NULL,'',NULL,NULL,'',0,1523397207,1523397207,0,0,NULL,1);
INSERT INTO `users_field_data` VALUES (1,'en','en',NULL,'admin','$S$Eb6kZl.9OFjoa69Z05pzUhaZJ6vpKaGZVpnjAxxLJ7ip0zOwanEV','admin@example.com','UTC',1,1523397207,1523397207,0,0,'admin@example.com',1);
```

### Using gdpr-replacements

This uses [Faker](https://packagist.org/packages/fzaninotto/faker) for most of the column sanitization.

Presently, the tool searches for the "gdpr-replacements" option, either passed as a command line argument, or as part of a [MySql options file](https://dev.mysql.com/doc/refman/8.0/en/option-files.html).

The "gdpr-replacements" option expects a JSON string with the following format

```
{"tableName" : {"columnName1": {"formatter": "formatterType", ...}, {"columnName2": {"formatter": "formatterType"}, ...}, ...}
```
Where *formatterType* is one of the following
* **name** - generates a name
* **phoneNumber** - generates a phone number
* **username** - generates a random user name
* **password** - generates a random password
* **email** - generates a random email address
* **date** - generates a date
* **longText** - generates a sentence
* **number** - generates a number
* **randomText** - generates a sentence
* **text** - generates a paragraph
* **uri** - generates a URI
* **clear** - generates an empty string

This will replace the given column's value with Faker output.

You can also save replacements mapping to JSON file and use it with `--gdpr-replacements-file` option.

## Use with drush

As this mimicks mysqldump, it can be use with drush, backup_migrate and any tool that uses mysqldump.
Drush example:

```
$ export PATH=/var/www/html/vendor/bin:$PATH
$ which mysqldump
/var/www/html/vendor/bin/mysqldump
$ drush sql-dump --tables-list=users_field_data --extra-dump=$'--gdpr-expressions=\'{"users_field_data":{"name":"uid","mail":"uid","init":"uid","pass":"\\"\\""}}\' --debug-sql'
```

### MySqlOptions file

You are able to have your gdpr-expressions/replacement options set in a mysql options file file.
It is to appear under the `[mysqldump]` section.

So, for example, you might have `/etc/my.cnf` with the following content

```
[mysqldump]
gdpr-replacements='{"fakertest":{"name": {"formatter":"name"}, "telephone": {"formatter":"phoneNumber"}}}'

```

## Status and further development

Currently this is a proof of concept to spark a community process.
Especially the `--gdpr-expressions` option is neither handy to write for humans, nor does it scale well.
Here we might need better options.

## Contributors notes

* Note that the project follows [PSR-2](https://www.php-fig.org/psr/psr-2/) for formatting. 
