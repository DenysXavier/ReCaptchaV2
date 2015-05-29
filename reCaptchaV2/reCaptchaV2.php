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

/**
* The main class of ReCaptchaV2 library.
*
* Takes care of the widget rendering, its validation and treats any error replied by Google reCAPTCHA.
*
* @author Denys W. Xavier
* @version 1.1.0
* @license Apache License 2.0
*/
class ReCaptchaV2 {
	/** Request Url for Google API. */
	const API_URL = "https://www.google.com/recaptcha/api";

	/** @var ReCaptchaV2I18n $i18n Internationalization definitions for ReCaptchaV2. */
	private $i18n;
	/** @var bool $includeNoScript Indicates whether or not including noscript tag along with the widget tag. */
	private $includeNoScript = false;
	/** @var array $lastErrors Last validation errors cache. */
	private $lastErrors = NULL;
	/** @var string $secretKey Secret key (or private key) provided by Google. */
	private $secretKey;
	/** @var string $siteKey Site key (or public key) provided by Google. */
	private $siteKey;
	/** @var string $theme Theme to be used by the widget. */
	private $theme = "light";
	/** @var string $type Widget type to be used. */
	private $type = "image";
	/** @var bool $valid Validation cache. */
	private $valid = NULL;

	/**
	* Creates a new instance of ReCaptchaV2.
	*
	* @param string $siteKey Site key (or public key) provided by Google.
	* @param string $secretKey Secret key (or private key) provided by Google.
	* @param string $theme Theme to be used by the widget.
	* @param string $type Widget type to be used.
	* @param string $lang Language code to be used for internationalization.
	* @param bool $includeNoScript Indicates whether or not including noscript tag along with the widget tag.
	*
	* @return new
	*
	* @throws InvalidArgumentException If any key is not provided.
	*/
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

	/**
	* Gets the language code currently used.
	*
	* @uses ReCaptchaV2I18n
	* 
	* @return string
	*/
	public function getLang() {
		return $this->i18n->getLang();
	}	

	/**
	* Gets an array containing the error codes.
	*
	* @return array
	*/
	public function getLastErrorCodes() {
		return array_keys($this->lastErrors);
	}

	/**
	* Gets an array containing the error descriptions.
	*
	* @return array
	*/
	public function getLastErrorDescriptions() {
		return array_values($this->lastErrors);
	}

	/**
	* Gets an associative array containing the last error codes replied and their descriptions.
	*
	* @return array
	*/
	public function getLastErrors() {
		return $this->lastErrors;
	}

	/**
	* Gets a string containing all error descriptions separated by a semicolon (;).
	*
	* @return string
	*/
	public function getLastErrorsAsString() {
		if (is_array($this->lastErrors)) {
			return implode("; ", $this->getLastErrorDescriptions());
		}
		return "";
	}
	
	/**
	* Gets a string that represents the script tag that refers to the Google API.
	*
	* @uses ReCaptchaI18n
	*
	* @return string
	*/
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
	/**
	* Gets the theme currently used by the widget.
	*
	* @return string
	*/
	public function getTheme() {
		return $this->theme;
	}

	/**
	* Gets the type of widget currently used.
	*
	* @return string
	*/
	public function getType() {
		return $this->type;
	}

	/**
	* Gets a string containing the necessary tags for rendering the widget.
	*
	* @return string
	*/
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

	/**
	* Validates the widget response.
	*
	* @return bool
	*
	* @throws Exception If any error occur during the execution of cURL module
	*/
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
			//curl_setopt($cUrl, CURLOPT_SSL_VERIFYPEER, false);
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

	/**
	* Sets whether or not including noscript tag along with the widget tag.
	*
	* @param bool $includeNoScript Whether or not including noscript tag along with the widget tag.
	*
	* @return self
	*/
	public function setIncludeNoScript($includeNoScript) {
		$this->includeNoScript = $includeNoScript;
		return $this;
	}
	
	/**
	* Sets the language to be used for internationalization.
	*
	* @param string $lang Language code to be used.
	*
	* @uses ReCaptchaI18n
	*
	* @return self
	*/
	public function setLang($lang) {		
		return $this->i18n->setLang($lang);
	}

	/**
	* Sets the theme to be used by the widget.
	*
	* @param string $theme Widget theme name to be used. It can be "light" or "dark".
	* @return self
	*/
	public function setTheme($theme) {
		$this->theme = $theme;
		return $this;
	}

	/**
	* Define o tipo de widget a ser utilizado.
	*
	* @param string $type Widget type name to be used. It can be "image" or "audio".
	* 
	* @return self
	*/
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
}
?>