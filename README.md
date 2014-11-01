#MySQL Schematic
---

A MySQL schema generator in PHP define your schemas as json or yaml then run the script to generate your database or maintain it.


[![Build Status](https://travis-ci.org/andrefigueira/Schematic.svg?branch=master)](https://travis-ci.org/andrefigueira/Schematic)
[![Latest Stable Version](https://poser.pugx.org/mysql/schematic/v/stable.svg)](https://packagist.org/packages/mysql/schematic) [![Total Downloads](https://poser.pugx.org/mysql/schematic/downloads.svg)](https://packagist.org/packages/mysql/schematic) [![Latest Unstable Version](https://poser.pugx.org/mysql/schematic/v/unstable.svg)](https://packagist.org/packages/mysql/schematic) [![License](https://poser.pugx.org/mysql/schematic/license.svg)](https://packagist.org/packages/mysql/schematic)

###Install via Composer
---

    {
        require: {
            "mysql/schematic": "1.*"
        }
    }
    
###Install it Globally
---

- Clone it to your machine
- Then run cd to where you clone the repo
- Then run the following commands

    $ chmod +x schematic.phar
    
    $ mv schematic.phar /usr/bin/local/schematic
    
Schematic will now be available globally for you!

###Schema format
---

Schema is defined in schema files, these must be stored in the schema folder in json files representing the table they are
for, e.g. (Make sure that you create the schema folder in the root of your project)

	~/ProjectFolder/schemas/table_name.json

The schema file contains all of the configuration of the database in order to create it or amend it, see an example below.

In the current version you need to pass a type and null through always, you pass the length in parenthesis on the type field.

Schematic uses all of the base MySQL values so you just need to put them into this file and they will work, if you spell something incorrectly, it
will stop running and throw and exception.

###Example Schema
---

    {
        "schematic": {
            "name": "NAME OF THE SCHEMATIC",
            "version": "1.0"
        },
        "database": {
            "general": {
                "name": "schematic",
                "charset": "utf8",
                "collation": "utf8_general_ci",
                "engine": "InnoDB"
            },
            "tables": {
                "TABLENAMEHERE": {
                    "fields": {
                        "id": {
                            "type": "int(11)",
                            "null": false,
                            "unsigned": true,
                            "index": "PRIMARY KEY",
                            "autoIncrement": true,
                            "comment": "Id field for listing ids"
                        },
                        "name": {
                            "type": "varchar(256)",
                            "null": false
                        }
                        "productId": {
                            "type": "int(11)",
                            "null": false,
                            "unsigned": true,
                            "index": "INDEX",
                            "foreignKey": {
                                "table": "products",
                                "field": "id",
                                "on": {
                                    "delete": "CASCADE",
                                    "update": "CASCADE"
                                }
                            }
                        }
                    }
                }
            }
        }
    }

---

Note that currently foreign keys are only added, but not removed, If created a new database with constraints, you must run the update twice to add the constraints

#####Usage:

`php cli.php  [options] command [arguments]`

#####Options:

  `--help           -h Display this help message.`
  
  `--quiet          -q Do not output any message.`
  
  `--verbose        -v|vv|vvv Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug`
  
  `--version        -V Display this application version.`
  
  `--ansi              Force ANSI output.`
  
  `--no-ansi           Disable ANSI output.`
  
  `--no-interaction -n Do not ask any interactive question.`

#####Available commands:

  `help                  Displays help for a command`
  
  `list                  Lists commands`
  
#####migrations

  `migrations:execute    Executes the database migration based on the JSON schema files`
  
  `migrations:generate   Generates the database schema JSON files`
  
  `migrations:mapping    Generates the database schema based on an existing database`

The script also creates a log of all of the database changes which are made.

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/andrefigueira/schematic/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

