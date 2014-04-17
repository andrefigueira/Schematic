MySQL Schematic
=========

A MySQL schema generator in PHP define your schemas as JSON then run the script to generate your database or maintain it.

[![Build Status](https://travis-ci.org/andrefigueira/Schematic.svg?branch=master)](https://travis-ci.org/andrefigueira/Schematic)

#Install via Composer

    {
        require: {
            "mysql/schematic": "1.*"
        }
    }

#Schema format

Schema is defined in JSON files, these must be stored in the schema folder in json files representing the table they are
for, e.g. (Make sure that you create the schema folder in the root of your project)

`/schemas/schema.json`

The schema file contains all of the configuration of the database in order to create it or amend it, see an example below.

In the current version you need to pass a type and null through always, you pass the length in parenthesis on the type field.

Schematic uses all of the base MySQL values so you just need to put them into this file and they will work, if you spell something incorrectly, it
will stop running and throw and exception.

#Example Schema

    {
        "schematic": {
            "name": "NAME OF THE SCHEMATIC",
            "version": "1.0"
        },
        "connection": {
            "host": "127.0.0.1",
            "user": "root",
            "pass": ""
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

###Note that foreign keys support is only available when first running the script to initially create your table

#Running the update

Once you have everything set up in your schema files, open your command line, cd to the root of the schematic folder and type the following:

`php vendor/mysql/schematic/cli.php`

If you set the php file to executable with:

`chmod +x vendor/mysql/schematic/cli.php`

Then you should be able to run the update with:

`./vendor/mysql/schematic/cli.php`

You could also consider creating an alias for it so that you don't need to type it every time that much, and also possibly clone the repo instead of including it as a dependency which would mean your alias could be a global one so you would use the exact same one everywhere.

This will execute the script, if there are any failures it will throw exception indicating what the errors are, on success it prints out a message indicating what it's done.

The script also creates a log of all of the database changes which are made.

Script options:

- `-r` Runs the MySQL Schematic exporter, creates the database or runs the updates based on the JSON schemas defined
- `-v` Prints the version of MySQL Schematic currently in use
- `-t` Creates a new schema.json template in your schema directory e.g. `php vendor/mysql/schematic/cli.php -t products`
- `-h` Shows the help menu