<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EventHandler_Module_Model extends Vtiger_Module_Model{

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);
		return $links;
	}

    protected static $_eventManager = false;
    protected static $_objectCache = array();
    protected static $_filterCache = false;

    protected static $Counter = 0;
    protected static $CounterInternal = 0;

    protected static function _loadFilterCache($filtername) {
        global $adb;
        $query = "SELECT handler_path, handler_class FROM vtiger_eventhandlers WHERE is_active=true AND event_name = ?";
        $result = $adb->pquery($query, array($filtername));

        if(!isset(self::$_filterCache[$filtername])) {
            self::$_filterCache[$filtername] = array();
        }

        while($filter = $adb->fetchByAssoc($result)) {
            self::$_filterCache[$filtername][] = $filter;
        }
    }

    public static function do_action($eventName, $parameter = false) {
        $startTime = microtime(true);

        // if vtiger.footer Action is called, output the timings for admins
        if($eventName == "vtiger.footer.after") {
            global $current_user;
            if($current_user->is_admin == "on") {
                echo "<div class='vtFooter' style='font-size:11px;padding:0 30px;color:rgb(153, 153, 153);'>Event processing <span title='total time the EventHandler was active' alt='total time the EventHandler was active'>".round(self::$Counter*1000, 1)."</span> / <span title='time Events used internal' alt='time Events used internal'>".round(self::$CounterInternal*1000, 1)." msec</div>";
            }
        }

        if(self::$_eventManager === false) {
            global $adb;
            self::$_eventManager = new VTEventsManager($adb);
            // Initialize Event trigger cache
            self::$_eventManager->initTriggerCache();
        }

        $startTime2 = microtime(true);

        self::$_eventManager->triggerEvent($eventName, $parameter);

        self::$Counter += (microtime(true) - $startTime);
        self::$CounterInternal += (microtime(true) - $startTime2);
    }

    public static function do_filter($filtername) {
        $startTime = microtime(true);
        global $adb;

        // load the Cache for this Filter
        if(self::$_filterCache === false || !isset(self::$_filterCache[$filtername])) {
            self::_loadFilterCache($filtername);
        }

        $extra = func_get_args();

        foreach(self::$_filterCache[$filtername] as $filter) {
            if(!isset(self::$_objectCache[$filter["handler_path"]."/".$filter["handler_class"]])) {
                require_once($filter["handler_path"]);

                $className = $filter["handler_class"];
                self::$_objectCache[$filter["handler_path"]."#".$filter["handler_class"]] = new $className();
            }

            $obj = self::$_objectCache[$filter["handler_path"]."#".$filter["handler_class"]];

            $startTime2 = microtime(true);

            $extra[1] = call_user_func_array(array($obj, 'handleFilter'), $extra);

            self::$CounterInternal += (microtime(true) - $startTime2);
            // $parameter = $obj->handleFilter($filtername, $parameter);
        }

        self::$Counter += (microtime(true) - $startTime);

        return $extra[1];
    }

}
?>
