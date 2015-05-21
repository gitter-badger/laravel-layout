<?php namespace Layout\Lavary;

use Lavary\Menu\Menu as BaseMenu;
use Lavary\Menu\Collection;

class Menu extends BaseMenu {
	
	/**
	* Configuration data
	*
	* @var array $config
	*/
	protected $config;
	/**
	 * Initializing the menu builder
	 */
	public function __construct(array $config = array())
	{
		// creating a collection for storing menus
		$this->collection = new Collection();
		$this->config = $config;
	}
	
	
	/**
	 * Loads and merges configuration data
	 *
	 * @param  string  $name
	 * @return array
	 */
	public function loadConf($name) {
		
		$name = strtolower($name);
		
		if (isset($this->config[$name]) && is_array($this->config[$name]))
		{
			return array_merge($this->config['default'], $this->config[$name] );
		}
		return $this->config['default'];
	}
}
