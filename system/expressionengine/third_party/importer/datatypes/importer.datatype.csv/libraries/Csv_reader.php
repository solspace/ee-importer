<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - CSV Reader
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/datatypes/importer.datatype.csv/libraries/Csv_reader.php
 */

class Csv_reader {

    private $fh;

    public	$show_error					= FALSE;
    public	$debug						= FALSE;
    public	$data						= '';
    public	$filename					= '';
    public	$first_record_column_names	= TRUE;
    public	$field_names				= array();
    public	$delimiter					= ',';
    public	$encloser					= '"';
    public	$error						= '';

	/**
	 * Constructor - Sets Preferences
	 *
	 * The constructor can be passed an array of config values
	 */
	public function __construct($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		log_message('debug', "CSV Reader Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize preferences
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}

		if ( $this->delimiter == '\t' OR strtolower($this->delimiter) == 'tab' )
		{
			$this->delimiter = "\t";
		}
		elseif ( strtolower($this->delimiter) == 'semicolon' )
		{
			$this->delimiter = ";";
		}
		elseif ( strtolower($this->delimiter) == 'comma' )
		{
			$this->delimiter = ',';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Open the File
	 *
	 * @access	public
	 * @return	void
	 */

    private function open_file()
    {
    	if ( $this->filename == '')
    	{
    		$this->error('csv_invalid_filename');
    		return FALSE;
    	}

        $this->fh = @fopen($this->filename, 'r');

        if ( ! is_resource($this->fh))
        {
            $this->error('csv_invalid_filename');
    		return FALSE;
        }

        return $this->fh;
    }

	// --------------------------------------------------------------------

	/**
	 * Parse the Data from a String, Returns ALL Rows of Data
	 *
	 * @access	public
	 * @return	void
	 */

    public function parse()
    {
    	if (empty($this->data))
    	{
    		$this->error('csv_data_input_required');
    		return FALSE;
    	}

    	if (strpos($this->data, "\n") === FALSE OR
    		strpos($this->data, $this->delimiter) === FALSE)
    	{
    		$this->error('csv_data_input_required');
    		return FALSE;
    	}

    	ini_set('auto_detect_line_endings', true);

		// So, str_getcsv() fails when there is a line break inside the enclosed content
		// https://bugs.php.net/bug.php?id=55763
		// However, fgetcsv() does not seem to suffer from this problem, so we use our backup method.
    	// $items = str_getcsv($this->data, "\n", $this->encloser); //parse the rows

    	$items = str_getcsv2($this->data, $this->delimiter, $this->encloser); //parse the rows

        // If need be, treat the first record as the column names and fetch again.
		if ($this->first_record_column_names === TRUE)
		{
			if (empty($this->field_names_array))
			{
				// $this->field_names_array = str_getcsv(array_shift($items), $this->delimiter, $this->encloser);
				$this->field_names_array = array_shift($items);
			}
		}
		// If not the case, we create generic names
		elseif(isset($items[0]) && empty($this->field_names_array))
		{
			// $fields = str_getcsv($items[0], $this->delimiter, $this->encloser);
			$fields =  $items[0];

			for($i=1, $s = count($fields); $i <= $s; ++$i)
			{
				$this->field_names_array[] = 'field_'.$i;
			}
		}

		// --------------------------------------------
        //  Get our Rows of Data, Add in Sensical Field Names
        // --------------------------------------------

        $data = array();

		foreach($items as $j => $row)
		{
			// $row = str_getcsv($row, $this->delimiter, $this->encloser); //parse the items in rows

			if (count($row) != count($this->field_names_array))
			{
				$this->error('csv_data_input_required');
				return FALSE;
			}

			for($i = 0, $s = count($this->field_names_array); $i < $s; ++$i)
			{
                $data[$j][$this->field_names_array[$i]] = $row[$i];
            }
		}

		return $data;
    }

	// --------------------------------------------------------------------

	/**
	 * Fetch a SINGLE row of Data from a File
	 *
	 * @access	public
	 * @return	void
	 */

    public function fetch()
    {
        if ( ! feof($this->fh))
        {
        	$item = fgetcsv($this->fh, 10000, $this->delimiter, $this->encloser);

            if ($this->data !== FALSE)
            {
            	// If need be, treat the first record as the column names and fetch again.
                if ($this->first_record_column_names === TRUE && empty($this->field_names_array))
                {
                    $this->field_names_array = $item;

                    return $this->fetch();
                }

                return $item;
            }
        }

		// Finished.
        $this->close_file();
        return NULL;
    }

	// --------------------------------------------------------------------

	/**
	 * Close the File Connection
	 *
	 * @access	public
	 * @return	void
	 */

    private function close_file()
    {
        @fclose($this->fh);
    }

	// ------------------------------------------------------------------------

	/**
	 * Display error message
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	private function error($line)
	{
		$this->error = $line;
	}
}
// END Csv_reader CLASS


if( ! function_exists('str_getcsv'))
{
	function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = '\\')
	{
		return str_getcsv2($input, $delimiter, $enclosure, $escape);
	}
}

function str_getcsv2($input, $delimiter = ",", $enclosure = '"', $escape = '\\')
{
	$fp = fopen("php://memory", 'r+');
	fputs($fp, $input);
	rewind($fp);

	// Seems fgetcsv() does not translate \\ and \" into \ and " in the data.  Kudos to Nic for pointing this out
	// http://us3.php.net/manual/en/function.fgetcsv.php#58124
	$find[]		= '\\\\';
	$replace[]	= '\\';

	$find[]		= '\\"';
	$replace[]	= '"';

	$return = array();

	// $escape was not available until PHP 5.3.0
	if (version_compare(PHP_VERSION, '5.3.0') >= 0)
	{
		while($data = fgetcsv($fp, null, $delimiter, $enclosure, $escape))
		{
			$return[] = str_replace($find, $replace, (count($data) == 1) ? array_shift($data) : $data);
		}
	}
	else
	{
		while($data = fgetcsv($fp, null, $delimiter, $enclosure))
		{
			$return[] = str_replace($find, $replace, (count($data) == 1) ? array_shift($data) : $data);
		}
	}

	fclose($fp);

	if (count($return) == 0) return FALSE;
	if (count($return) == 1) return array_shift($return);

	return $return;
}


?>