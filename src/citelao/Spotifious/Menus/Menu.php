<?php
namespace Spotifious\Menus;

interface Menu {
	public function __construct($query);
	public function output();
}