vtigerCRM 7 - EventHandler
======================

**Compatible with VtigerCRM 7**

This Extension will implement an improved Version of Eventhandling in vtigerCRM 7.  
**It isn't compatible to earlier/later vTigerCRM versions!**

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

**2017-05-27**
 - Start to migrate to VtigerCRM 7
