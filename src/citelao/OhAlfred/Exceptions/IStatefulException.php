<?php
namespace OhAlfred\Exceptions;

interface IStatefulException {
	function getState(); 
    function setState(array $state);
}