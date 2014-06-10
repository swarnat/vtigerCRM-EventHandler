vtigerCRM-EventHandler
======================

This Extension will implement an improved Version of Eventhandling in vtigerCRM 6.  
**It isn't compatible to earlier/later vtigerCRM versions!**

The downloads from *release* directory are compatible to the ModuleManager and must be installed at first.   
If you want to use this Extension you have to manually modify core files of vtigerCRM to implement the Events. **(See below)**

The system is similar to the one of Wordpress and has filter and actions.  
An **action** will be called by **EventHandler_Module_Model::do_action("$actionName", [$parameter])** and do some tasks without return a value.  
A **filter** will be called by **$parameter = EventHandler_Module_Model::do_filter("$filterName",$parameter)** and do some tasks but could modify the $parameter value, which will also returned. Normally the return value will be equal to $parameter if no filter change the value.
Otherwise the new value will be returned.

If you want to register your class for an Event you could take the default EventHandler function like this and set a new action/filtername.
```php
$em = new VTEventsManager($adb);

$em->registerHandler('vtiger.filter.listview.querygenerator.before', '<handlerFile>', '<handlerClass>');
```

This requires a File and Class which could be called to handle the Event/Filter.  
For Actions there has to be a **"handleEvent($handlerType, $parameter)"** function. Actions will be handled through the internal EventHandler class. They are implemented only to get a single interface for both methods.  
For Filters there has to be a **"handleFilter($handlerType, $parameter1, [$parameter2], ...)"** function, which will return the new $parameter value.  

======================
## Setup Instructions

###### 1. Install this Extension with ModuleManager

###### Open: modules/Vtiger/models/ListView.php

**Search:**  
```php
$listQuery = $this->getQuery();
```
**Insert Before:**
```php
/** EventHandler START */
$this->set(
	'query_generator',
	EventHandler_Module_Model::do_filter(
		"vtiger.filter.listview.querygenerator.before",
		$this->get('query_generator')
	)
);
/** EventHandler ENDE */
```
**Insert After:**
```php
/** EventHandler START */
$this->set(
	'query_generator',
	EventHandler_Module_Model::do_filter(
		"vtiger.filter.listview.querygenerator.after",
		$this->get('query_generator')
	)
);

$listQuery = EventHandler_Module_Model::filter("vtiger.filter.listview.querygenerator.query", $listQuery, $this->get('query_generator'));
/** EventHandler ENDE */
```

**Search:**
```php
return $listViewRecordModels;
```
**Insert Before:**
```php
/** EventHandler START */
$listViewRecordModels = EventHandler_Module_Model::do_filter(
	"vtiger.filter.listview.records",
	$listViewRecordModels,
	$pagingModel
);
/** EventHandler ENDE */
```	

###### Open: includes/main/WebUI.php

**Search:	**
```php	
$response = $handler->process($request);
```
**Insert before:**
```php
/** EventHandler START */
list($handler, $request) = EventHandler_Module_Model::do_filter(
	"vtiger.filter.process.".strtolower($componentName.".".$componentType).".before",
	array($handler, $request)
);
/** EventHandler ENDE */
```
**Insert after:**
```php
/** EventHandler START */
list($handler, $request) = EventHandler_Module_Model::do_filter(
	"vtiger.filter.process.".strtolower($componentName.".".$componentType).".after",
	array($handler, $request)
);
/** EventHandler ENDE */
```

###### Open: includes/runtime/Controller.php

**Search:**
```php
$viewer->view('Footer.tpl');
```	
+**Insert before:**
```php
/** EventHandler START */
$return = EventHandler_Module_Model::do_action("vtiger.footer.after");
if($return === false) { return; }
/** EventHandler ENDE */
```

**Insert after:**
```php
/** EventHandler START */
EventHandler_Module_Model::do_action("vtiger.footer.after");
/** EventHandler ENDE */
```