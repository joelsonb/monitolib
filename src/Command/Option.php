<?php

namespace MonitoLib\Command;

class Option
{
	const VERSION = '1.0.0';

	private $alias;
	private $name;
	private $description;
	private $required;

	/**
	* getAlias
	*
	* @return $alias
	*/
	public function getAlias ()
	{
		return $this->alias;
	}
	/**
	* getname
	*
	* @return $name
	*/
	public function getName ()
	{
		return $this->name;
	}
	/**
	* getDescription
	*
	* @return $description
	*/
	public function getDescription ()
	{
		return $this->description;
	}
	/**
	* getRequired
	*
	* @return $required
	*/
	public function getRequired ()
	{
		return $this->required;
	}
	/**
	 * setName
	 *
	 * @param $name
	 * @param $alias
	 */
	public function setName ($name, $alias = null)
	{
		$name = '-' . $name;

		if (strlen($name) > 2) {
			$name = '-' . $name;
		}

		echo "$name\n";

		$this->name = $name;

		if (!is_null($alias)) {
			$this->alias = $alias;
		}

		return $this;
	}
	/**
	 * setDescription
	 *
	 * @param $description
	 */
	public function setDescription ($description)
	{
		$this->description = $description;
		return $this;
	}
	/**
	 * setRequired
	 *
	 * @param $required
	 */
	public function setRequired ($required)
	{
		$this->required = $required;
		return $this;
	}
}