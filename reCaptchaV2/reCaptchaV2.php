<?php
/**
*                  Copyright 2015 Denys W. Xavier
*
*   Licensed under the Apache License, Version 2.0 (the "License");
*   you may not use this file except in compliance with the License.
*   You may obtain a copy of the License at
*
*       http://www.apache.org/licenses/LICENSE-2.0
*
*   Unless required by applicable law or agreed to in writing, software
*   distributed under the License is distributed on an "AS IS" BASIS,
*   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*   See the License for the specific language governing permissions and
*   limitations under the License.
*/

class ReCaptchaV2 {
	const API_URL = "https://www.google.com/recaptcha/api";
	
	private $siteKey;
	private $secretKey;
	private $valid = NULL;
	private $lastErrors = NULL;
	private $theme;
	private $type;
	private $lang;
	private $includeNoScript = false;
	private $strings;
	
	public function __construct ($siteKey, $secretKey, $theme = NULL, $type = NULL, $lang = "en", $includeNoScript = false) {
		$this->setLang($lang);
		
		if (empty($siteKey) || empty($secretKey)) {
			throw new InvalidArgumentException($this->strings["ctor"]);
		} else {
			$this->siteKey = $siteKey;
			$this->secretKey = $secretKey;
			$this->theme = $theme;
			$this->type = $type;
		}
	}
	
	public function getTheme() {
		return $this->theme;
	}
	
	public function setTheme($theme) {
		$this->theme = $theme;
		return $this;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	
	public function getLang() {
		return $this->lang;
	}
	
	public function setLang($lang) {
		if ($this->lang != $lang){
			if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $lang . ".txt")) {
				$this->lang = $lang;
				$this->strings = parse_ini_file($lang . ".txt");
			} else {
				$this->lang = "en";
				$this->strings = parse_ini_file("en.txt");
			}
		}
		return $this;
	}
	
	public function getWidget() {
		$wHTML = '<div class="g-recaptcha" data-sitekey="' . $this->siteKey . '"';
		
		if (!is_null($this->theme)) {
			$wHTML .= ' data-theme="' . $this->theme . '"';
		}
		
		if (!is_null($this->type)) {
			$wHTML .= ' data-type="' . $this->type. '"';
		}
		
		$wHTML .= '></div>';
		
		if ($this->includeNoScript) {
			$wHTML .= '<noscript>
  <div style="width: 302px; height: 352px;">
    <div style="width: 302px; height: 352px; position: relative;">
      <div style="width: 302px; height: 352px; position: absolute;">
        <iframe src="https://www.google.com/recaptcha/api/fallback?k=' . $this->siteKey . '"
                frameborder="0" scrolling="no"
                style="width: 302px; height:352px; border-style: none;">
        </iframe>
      </div>
      <div style="width: 250px; height: 80px; position: absolute; border-style: none;
                  bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
        <textarea id="g-recaptcha-response" name="g-recaptcha-response"
                  class="g-recaptcha-response"
                  style="width: 250px; height: 80px; border: 1px solid #c1c1c1;
                         margin: 0px; padding: 0px; resize: none;" value="">
        </textarea>
      </div>
    </div>
  </div>
</noscript>';
		}
		return $wHTML;
	}
	
	public function isValid(){
		if (is_null($this->valid)) {
			$data["secret"] = $this->secretKey;
			$data["response"] = $_POST["g-recaptcha-response"];
			$data["remoteip"] = $_SERVER['REMOTE_ADDR'];
			
			$cUrl = curl_init();
			curl_setopt($cUrl, CURLOPT_URL, self::API_URL . "/siteverify");
			curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cUrl, CURLOPT_POST, true);
			curl_setopt($cUrl, CURLOPT_POSTFIELDS, http_build_query($data));
			$response = json_decode(curl_exec($cUrl));		
			if (curl_errno($cUrl) > 0) {
				throw new Exception(curl_error($cUrl), curl_errno($cUrl));
			}
			curl_close($cUrl);

			$this->valid = $response->success;
			if (!$this->valid) {
				$this->lastErrors = $response->{"error-codes"};
			}
		}
		return $this->valid;
	}
	
	public function getlastErrors() {
		return $this->lastErrors;
	}
	
	public function getlastErrorsAsString() {
		if (is_array($this->lastErrors)) {
			foreach($this->lastErrors as $errors) {
				$errStrings[] = $this->strings[$errors];
			}
			return implode("; ", $errStrings);
		}
		return "";
	}	
}
?>