<?php
namespace Spotifious\Menus;

use Spotifious\Menus\Menu;
use OhAlfred\Applescript\ApplicationApplescript;

class Control implements Menu {

	protected $search;

	protected $commands = array(
		array(
			'name' => 'Next track',
			'keys' => 'skip',
			'icon' => 'include/images/commands/next.png',
			'action' => 'next⟩returnControls'
		),

		array(
			'name' => 'Previous track',
			'keys' => 'back',
			'icon' => 'include/images/commands/previous.png',
			'action' => 'previous⟩returnControls'
		)
	);

	public function __construct($query) {
		$this->search = mb_substr($query, 1);
	}

	public function output() {
		$results = array();
		foreach ($this->commands as $command) {
			if($this->search != null &&
				!@mb_stristr($command['name'] . ' ' . $command['keys'], $this->search))
				continue;

			$results[] = array(
				'title' => $command['name'],
				'arg' => $command['action'],
				'icon' => $command['icon']
			);
		}

		$results[] = array(
			'title' => 'Controls',
			'subtitle' => 'Access this menu at any time by typing `c`',
			'icon' => 'include/images/info.png',
		);

		return $results;
	}
}
