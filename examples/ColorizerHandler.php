<?php
require_once('include/utils/utils.php');

class ColorizerHandler extends VTEventHandler {
    private $_moduleCache = array();

    /**
     * @param $handlerType
     * @param $entityData VTEntityData
     */
    public function handleEvent($handlerType, $entityData){

		switch($handlerType) {
			case "vtiger.footer":
				# Write somethink into the footer
				echo "Extended Footer";
			break;
		}
	
    }

    public function handleFilter($handlerType, $parameter) {
	
		switch($handlerType) {
            case "vtiger.filter.listview.before.querygenerator":
				# $parameter is the QueryGenerator Object
                $fields = $parameter->getFields();
                # Add homephone field to load from database
				$fields[] = "homephone";
                $parameter->setFields($fields);
                break;
            case "vtiger.filter.listview.after.querygenerator":
                $fields = $parameter->getFields();
                $newFields = array();
                # remove homephone, because we don't want to have this column in the visible area
				foreach($fields as $value) {
                    if($value != "homephone") {
                        $newFields[] = $value;
                    }
                }
                $parameter->setFields($newFields);
                break;
            case "vtiger.filter.listview.render":
                // 0 -> Row
                // 1 -> complete Data from Query
                // 2 -> recordID
				
				# add homephone in a hidden field in every row
                $parameter[0][0] .= "<input type='hidden' id='colorizer_value_homephone_".$parameter[2]."' value='".$parameter[1]["homephone"]."'>";
                break;
        }
        return $parameter;

    }
}
