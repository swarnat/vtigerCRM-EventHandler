<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 05.02.13
 * Time: 11:23
 */
class SWEventHandler
{
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
        if($eventName == "vtiger.footer") {
            global $current_user;
            if($current_user->is_admin == "on") {
                echo "<div style='text-align:left;font-size:11px;padding:0 30px;color:rgb(153, 153, 153);'>Event processing <span title='total time the EventHandler was active' alt='total time the EventHandler was active'>".round(self::$Counter*1000, 1)."</span> / <span title='time Events used internal' alt='time Events used internal'>".round(self::$CounterInternal*1000, 1)." msec</div>";
            }
        }

        // Handle Events with the internal EventsManager
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

    public static function do_filter($filtername, $parameter) {
        $startTime = microtime(true);

        // load the Cache for this Filter
        if(self::$_filterCache === false || !isset(self::$_filterCache[$filtername])) {
            self::_loadFilterCache($filtername);
        }

        // if no filter is registerd only return $parameter
        if(!isset(self::$_filterCache[$filtername]) || count(self::$_filterCache[$filtername]) == 0) {
            return $parameter;
        }

        foreach(self::$_filterCache[$filtername] as $filter) {
            // if not used before this, create the Handler Class
            if(!isset(self::$_objectCache[$filter["handler_path"]."/".$filter["handler_class"]])) {
                require_once($filter["handler_path"]);

                $className = $filter["handler_class"];
                self::$_objectCache[$filter["handler_path"]."#".$filter["handler_class"]] = new $className();
            }

            $obj = self::$_objectCache[$filter["handler_path"]."#".$filter["handler_class"]];

            $startTime2 = microtime(true);
            // call the filter and set the return value again to $parameter
            $parameter = $obj->handleFilter($filtername, $parameter);
            self::$CounterInternal += (microtime(true) - $startTime2);
        }

        self::$Counter += (microtime(true) - $startTime);

        return $parameter;
    }
}
