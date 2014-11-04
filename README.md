   _____      __                         __  _     
  / ___/_____/ /_  ___  ____ ___  ____ _/ /_(______
  \__ \/ ___/ __ \/ _ \/ __ `__ \/ __ `/ __/ / ___/
 ___/ / /__/ / / /  __/ / / / / / /_/ / /_/ / /__  
/____/\___/_/ /_/\___/_/ /_/ /_/\__,_/\__/_/\___/  
                                                   
                                        

A database migrations tool, allows for easy database maintenance in a way that is easy to setup in a continuous integration environment.

[![Build Status](https://travis-ci.org/andrefigueira/Schematic.svg?branch=master)](https://travis-ci.org/andrefigueira/Schematic)
[![Latest Stable Version](https://poser.pugx.org/mysql/schematic/v/stable.svg)](https://packagist.org/packages/mysql/schematic) [![Total Downloads](https://poser.pugx.org/mysql/schematic/downloads.svg)](https://packagist.org/packages/mysql/schematic) [![Latest Unstable Version](https://poser.pugx.org/mysql/schematic/v/unstable.svg)](https://packagist.org/packages/mysql/schematic) [![License](https://poser.pugx.org/mysql/schematic/license.svg)](https://packagist.org/packages/mysql/schematic)
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/andrefigueira/schematic/trend.png)](https://bitdeli.com/free "Bitdeli Badge")
###Install locally via Composer
---

    {
        require: {
            "mysql/schematic": "1.*.*"
        }
    }
    
###Install it Globally
---

- Run the following commands:

Download the PHAR file:

    $ wget https://github.com/andrefigueira/Schematic/blob/master/schematic.phar

Make the PHAR package executable

    $ chmod +x schematic.phar
    
Move it to the user bin folder
    
    $ mv schematic.phar /usr/bin/local/schematic
    
Then use Schematic

    $ schematic
    
Schematic will now be available globally for you!

###Schema format
---

Schema is defined in schema files, these must be stored in the schema folder in json files representing the table they are
for, e.g. (Make sure that you create the schema folder in the root of your project)

	~/ProjectFolder/schemas/table_name.yaml

The schema file contains all of the configuration of the database in order to create it or amend it, see an example below.

In the current version you need to pass a type and null through always, you pass the length in parenthesis on the type field.

Schematic uses all of the base MySQL values so you just need to put them into this file and they will work, if you spell something incorrectly, it
will stop running and throw and exception.

###Example Schema
---

    schematic:
        name: Schematic
        version: 1.4.5
    database:
        general:
            name: schematic
            charset: utf8
            collation: utf8_general_ci
            engine: InnoDB
        tables:
            hello_world:
                fields:
                    id:
                        type: int(11)
                        'null': false
                        unsigned: true
                        autoIncrement: true
                        index: 'PRIMARY KEY'
                    client_id:
                        type: int(24)
                        'null': false
                        unsigned: true
                        autoIncrement: false
                    name:
                        type: varchar(128)
                        'null': false
                        unsigned: false
                        autoIncrement: false
                        rename: full_name
                    description:
                        type: varchar(256)
                        'null': false
                        unsigned: false
                        autoIncrement: false
                    created_date:
                        type: datetime
                        'null': false
                        unsigned: false
                        autoIncrement: false


---

Note that currently foreign keys are only added, but not removed, If created a new database with constraints, you must run the update twice to add the constraints.

##### Usage:

`schematic  [options] command [arguments]`

##### Options:

  `--help           -h Display this help message.`
  
  `--quiet          -q Do not output any message.`
  
  `--verbose        -v|vv|vvv Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug`
  
  `--version        -V Display this application version.`
  
  `--ansi              Force ANSI output.`
  
  `--no-ansi           Disable ANSI output.`
  
  `--no-interaction -n Do not ask any interactive question.`

##### Available commands:

  `help                  Displays help for a command`
  
  `list                  Lists commands`
  
##### Migrations

  `migrations:execute    Executes the database migration based on the JSON schema files`
  
  `migrations:generate   Generates the database schema JSON files`
  
  `migrations:mapping    Generates the database schema based on an existing database`

The script also creates a log of all of the database changes which are made.

