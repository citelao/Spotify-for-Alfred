<?php
$query = $argv[2];
$show_images = true; //($argv[1] == 'yes') ? true : false;
$maxResults = ($show_images) ? 6 : 15;
$results = array();