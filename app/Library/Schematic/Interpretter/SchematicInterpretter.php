<?php

namespace Library\Schematic\Interpretter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Library\Schematic\Exceptions\SchematicApplicationException;
use Symfony\Component\Yaml\Parser;

/**
 * Class SchematicInterpretter
 * @package Library\Schematic\Interpretter
 * @author Andre Figueira <andre.figueira@me.com>
 */
class SchematicInterpretter
{
	/**
	 * @var Object
	 */
	protected $di;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var \League\Flysystem\Filesystem
	 */
	protected $fileSystem;

	public function __construct()
	{
		$adapter = new Local(ROOT_PATH);

		$this->setFileSystem(new Filesystem($adapter));
	}

	/**
	 * @return Object
	 */
	public function getDi()
	{
		return $this->di;
	}

	/**
	 * @param Object $di
	 * @return $this
	 */
	public function setDi($di)
	{
		$this->di = $di;

		return $this;
	}

	/**
	 * @return Filesystem
	 */
	public function getFileSystem()
	{
		return $this->fileSystem;
	}

	/**
	 * @param Filesystem $fileSystem
	 * @return $this
	 */
	public function setFileSystem($fileSystem)
	{
		$this->fileSystem = $fileSystem;

		return $this;
	}

	public function getDatabases()
	{
		foreach ($this->requiredConfigs() as $requiredConfigItem) {
			if (array_key_exists($requiredConfigItem, $this->getConfig()) === false) {
				throw new SchematicApplicationException($requiredConfigItem . ' is missing from the config...');
			}
		}

		if ($this->getConfig() === null) {
			throw new SchematicApplicationException('Config must be loaded');
		}

		$contents = $this->getFileSystem()->listContents($this->getConfig()['directory']);

		$databases = [];

		foreach ($contents as $object) {
			if ($object['type'] == 'dir') {
				$subContents = $this->getFileSystem()->listContents($object['path']);

				foreach ($subContents as $subObject) {
					if ($subObject['type'] == 'file' ) {
						if ($subObject['extension'] == 'yaml') {
							$data = @file_get_contents($subObject['path']);

							if ($data) {
								$yaml = new Parser();

								$value = $yaml->parse($data);

								$result = $value = json_decode(json_encode($value), true);

								$databases[] = $result;

							} else {
								throw new SchematicApplicationException('Failed to load (' . $subObject['path'] . ') error (' . error_get_last()['message'] . ')');
							}
						}
					}
				}

				return $databases;
			}
		}
	}

	/**
	 * Config parameters which must be set
	 *
	 * @return array
	 */
	protected function requiredConfigs()
	{
		return [
			'fileType',
			'directory',
			'driver',
			'environmentConfigs',
		];
	}

	/**
	 * @return mixed
	 */
	public function getConfig()
	{
		return $this->di->get('config');
	}

	protected function multiKeyExists(array $array, $key) {
		if (array_key_exists($key, $array)) {
			return true;
		}

		foreach ($array as $k => $v) {
			if (!is_array($v)) {
				continue;
			}

			if (array_key_exists($key, $v)) {
				return true;
			}
		}

		return false;
	}
}