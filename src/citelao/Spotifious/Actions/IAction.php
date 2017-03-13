<?php
namespace Spotifious\Actions;

interface IAction {
	public function __construct($options, $alfred, $api);
	public function run();
}