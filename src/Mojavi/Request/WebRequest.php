<?php
/**
 * WebRequest provides additional support for web-only client requests such as
 * cookie and file manipulation.
 *
 * @package	Mojavi
 * @subpackage Request
 */
namespace Mojavi\Request;

use Mojavi\Exception\FileException as FileException;

class WebRequest extends Request
{

	// +-----------------------------------------------------------------------+
	// | METHODS															   |
	// +-----------------------------------------------------------------------+

	/**
	 * Retrieve an array of file information.
	 *
	 * @param string A file name
	 *
	 * @return array An associative array of file information, if the file
	 *			   exists, otherwise null.
	 */
	public function getFile ($name)
	{

		if (isset($_FILES[$name]))
		{

			return $_FILES[$name];

		}

		return null;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a file error.
	 *
	 * @param string A file name.
	 *
	 * @return int One of the following error codes:
	 *
	 *			 - <b>UPLOAD_ERR_OK</b>		(no error)
	 *			 - <b>UPLOAD_ERR_INI_SIZE</b>  (the uploaded file exceeds the
	 *										   upload_max_filesize directive
	 *										   in php.ini)
	 *			 - <b>UPLOAD_ERR_FORM_SIZE</b> (the uploaded file exceeds the
	 *										   MAX_FILE_SIZE directive that
	 *										   was specified in the HTML form)
	 *			 - <b>UPLOAD_ERR_PARTIAL</b>   (the uploaded file was only
	 *										   partially uploaded)
	 *			 - <b>UPLOAD_ERR_NO_FILE</b>   (no file was uploaded)
	 */
	public function getFileError ($name)
	{
		$retval = "No file uploaded.";

		if (isset($_FILES[$name]))
		{

			return $_FILES[$name]['error'];

		}

		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Get the file extension of an uploaded file.
	 *
	 * @param	string A file name.
	 * @return	string
	 */
	public function getFileExtension ($name)
	{
		
		$retval = null;
		$file_name = $this->getFileName($name);
		
		if (!is_null($file_name))
		{
			$retval = substr($file_name, strpos($file_name, '.') + 1);
		}

		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a file name.
	 *
	 * @param string A file name.
	 *
	 * @return string A file name, if the file exists, otherwise null.
	 */
	public function getFileName ($name)
	{

		if (isset($_FILES[$name]))
		{

			return $_FILES[$name]['name'];

		}

		return null;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve an array of file names.
	 *
	 * @return array An indexed array of file names.
	 */
	public function getFileNames ()
	{

		return array_keys($_FILES);

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve an array of files.
	 *
	 * @return array An associative array of files.
	 */
	public function getFiles ()
	{

		return $_FILES;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a file path.
	 *
	 * @param string A file name.
	 *
	 * @return string A file path, if the file exists, otherwise null.
	 */
	public function getFilePath ($name)
	{

		if (isset($_FILES[$name]))
		{

			return $_FILES[$name]['tmp_name'];

		}

		return null;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a file size.
	 *
	 * @param string A file name.
	 *
	 * @return int A file size, if the file exists, otherwise null.
	 */
	public function getFileSize ($name)
	{

		if (isset($_FILES[$name]))
		{

			return $_FILES[$name]['size'];

		}

		return null;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a file type.
	 *
	 * This may not be accurate. This is the mime-type sent by the browser
	 * during the upload.
	 *
	 * @param string A file name.
	 *
	 * @return string A file type, if the file exists, otherwise null.
	 */
	public function getFileType ($name)
	{

		if (isset($_FILES[$name]))
		{

			return $_FILES[$name]['type'];

		}

		return null;

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not a file exists.
	 *
	 * @param string A file name.
	 *
	 * @return bool true, if the file exists, otherwise false.
	 */
	public function hasFile ($name)
	{

		return isset($_FILES[$name]);

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not a file error exists.
	 *
	 * @param string A file name.
	 *
	 * @return bool true, if the file error exists, otherwise false.
	 */
	public function hasFileError ($name)
	{

		if (isset($_FILES[$name]))
		{

			return ($_FILES[$name]['error'] != UPLOAD_ERR_OK);

		}

		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not any file errors occured.
	 *
	 * @return bool true, if any file errors occured, otherwise false.
	 */
	public function hasFileErrors ()
	{

		foreach ($_FILES as &$file)
		{

			if ($file['error'] != UPLOAD_ERR_OK)
			{

				return true;

			}

		}

		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not any files exist.
	 *
	 * @return bool true, if any files exist, otherwise false.
	 */
	public function hasFiles ()
	{

		return (count($_FILES) > 0);

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialize this Request.
	 *
	 * @param Context A Context instance.
	 * @param array   An associative array of initialization parameters.
	 *
	 * @return bool true, if initialization completes successfully, otherwise
	 *			  false.
	 *
	 * @throws <b>InitializationException</b> If an error occurs while
	 *										initializing this Request.
	 */
	public function initialize ($context, $parameters = null)
	{

		if (isset($_SERVER['REQUEST_METHOD']))
		{

			switch ($_SERVER['REQUEST_METHOD'])
			{

				case 'GET':
					$this->setMethod(self::GET);
					break;

				case 'POST':
					$this->setMethod(self::POST);
					break;
					
				case 'PUT':
					$this->setMethod(self::PUT);
					break;
					
				case 'DELETE':
					$this->setMethod(self::DELETE);
					break;

				default:
					$this->setMethod(self::GET);

			}

		} else
		{

			// set the default method
			$this->setMethod(self::GET);

		}

		// load parameters from GET/PATH_INFO/POST
		$this->loadParameters();

	}

	// -------------------------------------------------------------------------

	/**
	 * Loads GET, PATH_INFO and POST data into the parameter list.
	 *
	 * @return void
	 */
	private function loadParameters ()
	{

		// merge GET parameters
		$this->setParametersByRef($_GET);

		// parse PATH_INFO
		switch (MO_PATH_INFO_ARRAY)
		{

			case 'SERVER':

				$pathArray =& $_SERVER;
				break;

			case 'ENV':
			default:

				$pathArray =& $_ENV;

		}

		if (isset($pathArray[MO_PATH_INFO_KEY]))
		{

			$array = explode('/', trim($pathArray[MO_PATH_INFO_KEY], '/'));
			$count = count($array);

			for ($i = 0; $i < $count; $i++)
			{

				// see if there's a value associated with this parameter,
				// if not we're done with path data
				if ($count > ($i + 1))
				{

					$this->setParameterByRef($array[$i], $array[++$i]);

				}

			}

		}

		// merge POST parameters
		$this->setParametersByRef($_POST);
		
		// merge PUT parameters
		$raw_params = array();
		parse_str($this->getRawBody(), $raw_params);
		$this->setParametersByRef($raw_params);

	}

	// -------------------------------------------------------------------------

	/**
	 * Move an uploaded file.
	 *
	 * @param string A file name.
	 * @param string An absolute filesystem path to where you would like the
	 *			   file moved. This includes the new filename as well, since
	 *			   uploaded files are stored with random names.
	 * @param int	The octal mode to use for the new file.
	 * @param bool   Indicates that we should make the directory before moving
	 *			   the file.
	 * @param int	The octal mode to use when creating the directory.
	 *
	 * @return bool true, if the file was moved, otherwise false.
	 *
	 * @throws FileException If a major error occurs while attempting to move
	 *					   the file.
	 */
	public function moveFile ($name, $file, $fileMode = 0666, $create = true,
							  $dirMode = 0777)
	{

		if (isset($_FILES[$name]) && $_FILES[$name]['error'] == UPLOAD_ERR_OK &&
			$_FILES[$name]['size'] > 0)
		{

			// get our directory path from the destination filename
			$directory = dirname($file);

			if (!is_readable($directory))
			{

				$fmode = 0777;

				if ($create && !@mkdir($directory, $dirMode, true))
				{

					// failed to create the directory
					$error = 'Failed to create file upload directory "%s"';
					$error = sprintf($error, $directory);

					throw new FileException($error);

				}

				// chmod the directory since it doesn't seem to work on
				// recursive paths
				@chmod($directory, $dirMode);

			} else if (!is_dir($directory))
			{

				// the directory path exists but it's not a directory
				$error = 'File upload path "%s" exists, but is not a directory';
				$error = sprintf($error, $directory);

				throw new FileException($error);

			} else if (!is_writable($directory))
			{

				// the directory isn't writable
				$error = 'File upload path "%s" is not writable';
				$error = sprintf($error, $directory);

				throw new FileException($error);

			}

			if (@move_uploaded_file($_FILES[$name]['tmp_name'], $file))
			{

				// chmod our file
				@chmod($file, $fileMode);

				return true;

			}

		}

		return false;

	}
	
	/**
	 * returns the contents of the uploaded file in a string
	 *
	 * @param string $name
	 * @return false on failure, string on success
	 */
	public function getFileContents($name) {
		$retval = '';
		if($this->hasFile($name)) {
			$retval = file_get_contents($_FILES[$name]['tmp_name']);
		}
		return $retval;
	}

	// -------------------------------------------------------------------------

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return void
	 */
	public function shutdown ()
	{

		// nothing to do here

	}

}

