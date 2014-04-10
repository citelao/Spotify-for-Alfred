<?php
namespace OhAlfred;

/* see: https://github.com/jdfwarrior/Workflows/blob/master/workflows.php */
class OhAlfred {
	protected $results;

	public function alfredify($r = null) {
		if($r == null)
			$r = $this->results;

		print "<?xml version='1.0'?>\r\n<items>";

		foreach($r as $result) {
			if(!isset($result['arg']))
				$result['arg'] = 'null';

			if(!isset($result['title']))
				$result['title'] = 'null';

			if(!isset($result['icon']))
				$result['icon'] = 'icon.png';

			if(!isset($result['valid']))
				$result['valid'] = 'yes';

			if(!isset($result['uid']))
				$result['uid'] = time() . "-" . $result['title'];

			if(!isset($result['autocomplete']))
				$result['autocomplete'] = '';

			if(!isset($result['subtitle']))
				$result['subtitle'] = '';

			print "\r\n\r\n";
			print "	<item uid='" . $this->escapeQuery($result['uid']) . "' arg='" . $result['arg'] . "' valid='" . $this->escapeQuery($result['valid']) . "' autocomplete='" . $this->escapeQuery($result['autocomplete']) . "'>\r\n";
			print "		<title>" . $this->escapeQuery($result['title']) . "</title>\r\n";
			print "		<subtitle>" . $this->escapeQuery($result['subtitle']) . "</subtitle>\r\n";
			print "		<icon>" . $this->escapeQuery($result['icon']) . "</icon>\r\n";
			print "	</item>\r\n";
		}

		print "</items>";
	}

	public function escapeQuery($text) {
		$text = str_replace("&", "&amp;", $text);
		$text = str_replace("'", "&#39;", $text);

		return $text;
	}
}