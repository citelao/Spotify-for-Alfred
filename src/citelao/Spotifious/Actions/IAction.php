<?php
namespace Spotifious\Actions;

interface IAction {
	public function __construct($query, $alfred, $api);
	public function run();
}