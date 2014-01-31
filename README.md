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
dbName =        site_dating
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
        "table"=>"page",
        "expression" => "id, title, page",
        "values" => array(...),
    "where" => "id > 2",
    "group" => "text",
    "order" => "id DESC",
    "limit" => 2,
    "exit" => false,
    "sql_cache" => false
));
```

## Choose from the database one record

### Execute the query
#### First version which returns an array of
```php
$page = $sql->OneResult(array("table"=>"page"));
```

#### Second version which returns an object
```php
$page = $sql->OneResult(array("table"=>"page", "type_array_result"=>false));
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
$pages = $sql->ArrayResults(array("table"=>"page"));
```

#### Second version which returns an object
```php
$pages = $sql->ArrayResults(array("table"=>"page", "type_array_result"=>false));
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
$count = $sql->CountRow(array("table"=>"page"));
```
#### Your query
```php
$count = $sql->QueryCountRow("SELECT id FROM `ht_page`");
```

## Update rows
#### First version
```php
$update = $sql->UpdateRow(array("table"=>"page","values"=>array("title"=>"My update")));
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
$insert = $sql->InsertRow(array("table"=>"page","values"=>
                array("
                    title"=>"Next page",
                    "text"=>"Next Description text"
                )
));
```
#### Second version
```php
$insert = $sql->InsertRow(array("table"=>"page","values"=>
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
    
## Delete rows
#### First version
```php
$delete = $sql->DeleteRow(array("table"=>"page","where"=>"id=5"));
```
#### Second version
```php
$delete = $sql->QueryDeleteRow("DELETE FROM `ht_page` WHERE id=5");
```
#### Third version
```php
$sql->bindMore(array(":id_delete"=>5));
$delete = $sql->QueryDeleteRow("DELETE FROM `ht_page` WHERE id=:id_delete");
```
### Results
bool true/false

## Checking the existence of records in the database
```php
$exist = $sql->HasRow(array("table"=>"page","where"=>"id=3"));

$exist = $sql->QueryHasRow("SELECT id FROM `ht_page` WHERE id=3");
```
### Results
bool true/false

## Clear all parameters for queries
```php
$sql->ClearArguments();
```

## Clear some of the parameters for queries
```php
$sql->ClearArguments("table","where","limit");
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

## OPTIMIZE TABLE
Reorganizes the physical storage of table data and associated index data, to reduce storage space and improve I/O efficiency when accessing the table.

#### Examples
```php
$sql->OptimizeTable(array("table"=>"page"));
```
### Results
bool true/false

##Close the database connection
```php
$sql->CloseConnect();
```

##View all info columns
### Examples
#### First version
```php
//$sql->type_array_result = false; - for object
$column = $sql->ColumnShow("page");
```
#### Second version
```php
//$sql->type_array_result = false; - for object
$sql->table = "page";
$column = $sql->ColumnShow();
```
### Results
```php
(
    [0] => Array
        (
            [Field] => id
            [Type] => int(11)
            [Null] => NO
            [Key] => PRI
            [Default] => 
            [Extra] => auto_increment
        )
    [1] => Array
        (
            ..............
        )
    ............
)
```

## Work with DataBase
#### Creates the database. It the DB exists already, it clears it first.
```php
$sql->DatabaseCreate(array("dbName"=>"newbase"));
// OR
$sql->dbName = "newbase";
$sql->DatabaseCreate();
```
### Results
bool true/false
#### Selects the database for use.
```php
$sql->DatabaseSelect(array("dbName"=>"newbase"));
// OR
$sql->dbName = "newbase";
$sql->DatabaseSelect();
```
### Results
bool true/false
#### Drop database
```php
$sql->DatabaseDrop(array("dbName"=>"page"));
// OR
$sql->dbName = "page";
$sql->DatabaseDrop();
```
### Results
bool true/false

## More features
### Return the maximum value of the field
```php
$sql->table = "page";
$sql->expression = "id";
$sql->GetMax();
// OR
$sql->GetMax("page","id");
```
### Results
mixed data (max value in expression)

### Return the minimum value of the field
```php
$sql->table = "page";
$sql->expression = "id";
$sql->GetMin();
// OR
$sql->GetMin("page","id");
```
### Results
mixed data (max value in expression)

### Simplified data output from the database for specific records
```php
$sql->table = "page";
//$sql->expression = "*";
$sql->GetArrayById(null, array(2,3,5));
// OR
$sql->GetArrayById("page", range(1,5));
// OR
$sql->GetArrayById("page", range(1,5), "uid_page");
```
### Results
An array of data, depending on the parameters passed


# Other Info

#### How do I get a response in the form of an object for his own request?
```php
$sql->type_array_result = false;
$page = $sql->QueryResult("SELECT * FROM `ht_page`");
```
#### Results
```php
$pages->id
$pages->title
$pages->text
```


##List all possible requests to work with rows
(OneResult, ArrayResults, CountRow, UpdateRow, InsertRow, DeleteRow, QueryResult, Query... , ...)
### The example on the basis of deleting rows
#### First
```php
$delete = $sql->DeleteRow(array("table"=>"page","where"=>"id=5"));
```
#### Second
```php
$delete = $sql->DeleteRow(array("table"=>"page","where"=>"id=:id_delete"),array(":id_delete"=>5));
```
#### Third
```php
$sql->bindMore(array(":id_delete"=>1, ":order_name"=>"title"));
$delete = $sql->DeleteRow(array("table"=>"page","where"=>"id>:id_delete","order"=>":order_name DESC","limit"=>2));
```
#### Fourth
```php
$delete = $sql->QueryDeleteRow("DELETE FROM `ht_page` WHERE id=5");
```
#### Fifth
```php
$delete = $sql->QueryDeleteRow("DELETE FROM `ht_page` WHERE id>:id_delete",array(":id_delete"=>2));
```
#### Sixth
```php
$sql->bindMore(array(":id_delete"=>1, ":limit_page"=>2));
$delete = $sql->QueryDeleteRow("DELETE FROM `ht_page` WHERE id>:id_delete LIMIT :limit_page");
```



## List of all functions

#### Operation with row
```php
$sql->OneResult(...);
$sql->ArrayResults(...);
$sql->CountRow(...);
$sql->UpdateRow(...);
$sql->InsertRow(...);
$sql->DeleteRow(...);
$sql->HasRow(...);

$sql->QueryResult(...);
$sql->QueryArrayResult(...);
$sql->QueryCountRow(...);
$sql->QueryUpdateRow(...);
$sql->QueryInsertRow(...);
$sql->QueryDeleteRow(...);
$sql->QueryHasRow(...);
```
#### Operation with database, table, column and other
```php
$sql->DatabaseCreate(...);
$sql->DatabaseSelect(...);
$sql->DatabaseDrop(...);

$sql->ColumnShow(...);

$sql->GetMax(...);
$sql->GetMin(...);
$sql->GetArrayById(...);
```
#### Operation transaction
```php
$sql->beginTransaction();
$sql->commit();
$sql->rollback();
```
#### Other
```php
$sql->Init(...);
$sql->bindMore(...);
$sql->ClearArguments(...);
$sql->OptimizeTable(...);
$sql->CloseConnect();
```
