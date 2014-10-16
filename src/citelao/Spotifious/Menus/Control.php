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
			'action' => 'spotify⟩next track'
		),

		array(
			'name' => 'Previous track',
			'keys' => 'back',
			'action' => 'spotify⟩previous track'
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
				'arg' => $command['action']
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
