# PHP Search All Database
This code can search the entire database, by narrowing down the tables &amp; columns to search in. Giving flexibility and high-performance execution to execute faster searches by lowering the traversing nodes.<br/>
<li>Simple PHP Searching tool</li>
<li>User friendly - Plug and Play</li>
<li>Tiny PHP search engine</li>

# How to use:


Assign keyword to search to variable `$search_keyword`

```php
$search_keyword = "KEYWORD";						// Enter keyword to search
```

Enter `table names` & their respective `column names` in an associative array `$table_associative_array` to search the `given keyword`.

```php
$table_associative_array = array( 
			'TABLE NAME 1' => array(			// TABLENAME 1 to search in
				'columnN_NAME_A',			// column Name A to search in
				'columnN_NAME_B'			// column Name B to search in
			),
			'TABLE NAME 2' => array(			// TABLENAME 2 to search in
				'columnN_NAME_A',			// column Name A to search in
				'columnN_NAME_B'			// column Name B to search in
			)
		);
```

Call this fantastic function, with two parameters `php_search_all_database( "Keyword to search", "Table name array" )` as below

```php
php_search_all_database( $search_keyword, $table_associative_array );	// call this Awesome function to run script
```



Please STAR if you like this script.
