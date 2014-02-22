<?php

$a = array("test", "test");

if(is_array($a)) {
	$b = implode($a, 'test');
} else {
	$b = $a;
}

print $b;	