# PHP Search All Database
With this code, you can search entire database with narrowing down the Tables &amp; Column to search in. Giving the flexibility to developer and efficiency to execute faster searches by lowering down the traversing nodes.<br/>
<li>Sample, Easy & Efficient searching.</li>
<li>User friendly - pass parameters and get started</li>

# Usage

##Here's how to use the function:

Assign `keyword to search` to varible `$search_keyword`
```php
$search_keyword = "keyword";                            // search Keyword
```

Enter `table names` in varible `$table_array` to search the given keyword
```php
$table_array = ['',''];                                 // table names in array format
```

Call this awesome function `php_search_all_database("Keyword to search", "Table name array")` as below
```php
php_search_all_database($search_keyword, $table_array); // call this Awesomme function
```
