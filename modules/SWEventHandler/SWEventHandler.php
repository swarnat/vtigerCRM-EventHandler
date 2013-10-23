<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 05.02.13
 * Time: 11:23
 */
class SWEventHandler
{
    private static $_eventManager = false;

    public static function fire($eventName, $parameter) {
        if(self::$_eventManager === false) {
            global $adb;
            self::$_eventManager = new VTEventsManager($adb);
            // Initialize Event trigger cache
            self::$_eventManager->initTriggerCache();
        }

        self::$_eventManager->triggerEvent($eventName, $parameter);
    }

    public static function filter($filtername, $parameter) {
        global $adb;

        $query = "SELECT * FROM vtiger_eventhandlers WHERE is_active=true AND event_name = ?";
        $result = $adb->pquery($query, array($filtername));

        while($filter = $adb->fetchByAssoc($result)) {
            require_once($filter["handler_path"]);
            $className = $filter["handler_class"];
            $obj = new $className();
            $parameter = $obj->handleFilter($filtername, $parameter);
        }

        return $parameter;
    }
}
