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


## All of the options available for the call
```php
$pages = $sql->ArrayResults(array(
    "tableName"=>"page",
    "expression" => "id, title, page",
    "values" => array(...),
    "where" => "id > 2",
    "group" => "text",
    "order => "id DESC",
    "limit" => 2,
    "exit" => false,
    "sql_cache" => false
));
```

## Choose from the database one record

### Execute the query
#### First version which returns an array of
```php
$page = $sql->OneResult(array("tableName"=>"page"));
```

#### Second version which returns an object
```php
$page = $sql->OneResult(array("tableName"=>"page", "type_array_result"=>false));
```
#### Results Array
```php
Array
(
    [id] => 1
    [title] => Test
    [text] => My test description
)
```
#### Results Object
```php
$pages->id
$pages->title
$pages->text
```

## Choose from the database all records

### Execute the query
#### First version which returns an array of
```php
$pages = $sql->ArrayResults(array("tableName"=>"page"));
```

#### Second version which returns an object
```php
$pages = $sql->ArrayResults(array("tableName"=>"page", "type_array_result"=>false));
```
#### Results Array
```php
Array
(
    [0] => Array
        (
            [id] => 1
            [title] => Test
            [text] => My test description
        )

    [1] => Array
        (
            [id] => 2
            [title] => Test 2
            [text] => My test description 2
        )

    ...........
)
```
#### Results Object
```php
$pages[...]->id
$pages[...]->title
$pages[...]->text
```

##Print a single entry for complex or custom queries

### Execute the query
#### First version which returns an array of
```php
$page = $sql->QueryResult("SELECT * FROM `ht_page`");
```

#### Second version which returns an object
```php
$page = $sql->QueryResult("SELECT * FROM `ht_page`",array("type_array_result"=>false));
```
#### Results

The result is similar point: Choose from the database one record

##Print out all records for complex or custom queries

### Execute the query
#### First version which returns an array of
```php
$pages = $sql->QueryArrayResult("SELECT * FROM `ht_page`");
```

#### Second version which returns an object
```php
$pages = $sql->QueryArrayResult("SELECT * FROM `ht_page`",array("type_array_result"=>false));
```
#### Results

The result is similar point: Choose from the database all records

##Run a secure request

### Execute the query
#### First version
```php
$id_page = 3;
$page = $sql->OneResult(array("where"=>"id = :num", "limit"=>2),array(":num"=>$id_page));
```
#### Second version
```php
$id_page = 3;
$sql->bindMore(array(":num"=>$id_page));
$page = $sql->OneResult(array("where"=>"id = :num", "limit"=>2));
```
#### Third version
```php
$id_page = 3;
$page = $sql->ArrayResults(array("where"=>"id = :num", "limit"=>2),array(":num"=>$id_page));
```
#### Fourth version
```php
$id_page = 3;
$sql->bindMore(array(":num"=>$id_page));
$page = $sql->ArrayResults(array("where"=>"id = :num", "limit"=>2));
```

#### Results
The result is similar point: Choose from the database one record

##Return the number of records in the database

#### Standart query
```php
$count = $sql->CountRow(array("tableName"=>"page"));
```
#### Your query
```php
$count = $sql->QueryCountRow("SELECT id FROM `ht_page`");
```

## Update rows
#### First version
```php
$update = $sql->UpdateRow(array("tableName"=>"page","values"=>array("title"=>"My update")));
```
#### Second version
```php
$update = $sql->QueryUpdateRow("UPDATE ht_page SET title='My update'");
```
#### Third version
```php
$sql->bindMore(array(":set_update"=>"My update"));
$update = $sql->QueryUpdateRow("UPDATE ht_page SET title=:set_update");
```
### Results
bool true/false

## Insert data to table
#### First version
```php
$insert = $sql->InsertRow(array("tableName"=>"page","values"=>
                array("
                    title"=>"Next page",
                    "text"=>"Next Description text"
                )
));
```
#### Second version
```php
$insert = $sql->InsertRow(array("tableName"=>"page","values"=>
                array(
                    array(
                        "title"=>"Next page",
                        "text"=>"Next Description text"
                    ),
                    array("Next page 2","Next Description text 2"),
                    array("Next page 3","Next Description text 3"),
                    ..........
                )
));
```
#### Third version
```php
$insert = $sql->QueryInsertRow("INSERT INTO ht_page(`title`,`text`) VALUES ('Next page','Next Description text')");
```
#### Fourth version
```php
$sql->bindMore(array(":title_page"=>"Next page", ":text_page"=>"Next Description text"));
$insert = $sql->QueryInsertRow("INSERT INTO ht_page(`title`,`text`) VALUES (:title_page,:text_page)");
```
### Results
integer (last id)
    


## Clear all parameters for queries
```php
$sql->ClearArguments();
```

## Clear some of the parameters for queries
```php
$sql->ClearArguments("tableName","where","limit");
```






## Transaction functions
#### Initiates a transaction
```php
$sql->beginTransaction();
```
#### Commits a transaction
```php
$sql->commit();
```
#### Rolls back a transaction
```php
$sql->rollback();
```

### Examples
```php
$sql->beginTransaction();
$sql->Init("UPDATE ht_page SET title = 'hamburger'");
$sql->Init("DROP TABLE ht_page");
$sql->rollback();
```





## List of all functions

#### Operation with row
```php
$sql->OneResult(...);
$sql->ArrayResults(...);
$sql->CountRow(...);
$sql->UpdateRow(...);
$sql->InsertRow(...);

$sql->QueryResult(...);
$sql->QueryArrayResult(...);
$sql->QueryCountRow(...);
$sql->QueryUpdateRow(...);
$sql->QueryInsertRow(...);
```
#### Operation transaction
```php
$sql->beginTransaction(...);
$sql->commit(...);
$sql->rollback(...);
```
#### Other
```php
$sql->Init(...);
$sql->bindMore(...);
$sql->ClearArguments(...);
```
