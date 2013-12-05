<?php
/**
 * LetMC
 *
 * PHP class to allow integration with the LetMC SOAP API
 *
 * @author Christian Thomas <christian@industriousmouse.co.uk>
 */
class LetMC
{
	/**
	 * API URL
	 *
	 * @access	private
	 * @var string
	 */
	private $api_url 	= 'http://portal.letmc.com/PropertySearchService.asmx?WSDL';

	/**
	 * Client ID
	 *
	 * @access	private
	 * @var 	string
	 */
	private $client_id 	= '{CLIENT-ID-HERE}';

	/**
	 * Property Search Defaults
	 *
	 * @access	private
	 * @var 	array
	 */
	private $search_defaults = array(
		'nMaxResults' 		=> 10,
		'nRentMinimum' 		=> 0,
		'nRentMaximum' 		=> 0,
		'nMaximumTenants' 	=> -1
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{

	}

	/**
	 * GetBranches
	 *
	 * Return all branches by Client ID
	 *
	 * @access	public
	 */
	public function GetBranches()
	{
		$options = array();

		$result = $this->make_request(__FUNCTION__, $options);

		if(!isset($result->GetBranchesResult)) return false;

		return $result->GetBranchesResult->BranchInfo;
	}

	/**
	 * GetBranchDetails
	 *
	 * Return branch details by $branch_ids
	 *
	 * @access	public
	 * @param 	array 	$branch_ids 	Any associated Branch ID's - Strings will be typecasted
	 */
	public function GetBranchDetails($branch_ids = array())
	{
		$options['objBranchIDs'] = (array) $branch_ids;

		$result = $this->make_request(__FUNCTION__, $options);

		if(!isset($result->GetBranchDetailsResult)) return false;

		return $result->GetBranchDetailsResult->BranchInfo;
	}

	/**
	 * SearchProperties
	 *
	 * Perform a search on LetMC available properties
	 *
	 * @access	public
	 */
	public function SearchProperties($options = array())
	{
		$options = array_merge(
			$this->search_defaults,
			$options
		);

		$result = $this->make_request(__FUNCTION__, $options);

		return $result;
	}

	/**
	 * GetPropertyDetails
	 *
	 * Return all properties that match the ID's within $options['objPropertyIDs'] array
	 *
	 * @access	public
	 */
	public function GetPropertyDetails($options = array())
	{
		$result = $this->make_request(__FUNCTION__, $options);

		return $result;
	}

	/**
	 * Make Request
	 *
	 * Function to perform the SOAP request
	 * Returns any successful requests back to the previous function
	 * Logs any failures in /wp-includes/letmc/logs if there are any
	 *
	 * @access 	private
	 * @param  	string 			$function 	SOAP Function Name
	 * @param  	array 			$options  	Any arguments/options to pass
	 * @return 	mixed
	 */
	private function make_request($function, $options)
	{
		// Assign the LetMC Client ID
		$options['strClientID'] = $this->client_id;

		// Instantiate SOAP Client (Force Single items to be returned as arrays for consistency)
		$client = new SoapClient(
			$this->api_url,
			array(
				'features' => SOAP_SINGLE_ELEMENT_ARRAYS
			)
		);

		// Perform request
		try {

			$result = $client->{$function}($options);

		} catch(SoapFault $e) {

			$this->log($e->getMessage());
			return false;

		}

		return $result;
	}

	/**
	 * Log
	 *
	 * Logs a message to the log file
	 *
	 * @access	private
	 * @param	string		message to be logged
	 * @return	void
	 */
	private function log($message)
	{
		$dir 	= dirname(__FILE__) . '/logs/';
		$file 	= $dir.date('Y-m-d').'.log';

		if (!is_dir($dir)) mkdir($dir, 0755, true);

		$handle = fopen($file, 'a+');
		fwrite($handle, date('H:i:s').' -- '.$message."\n");
		fclose($handle);
	}

}