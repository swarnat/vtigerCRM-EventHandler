vtigerCRM 6 - EventHandler
======================

**Compatible with 6.0, 6.1 and 6.2**

This Extension will implement an improved Version of Eventhandling in vtigerCRM 6.  
**It isn't compatible to earlier/later vtigerCRM versions!**

The downloads from the *release* directory are fully compatible with the ModuleManager and **must be installed at first**.   
If you want to use this Extension, you have to enter some additional code lines into core files of vtigerCRM manually to implement the necessary Interfaces. **(See below)**

The system is similar to the one of Wordpress and has filter and actions.  
An **action** will be called by **EventHandler_Module_Model::do_action("$actionName", [$parameter])** and do some tasks without return a value.  
A **filter** will be called by **$parameter = EventHandler_Module_Model::do_filter("$filterName",$parameter)** and do some tasks but could modify the $parameter value, which will also returned. Normally the return value will be equal to $parameter if no filter change the value.
Otherwise the new value will be returned.

If you want to register your class for an Event you could take the default EventHandler function like this and set a new action/filtername.
```php
$em = new VTEventsManager($adb);

$em->registerHandler('vtiger.filter.listview.querygenerator.before', '<handlerFile>', '<handlerClass>');
```

This requires a File and a Class which could be called to handle the Event/Filter.  
For Actions there has to be a **"handleEvent($handlerType, $parameter)"** function. Actions will be handled through the internal EventHandler class. They are implemented only to get a single interface for both methods.  
For Filters there has to be a **"handleFilter($handlerType, $parameter, [$parameter2], ...)"** function, which will return the new $parameter value.  

======================
## Changelog

**2014-01-08**
 - Modify Insertion 2.4.2. **Please check the corresponding files**

**600.0102 (2014-12-31)**
 - Increase the Performance for handling of many Filter/Actions
 - *Added Actions:* vtiger.process.finish
 - *Added Filters:* vtiger.filter.detailview.record, $sortOrder = $this->getForSql('sortorder');
 - *Added Code Insertions*: 2.6, 2.4.2, 2.1.3
 - Modify Insertion 2.4.1, 2.1.1, 2.1.3. **Please check the corresponding files**
	
**600.0101 (2013-12-16)**
 - first public Release for VtigerCRM 6


======================
## Setup Instructions

###### 1. Install this Extension with ModuleManager

###### 2.1 Open: modules/Vtiger/models/ListView.php

**2.1.1 Search:**  
```php
$listQuery = $this->getQuery();
```
Use the one inside the function **getListViewEntries** around Line 200!

**Insert Before:**
```php
/** EventHandler START */
	$this->set(
		'query_generator',
		EventHandler_Module_Model::do_filter(
			array(
				'vtiger.filter.listview.querygenerator.before',
				'vtiger.filter.listview.'.strtolower($moduleName).'.querygenerator.before'
			),
			$this->get('query_generator'),
			$pagingModel
		)
	);
/** EventHandler ENDE */
```
**Insert After:**
```php
	$this->set(
		'query_generator',
		EventHandler_Module_Model::do_filter(
			array(
				'vtiger.filter.listview.querygenerator.after',
				'vtiger.filter.listview.'.strtolower($moduleName).'.querygenerator.after'
			),
			$this->get('query_generator'),
			$pagingModel
		)
	);

	$listQuery = EventHandler_Module_Model::do_filter("vtiger.filter.listview.querygenerator.query", $listQuery, $this->get('query_generator'));/** EventHandler ENDE */
```

**2.1.2 Search:**
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
**2.1.3 Search:**
```php
	$sortOrder = $this->getForSql('sortorder');
```
**Insert After:**
```php
	/** EventHandler START */
	list($orderBy, $sortOrder) = EventHandler_Module_Model::do_filter(
		array(
			'vtiger.filter.listview.orderby',
			'vtiger.filter.listview.'.strtolower($moduleName).'.orderby'
		),
		array(
			$orderBy,
			$sortOrder
		),
		$queryGenerator,
		$pagingModel
	);
	/** EventHandler ENDE */
```	

######2.2 Open: modules/Vtiger/models/Record.php

**2.2.1 Search:**
```php
return $instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
```	
**Insert before:**
```php
/** EventHandler START */
$focus->column_fields = EventHandler_Module_Model::do_filter('vtiger.filter.record.getclean', $focus->column_fields, $moduleName);
/** EventHandler ENDE */
```

######2.3 Open: modules/Inventory/views/Edit.php

**2.3.1 Search:**
```php
$recordModel->setRecordFieldValues($parentRecordModel);
```	
**Insert after:**
```php
/** EventHandler START */
$recordModel = EventHandler_Module_Model::do_filter('vtiger.filter.'.strtolower($moduleName).'.convert', $recordModel, $parentRecordModel);
/** EventHandler ENDE */
```

###### 2.4 Open: includes/main/WebUI.php

**2.4.1 Search:**
```php	
$response = $handler->process($request);
```
**Insert before:**
```php
/** EventHandler START */
	list($handler, $request) = EventHandler_Module_Model::do_filter(
		array(
			"vtiger.filter.process.".strtolower($module.'.'.$componentName.".".$componentType).".before", 
			"vtiger.filter.process.".strtolower($componentName.".".$componentType).".before" 
		),
		array($handler, $request)
	);
/** EventHandler ENDE */
```
**Insert after:**
```php
/** EventHandler START */
	list($handler, $request) = EventHandler_Module_Model::do_filter(
		array(
			"vtiger.filter.process.".strtolower($module.'.'.$componentName.".".$componentType).".after", 
			"vtiger.filter.process.".strtolower($componentName.".".$componentType).".after" 
		),
		array($handler, $request)
	);
/** EventHandler ENDE */
```

**2.4.2. Search: **
```php
	if ($response) {
		$response->emit();
	}
```
** Insert before: **
```php
	/** EventHandler START */
	EventHandler_Module_Model::do_action("vtiger.process.finish", array($module, $componentName, $componentType));
	EventHandler_Module_Model::do_action("vtiger.process.".strtolower($module.'.'.$componentName.".".$componentType).".finish", array($module, $componentName, $componentType));
	/** EventHandler ENDE */
```

###### 2.5 Open: includes/runtime/Controller.php

**2.5.1 Search:**
```php
$viewer->view('Footer.tpl');
```	
**Insert before:**
```php
/** EventHandler START */
$return = EventHandler_Module_Model::do_action("vtiger.footer.before");
if($return === false) { return; }
/** EventHandler ENDE */
```

**Insert after:**
```php
/** EventHandler START */
EventHandler_Module_Model::do_action("vtiger.footer.after");
/** EventHandler ENDE */
```
###### 2.6 Open: modules/Vtiger/models/DetailView.php

**2.6.1 Search
```php
$this->record = $recordModuleInstance;
```
** Insert before: **
```php
$recordModuleInstance = EventHandler_Module_Model::do_filter('vtiger.filter.detailview.record', $recordModuleInstance);
```
