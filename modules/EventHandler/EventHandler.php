<?php

if(!class_exists("EventHandler", false)) {
    class EventHandler {

        public function vtlib_handler($modulename, $event_type) {
            global $adb;
#            ini_set("display_errors", 1);
#            error_reporting(E_ALL);

#            echo "vtlib Handler (".$event_type.")<br>";
            if($event_type == 'module.postinstall') {

            } else if($event_type == 'module.disabled') {
                // TODO Handle actions when this module is disabled.
            } else if($event_type == 'module.enabled') {
                // TODO Handle actions when this module is enabled.
            } else if($event_type == 'module.preuninstall') {
                // TODO Handle actions when this module is about to be deleted.
            } else if($event_type == 'module.preupdate') {
                // TODO Handle actions before this module is updated.
            } else if($event_type == 'module.postupdate') {

            }
			
        }

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

        public static function filter($filtername) {
            global $adb;

            $query = "SELECT * FROM vtiger_eventhandlers WHERE is_active=true AND event_name = ?";
            $result = $adb->pquery($query, array($filtername));

            $args = func_get_args();
            array_unshift($args, $filtername);

            while($filter = $adb->fetchByAssoc($result)) {
                require_once($filter["handler_path"]);
                $className = $filter["handler_class"];
                $obj = new $className();

                $args[1] = call_user_func_array(array($obj, 'handleFilter'), $args);
            }

            return $args[1];
        }


    }
}