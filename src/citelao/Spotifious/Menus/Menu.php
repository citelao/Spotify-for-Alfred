<?php
namespace Spotifious\Menus;

interface Menu {
	public function __construct($query, $alfred, $api);
	public function output();
}