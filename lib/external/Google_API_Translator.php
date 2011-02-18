<?php
class Google_API_translator {
	public $opts = array("text" => "", "language_pair" => "en|it");
	public $out = "";

	public function __construct() {
	}

	public function setOpts($opts) {
		if ($opts["text"] != "") {
			$this->opts["text"] = $opts["text"];
		}

		if ($opts["language_pair"] != "") {
			$this->opts["language_pair"] = $opts["language_pair"];
		}
	}

	public function translate() {
		$this->out = "";
		$google_translator_url = "http://translate.google.com/translate_t?langpair=".urlencode($this->opts["language_pair"])."&amp;";
		$google_translator_data = "text=".urlencode($this->opts["text"]);
		$gphtml = $this->postPage(array("url" => $google_translator_url, "data" => $google_translator_data));
		$out = substr($gphtml, strpos($gphtml, "<div id=result_box dir=\"ltr\">"));
		$out = substr($out, 29);
		$out = substr($out, 0, strpos($out, "</div>"));
		$this->out = utf8_encode($out);
		return $this->out;
	}

	// post form data to a given url using curl libs
	private function postPage($opts) {
		$html = "";
		if($opts["url"] != "" && $opts["data"] != "") {
			$ch = curl_init($opts["url"]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $opts["data"]);
			$html = curl_exec($ch);
			if(curl_errno($ch)) $html = "";
			curl_close ($ch);
		}
		return $html;
	}
}
?>