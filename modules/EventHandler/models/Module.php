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
    protected static $DEBUG = false;

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

    protected static $DEBUGCOUNTER = array('core' => array(), 'actions' => array(), 'filter' => array());

    /**
     * @param array $filtername
     */
    protected static function _loadFilterCache() {
        global $adb;
        if(self::$DEBUG === true) {
            $startTime = microtime(true);
        }

        if(self::$_filterCache !== false) {
            return;
        }

        $query = "SELECT handler_path, handler_class, event_name FROM vtiger_eventhandlers WHERE is_active=true";// AND event_name IN (".generateQuestionMarks($tmpFilterlist).')';
        $result = $adb->query($query);

        while($filter = $adb->fetchByAssoc($result)) {
            if(!isset(self::$_filterCache[$filter['event_name']])) {
                self::$_filterCache[$filter['event_name']] = array();
            }

            self::$_filterCache[$filter['event_name']][] = $filter;
        }

        if(self::$DEBUG === true) {
            self::$DEBUGCOUNTER['core']['_loadFilterCache'][] = round((microtime(true) - $startTime) * 1000, 2).'ms';
        }
    }

    public static function do_action($eventName, $parameter = false) {
        $startTime = microtime(true);

        // if vtiger.footer Action is called, output the timings for admins
        if($eventName == "vtiger.process.finish") {
            $headers = headers_list();
            $isJSON = false;
            foreach($headers as $header) {
                if(strpos($header, 'text/json') !== false) {
                    $isJSON = true;
                    break;
                }
            }

            if($isJSON === false) {
                global $current_user;
                if($current_user->is_admin == "on") {
    //                echo "<div class='vtFooter' style='font-size:11px;padding:0 30px;color:rgb(153, 153, 153);'>Event processing <span title='total time the EventHandlerCore was active' alt='total time the EventHandlerCore was active'>".round(self::$Counter*1000, 1)."</span> / <span title='time Events used internal' alt='time Events used internal'>".round(self::$CounterInternal*1000, 1)." msec</div>";
                    echo "<script type='text/javascript'>console.log('EventHandler: total time the EventHandlerCore was active (ms)', ".round(self::$Counter*1000, 1)."); console.log('EventHandler: time Events used internal (ms)', ".round(self::$CounterInternal*1000, 1).");</script>";
                    if(self::$DEBUG === true) {
                        echo '<script type="text/javascript">console.log('.json_encode(self::$DEBUGCOUNTER).');</script>';
    //                    header('EventHandlerCore:'.round(self::$Counter*1000, 1).'ms');
    //                    header('EventHandlerEvents:'.round(self::$CounterInternal*1000, 1).'ms');
                    }
                }
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

        $duration = (microtime(true) - $startTime2);
        self::$CounterInternal += $duration;
        self::$Counter += (microtime(true) - $startTime) - $duration;

        if(self::$DEBUG === true) {
            $duration = round((microtime(true) - $startTime2) * 1000, 4);
            self::$DEBUGCOUNTER['actions'][$eventName][] = $duration . 'ms';
        }
    }

    public static function do_filter($filternames) {
        $startTime = microtime(true);
        $duration = 0;
        global $adb;

        if(!is_array($filternames)) {
            $filternames = array($filternames);
        }

        // load the Cache for this Filter
        if(self::$_filterCache === false) {
            self::_loadFilterCache();
        }

        $extra = func_get_args();

        foreach($filternames as $filtername) {
            if(self::$DEBUG === true) {
                self::$DEBUGCOUNTER['core']['done'][] = $filtername;
            }
            if(is_array(self::$_filterCache[$filtername])) {
                foreach(self::$_filterCache[$filtername] as $filter) {
                    if(!isset(self::$_objectCache[$filter["handler_path"]."/".$filter["handler_class"]])) {
                        require_once($filter["handler_path"]);

                        $className = $filter["handler_class"];
                        self::$_objectCache[$filter["handler_path"]."#".$filter["handler_class"]] = new $className();
                    }

                    $obj = self::$_objectCache[$filter["handler_path"]."#".$filter["handler_class"]];

                    $startTime2 = microtime(true);

                    $extra[0] = $filtername;
                    $extra[1] = call_user_func_array(array($obj, 'handleFilter'), $extra);

                    self::$CounterInternal += (microtime(true) - $startTime2);
                    $duration += (microtime(true) - $startTime2);

                    if(self::$DEBUG === true) {
                        $durationDebug = round((microtime(true) - $startTime2) * 1000, 4);
                        self::$DEBUGCOUNTER['filter'][$filtername][$filter["handler_class"]] =  $durationDebug . 'ms';
                    }
                    // $parameter = $obj->handleFilter($filtername, $parameter);
                }
            }
        }

        self::$Counter += (microtime(true) - $startTime) - $duration;

        return $extra[1];
    }

}
?>
