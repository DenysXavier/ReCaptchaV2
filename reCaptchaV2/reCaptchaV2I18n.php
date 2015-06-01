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

/**
* Class that implements internationalization for ReCaptchaV2.
*
* This class uses files named after language codes containing keys to refer to specific strings used by ReCaptchaV2 and Google reCAPTCHA.
*
* @author Denys W. Xavier
* @version 1.0.3
* @license Apache License 2.0
*/
class ReCaptchaV2I18n {
	/** @var array $availableLanguages List of available languages for Google reCAPTCHA. */
	private static $availableLanguages = array ("ar", "bg", "ca", "zh-CN", "zh-TW", "hr", "cs", "da", "nl", "en-GB", "en", "fil", "fi", "fr", "fr-CA", "de", "de-AT", "de-CH", "el", "iw", "hi", "hu", "id", "it", "ja", "ko", "lv", "lt", "no", "fa", "pl", "pt", "pt-BR", "pt-PT", "ro", "ru", "sr", "sk", "sl", "es", "es-419", "sv", "th", "tr", "uk", "vi");
	/** @var array $translatedLanguages List of languages that ReCaptchaV2 has already been translated into. */
	private static $translatedLanguages = array("en", "pt-BR");
	
	/** @var string $lang Language code being used for Google reCAPTCHA. */
	private $lang;
	/** @var array $strings Cache of strings read from the internationalization file for ReCaptchaV2. */
	private $strings;
	
	/**
	* Creates a new instance of ReCaptchaV2I18n.
	*
	* @param string $lang Language code to be used by Google reCAPTCHA and for ReCaptchaV2 internationalization.
	*
	* @return new
	*/
	public function __construct ($lang = NULL) {
		$this->setLang($lang);
	}
	
	/**
	* Gets a list of available languages for Google reCAPTCHA.
	*
	* @return array
	*/
	public static function getAvailableLanguages() {
		return self::$availableLanguages;
	}
	
	/**
	* Gets the language code being used by Google reCAPTCHA.
	*
	* @return string
	*/
	public function getLang() {
		return $this->lang;
	}
	
	/**
	* Gets a specific string from the cache of strings read from the internationalization file for ReCaptchaV2.
	*
	* @param string $key The key of the string to be returned.
	*
	* @return string
	*/
	public function getString($key) {
		return $this->strings[$key];
	}
	
	/**
	* Tries to load the strings from the internationalization file into the property $strings.
	*
	* @param string $baseFilename Base file name of the internationalization file to be read. Basically, it is the chosen language code.
	*
	* @throws Exception If internationalization file is missing for the specified $baseFilename.
	*/
	private function loadStrings($baseFilename) {
		$filename = $baseFilename . ".txt";
		if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $filename)) {
			$this->strings = parse_ini_file($filename);
		} else {
			throw new Exception("Internationalization file is missing for '" . $baseFilename . "' language code.");
		}
	}
	
	/**
	* Sets the language code to be used by Google reCAPTCHA and for ReCaptchaV2 internationalization.
	*
	* @param string $lang The language code to be used.
	*
	* @return self
	*/
	public function setLang($lang = NULL) {
		/* When no suitable option is to be found, then, set $stringsLang to "en" as default */
		$stringsLang = "en";
			
		/* if no language code is informed, then, automatically try to figure out the best option for ReCaptchaV2 internationalization */
		if (is_null($lang)) {
			$acceptedLangs = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			foreach($acceptedLangs as $acceptedLang) {
				$token = strtok($acceptedLang, ";");
				if (in_array($token, self::$translatedLanguages)) {
					$stringsLang = $token;
					break;
				}
			}
		} else if (in_array($lang, self::$translatedLanguages)) {
			$stringsLang = $lang;
		}
		
		$this->lang = $lang;
		$this->loadStrings($stringsLang);
		return $this;
	}
}
?>