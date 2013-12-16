vtigerCRM-EventHandler
======================

This Extension will implement a improved Version of Eventhandling in vtigerCRM 5.40.  
**It isn't compatible to earlier/later vtigerCRM versions!**

The downloads from *release* directory are compatible to the ModuleManager.   
If you want to use this Extension you have to manually modify core files of vtigerCRM to implement the Events. **(See below)**

The system is similar to the one of Wordpress and has filter and actions.  
An **action** will be called by **SWEventHandler::do_action("$actionName",[$parameter])** and do some tasks without return a value.  
A **filter** will be called by **$parameter = SWEventHandler::do_filter("$filterName",$parameter)** and do some tasks but could modify the $parameter value, which will also returned. Normally the return value will be equal to $parameter if no filter change the value.
Otherwise the new value will be returned.

If you want to register your class for an Event you could take the default EventHandler function like this and set a new action/filtername.
```php
$em = new VTEventsManager($adb);

$em->registerHandler('vtiger.filter.listview.querygenerator.before', '<handlerFile>', '<handlerClass>');
```

This requires a File and Class which could be called to handle the Event/Filter.  
For Actions there has to be a **"handleEvent($handlerType, $parameter)"** function. Actions will be handled through the internal EventHandler class. They are implemented only to get a single interface for both methods.  
For Filters there has to be a **"handleFilter($handlerType, $parameter)"** function, which will return the new $parameter value.  

======================
## Setup Instructions

###### 1. Install this Extension with ModuleManager

###### Open: include/utils/utils.php  

**Search:**  
```php
require_once 'vtlib/Vtiger/Language.php';
```
**Insert After**  
```php
require_once 'modules/SWEventHandler/SWEventHandler.php';
```

###### Open: modules/Vtiger/ListView.php

**Search:**  
```php
$list_query = $queryGenerator->getQuery();
```
**Insert before:**  
```php
/** Additional Event Triggers by swarnat START*/
  if(class_exists("SWEventHandler")) 
    $queryGenerator = SWEventHandler::do_filter("vtiger.filter.listview.querygenerator.before", $queryGenerator);
/** Additional Event Triggers by swarnat END */
```
**AND Insert After:**  
```php
/** Additional Event Triggers by swarnat START*/
  if(class_exists("SWEventHandler")) 
    $queryGenerator = SWEventHandler::do_filter("vtiger.filter.listview.querygenerator.after", $queryGenerator);

	if(class_exists("SWEventHandler"))
      $list_query = SWEventHandler::do_filter("vtiger.filter.listview.querygenerator.query", $list_query);
/** Additional Event Triggers by swarnat END */

```

###### Open: include/ListView/ListViewController.php  

**Search:**  
```php
$data[$recordId] = $row;
```
**Insert before:**  
```php
/** Additional Event Triggers by swarnat START*/
  if(class_exists("SWEventHandler")) 
    list($row, $unused, $unused2) = SWEventHandler::do_filter("vtiger.filter.listview.render", array($row, $this->db->query_result_rowdata($result, $i), $recordId));
/** Additional Event Triggers by swarnat END */
```

**Search:**  
```php
return $header;
```
**Insert before:**  
```php
/** Additional Event Triggers by swarnat START*/
  if(class_exists("SWEventHandler")) 
    $header = SWEventHandler::do_filter("vtiger.filter.listview.header", $header);
/** Additional Event Triggers by swarnat END */
```


###### Open: modules/Vtiger/footer.php  

**Search:**  
```php
global $app_strings;
```
**Add after:**  
```php
/** Additional Event Triggers by swarnat START*/
  if(class_exists("SWEventHandler")) 
    SWEventHandler::do_action("vtiger.footer");
/** Additional Event Triggers by swarnat END */
```
###### Open: modules/Vtiger/header.php

**Search:**  
```php
$smarty->display("Header.tpl");
```
**add After:**  
```php
/** Additional Event Triggers by swarnat START*/
  if(class_exists("SWEventHandler")) 
    SWEventHandler::do_action("vtiger.header");
/** Additional Event Triggers by swarnat END */
```
