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
class ReCaptchaV2I18n {
	private static $availableLanguages = array ("ar", "bg", "ca", "zh-CN", "zh-TW", "hr", "cs", "da", "nl", "en-GB", "en", "fil", "fi", "fr", "fr-CA", "de", "de-AT", "de-CH", "el", "iw", "hi", "hu", "id", "it", "ja", "ko", "lv", "lt", "no", "fa", "pl", "pt", "pt-BR", "pt-PT", "ro", "ru", "sr", "sk", "sl", "es", "es-419", "sv", "th", "tr", "uk", "vi");
	private static $translatedLanguages = array("en", "pt-BR");
	
	private $lang;
	private $strings;
	
	public function __construct ($lang = NULL) {
		$this->setLang($lang);
	}
	
	public static function getAvailableLanguages() {
		return self::$availableLanguages;
	}
	
	public function getLang() {
		return $this->lang;
	}
	
	public function getString($key) {
		return $this->strings[$key];
	}
	
	private function loadStrings($baseFilename) {
		$filename = $baseFilename . ".txt";
		if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $filename)) {
				$this->strings = parse_ini_file($filename);
		} else {
			throw new Exception("Internationalization file missing for '" . $baseFilename . "' language code.");
		}
	}
	
	public function setLang($lang = NULL) {
		/* if no language code is informed, then, automatically try to figure out the best option for ReCaptchaV2 internationalization */
		if (is_null($lang)) {
			$acceptedLangs = explode(";", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			foreach($acceptedLangs as $acceptedLang) {
				$token = strtok($acceptedLang, ",");
				if (in_array($token, self::$translatedLanguages)) {
					$stringsLang = $token;
					break;
				}
			}
		} else if (in_array($lang, self::$translatedLanguages)) {
			$stringsLang = $lang;
		} else {
			/* If no suitable option was found, then, set $stringsLang to "en" as default */
			$stringsLang = "en";
		}
		
		$this->lang = $lang;
		$this->loadStrings($stringsLang);
		return $this;
	}
}
?>