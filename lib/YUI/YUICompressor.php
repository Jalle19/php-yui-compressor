<?php

/**
 * PHP wrapper for the YUI compressor. 
 * Based on https://github.com/gpbmike/PHP-YUI-Compressor.
 * 
 * @author Sam stenvall <sam@supportersplace.com>
 * @copyright Copyright &copy; Sam Stenvall 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace YUI;

class YUICompressor
{

	const TYPE_CSS = 'css';
	const TYPE_JS = 'js';

	/**
	 * @var string the absolute path to yuicompressor.jar
	 */
	private $_jarPath;

	/**
	 * @var array the default options for the YUI compressor
	 */
	private $_options = array(
		'type'=>self::TYPE_JS,
		'line-break'=>false,
		'verbose'=>false,
		'nomunge'=>false,
		'preserve-semi'=>false,
		'disable-optimizations'=>false,
	);

	/**
	 * @var array the names of the JavaScript-specific compressor options
	 */
	private static $_javaScriptOptions = array(
		'nomunge',
		'preserve-semi',
		'disable-optimizations',
	);

	/**
	 * Class constructor
	 * @param array $options (optional) options for the YUI compressor
	 */
	public function __construct($options = array())
	{
		$this->_jarPath = realpath(__DIR__
				.'/../../vendor/nervo/yuicompressor/yuicompressor.jar');

		$this->setOptions($options);
	}

	/**
	 * Sets the compressor type. Valid values are TYPE_JS and TYPE_CSS. This 
	 * method can be used to change the compressor type after the class has 
	 * been instantiated.
	 * @param string $type the type
	 * @throws Exception if the type is invalid
	 */
	public function setType($type)
	{
		if ($type === self::TYPE_CSS || $type === self::TYPE_JS)
			$this->_options['type'] = $type;
		else
			throw new Exception('Invalid type: '.$type);
	}

	/**
	 * Compresses the specified data and returns it
	 * @param string $data the data
	 * @return string the compressed data
	 * @throws Exception if the compression fails
	 */
	public function compress($data)
	{
		// Construct the command
		$cmd = 'java -jar '.escapeshellarg($this->_jarPath).' --charset UTF-8';
		$cmd .= ' --type '.$this->_options['type'];

		if ($this->_options['line-break'] !== false)
			$cmd .= ' --line-break '.(int) $this->_options['line-break'];

		if ($this->_options['verbose'])
			$cmd .= " -v";

		// Javascript-specific options
		if ($this->_options['type'] === self::TYPE_JS)
			foreach (self::$_javaScriptOptions as $option)
				if ($this->_options[$option])
					$cmd .= ' --'.$option;

		// Run the command
		$pipes = array();
		$descriptors = array(
			0=>array('pipe', 'r'),
			1=>array('pipe', 'w'),
			2=>array('pipe', 'w'),
		);

		$process = proc_open($cmd, $descriptors, $pipes);

		if (is_resource($process))
		{
			// Write the data we want to compress to STDIN
			fwrite($pipes[0], $data);
			fclose($pipes[0]);

			// Get the compressed data and eventual error from STDOUT and STDERR
			$compressedData = stream_get_contents($pipes[1]);
			$error = stream_get_contents($pipes[2]);
			fclose($pipes[1]);
			fclose($pipes[2]);

			$return = proc_close($process);

			// Throw an exception if compression fails
			if ($return === 0)
				return $compressedData;
			else
				throw new Exception('Failed to compress data: '.$error, $return);
		}

		throw new Exception('Failed to open a process');
	}

	/**
	 * Merges the default options with the ones specified. This is done in a 
	 * separate method because it's a bad idea to throw exceptions from the 
	 * constructor.
	 * @param array $options the options
	 * @throws Exception if an option is invalid
	 */
	private function setOptions($options)
	{
		// Check that all supplied options are valid
		foreach (array_keys($options) as $option)
			if (!array_key_exists($option, $this->_options))
				throw new Exception('Invalid option: '.$option);

		$this->_options = array_merge($this->_options, $options);
	}

}
