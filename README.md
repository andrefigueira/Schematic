MySQL Schematic
=========

A MySQL schema generator in PHP define your schemas as JSON then run the script to generate your database or maintain it.

[![Build Status](https://travis-ci.org/andrefigueira/Schematic.svg?branch=master)](https://travis-ci.org/andrefigueira/Schematic)
[![Latest Stable Version](https://poser.pugx.org/mysql/schematic/v/stable.svg)](https://packagist.org/packages/mysql/schematic) [![Total Downloads](https://poser.pugx.org/mysql/schematic/downloads.svg)](https://packagist.org/packages/mysql/schematic) [![Latest Unstable Version](https://poser.pugx.org/mysql/schematic/v/unstable.svg)](https://packagist.org/packages/mysql/schematic) [![License](https://poser.pugx.org/mysql/schematic/license.svg)](https://packagist.org/packages/mysql/schematic)

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

###Note that currently foreign keys are only added, but not removed, If created a new database with constraints, you must run the update twice to add the constraints

#Running the update

Once you have everything set up in your schema files, open your command line, cd to the root of the schematic folder and type the following:

`php cli.php`

You could also consider creating an alias for it so that you don't need to type it every time that much, and also possibly clone the repo instead of including it as a dependency which would mean your alias could be a global one so you would use the exact same one everywhere.

This will execute the script, if there are any failures it will throw exception indicating what the errors are, on success it prints out a message indicating what it's done.

The script also creates a log of all of the database changes which are made.

#Reverse Engineering