vtigerCRM-EventHandler
======================

This Extension will implement a improved Version of Eventhandling in vtigerCRM 5.40. 
**It isn't compatible to earlier/later vtigerCRM versions!**

The downloads from *release* directory are compatible to the ModuleManager. 
If you want to use this Extension you have to manualle modify Core files of vtigerCRM to implement the Events. (See below)

#### Setup Instructions

##### Open: include/utils/utils.php  

**Search:**  
  require_once 'vtlib/Vtiger/Language.php';
**Insert After**
  require_once 'modules/SWEventHandler/SWEventHandler.php';
  
##### Open: modules/Vtiger/ListView.php

**Search:**
  $list_query = $queryGenerator->getQuery();  
**Insert before:**
  /** Additional Event Triggers by swarnat START*/
    if(class_exists("SWEventHandler")) $queryGenerator = SWEventHandler::filter("vtiger.filter.listview.before.querygenerator", $queryGenerator);
  /** Additional Event Triggers by swarnat END */
**AND Insert After:**
  /** Additional Event Triggers by swarnat START*/
    if(class_exists("SWEventHandler")) $queryGenerator = SWEventHandler::filter("vtiger.filter.listview.after.querygenerator", $queryGenerator);
  /** Additional Event Triggers by swarnat END */

##### Open: include/ListView/ListViewController.php

**Search:**
  $data[$recordId] = $row;
**Insert before:**
  /** Additional Event Triggers by swarnat START*/
    if(class_exists("SWEventHandler")) list($row, $tmp, $tmp2) = SWEventHandler::filter("vtiger.filter.listview.render", array($row, $this->db->fetchByAssoc($result, $i), $recordId));
  /** Additional Event Triggers by swarnat END */

##### Open: modules/Vtiger/footer.php

**Search:**
  global $app_strings;
  
**Add after:**
  /** Additional Event Triggers by swarnat START*/
    if(class_exists("SWEventHandler")) $queryGenerator = SWEventHandler::fire("vtiger.footer", false);
  /** Additional Event Triggers by swarnat END */

##### Open: modules/Vtiger/header.php

**Search:**
  $smarty->display("Header.tpl");
**add After:**
  /** Additional Event Triggers by swarnat START*/
    if(class_exists("SWEventHandler")) $queryGenerator = SWEventHandler::fire("vtiger.header", false);
  /** Additional Event Triggers by swarnat END */