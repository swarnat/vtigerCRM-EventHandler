File: modules/Vtiger/models/ListView.php

Search:

	$listQuery = $this->getQuery();

Insert Before:

	/** EventHandler START */
	$this->set(
		'query_generator',
		EventHandler_Module_Model::do_filter(
			"vtiger.filter.listview.querygenerator.before",
			$this->get('query_generator')
		)
	);
	/** EventHandler ENDE */
		
Insert After:

	/** EventHandler START */
	$this->set(
		'query_generator',
		EventHandler_Module_Model::do_filter(
			"vtiger.filter.listview.querygenerator.after",
			$this->get('query_generator')
		)
	);

	$listQuery = EventHandler_Module_Model::filter("vtiger.filter.listview.querygenerator.query", $listQuery, $this->get('query_generator'));
	/** EventHandler ENDE */


Search:

	return $listViewRecordModels;

Insert Before:

	/** EventHandler START */
	$listViewRecordModels = EventHandler_Module_Model::do_filter(
		"vtiger.filter.listview.records",
		$listViewRecordModels,
		$pagingModel
	);
	/** EventHandler ENDE */
	
File: includes/main/WebUI.php

Search:	
	$response = $handler->process($request);
	
Insert before:

	/** EventHandler START */
	list($handler, $request) = EventHandler_Module_Model::do_filter(
		"vtiger.filter.process.".strtolower($componentName.".".$componentType).".before",
		array($handler, $request)
	);
	/** EventHandler ENDE */
	
Insert after:

	/** EventHandler START */
	list($handler, $request) = EventHandler_Module_Model::do_filter(
		"vtiger.filter.process.".strtolower($componentName.".".$componentType).".after",
		array($handler, $request)
	);
	/** EventHandler ENDE */
	
	
File: includes/runtime/Controller.php

Search:
	$viewer->view('Footer.tpl');
	
Insert before:

	/** EventHandler START */
	$return = EventHandler_Module_Model::do_action("vtiger.footer.after");
	if($return === false) { return; }
	/** EventHandler ENDE */


Insert after:

	/** EventHandler START */
	EventHandler_Module_Model::do_action("vtiger.footer.after");
	/** EventHandler ENDE */
