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

    }
}