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
require_once ("reCaptchaV2I18n.php");

class ReCaptchaV2 {	
	const API_URL = "https://www.google.com/recaptcha/api";

	private $i18n;
	private $includeNoScript;
	private $lastErrors = NULL;
	private $secretKey;
	private $siteKey;
	private $theme;
	private $type;
	private $valid = NULL;

	public function __construct ($siteKey, $secretKey, $theme = "light", $type = "image", $lang = NULL, $includeNoScript = false) {
		$this->i18n = new ReCaptchaV2I18n($lang);
		
		if (empty($siteKey) || empty($secretKey)) {
			throw new InvalidArgumentException($this->i18n->getString("ctor_missing_keys"));
		} else {
			$this->siteKey = $siteKey;
			$this->secretKey = $secretKey;
			$this->theme = $theme;
			$this->type = $type;
		}
	}

	public function getLang() {
		return $this->i18n->getLang();
	}

	public function getLastErrors() {
		return $this->lastErrors;
	}

	public function getLastErrorsAsString() {
		if (is_array($this->lastErrors)) {
			return implode("; ", $this->getLastErrorDescriptions());
		}
		return "";
	}
	
	public function getLastErrorCodes() {
		return array_keys($this->lastErrors);
	}

	public function getLastErrorDescriptions() {
		return array_values($this->lastErrors);
	}

	public function getScript() {
		$jsURL = self::API_URL . ".js";
		$urlVars = array();
		
		if (!is_null($this->i18n->getLang())) {
			$urlVars["hl"] = $this->i18n->getLang();
		}
		
		if (count($urlVars) > 0) {
			$jsURL .= "?" . http_build_query($urlVars);		
		}
		
		return '<script src="' . $jsURL . '" type="text/javascript" async="async" defer="defer"></script>';
	}

	public function getTheme() {
		return $this->theme;
	}
	
	public function getType() {
		return $this->type;
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
        <iframe src="' . self::API_URL . '/fallback?k=' . $this->siteKey . '" frameborder="0" scrolling="no" style="width: 302px; height:352px; border-style: none;"></iframe>
      </div>
      <div style="width: 250px; height: 80px; position: absolute; border-style: none; bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
        <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 80px; border: 1px solid #c1c1c1; margin: 0px; padding: 0px; resize: none;" value="">
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
				$errorCodes = $response->{"error-codes"};
				foreach($errorCodes as $errCode) {
					$this->lastErrors[$errCode] = $this->i18n->getString($errCode);
				}
			}
		}
		return $this->valid;
	}

	public function setLang($lang) {		
		return $this->i18n->setLang($lang);
	}

	public function setTheme($theme) {
		$this->theme = $theme;
		return $this;
	}

	public function setType($type) {
		$this->type = $type;
		return $this;
	}
}
?>