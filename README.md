PDO Database Class
============================

A database class for PHP-MySQL which uses the PDO extension.

## To use the class
#### 1. Edit the database settings in the settings.ini.php
```
[SQL]
engine =        mysql
host =          localhost
;port =         3306
user =          root
password =
dbname =        site_dating
charset =       utf8
tablePrefix =   ht_
```
#### 2. Require the class in your project
```php
<?php
require_once "SQLCore.php";
```
#### 3. Create the instance 
```php
<?php
// The instance
$sql = new SQLCore;
```

## Examples
Below some examples of the basic functions of the database class. I've included a SQL dump so you can easily test the database
class functions. 
#### The ht_page table 
| id | title | text 
|:-----------:|:------------:|:------------:|
| 1       |        first |     First description text  
| 2       |        second  |     Second description text  
| 3       |        third  |     Third description text   
| 4       |        fourth |     Fourth description text   
| 5       |        fifth|     Fifth description text   

