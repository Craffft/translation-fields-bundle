<?php

/*
 * This file is part of the Translation Fields Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Craffft\TranslationFieldsBundle\Service;

use Contao\BackendUser;
use Contao\System;
use TranslationFields\TranslationFieldsPageModel;

class Languages
{
    private static $arrLanguages = array();

    /**
     * @param bool $blnReload
     * @return array
     */
    public function getLanguages($blnReload = false)
    {
        if ($blnReload || !is_array(self::$arrLanguages) || count(self::$arrLanguages) < 1) {
            // Get all languages
            $arrSystemLanguages = System::getLanguages();

            // Get all used languages
            $arrLanguages = array();

            // If languages are specified
            if ($GLOBALS['TL_CONFIG']['chooseTranslationLanguages'] == '1') {
                $arrTranslationLanguages = deserialize($GLOBALS['TL_CONFIG']['translationLanguages']);

                if (is_array($arrTranslationLanguages) && $arrTranslationLanguages > 0) {
                    foreach ($arrTranslationLanguages as $strLanguage) {
                        $arrLanguages[$strLanguage] = $arrSystemLanguages[$strLanguage];
                    }
                }
            } else {
                $objRootPages = TranslationFieldsPageModel::findRootPages();

                if ($objRootPages !== null) {
                    while ($objRootPages->next()) {
                        $arrLanguages[$objRootPages->language] = $arrSystemLanguages[$objRootPages->language];
                    }
                }

                // If langauge array is empty
                if (count($arrLanguages) < 1) {
                    // Set all available languages
                    $arrLanguages = System::getLanguages(true);

                    // Set the language of the user to the top
                    if (BackendUser::getInstance()->language != null) {
                        // Get langauge value
                        $strLanguageValue = $arrLanguages[BackendUser::getInstance()->language];

                        // Remove the current language from the array
                        unset($arrLanguages[BackendUser::getInstance()->language]);

                        // Add old array to a temp array
                        $arrLanguagesTemp = $arrLanguages;

                        // Generate a new array
                        $arrLanguages = array(BackendUser::getInstance()->language => $strLanguageValue);

                        // Merge the old array into the new array
                        $arrLanguages = array_merge($arrLanguages, $arrLanguagesTemp);
                    }
                }
            }

            self::$arrLanguages = $arrLanguages;
        }

        return self::$arrLanguages;
    }

    /**
     * @param bool $blnReload
     * @return array
     */
    public function getLanguageKeys($blnReload = false)
    {
        $arrLanguages = self::getLanguages($blnReload);

        return array_keys($arrLanguages);
    }
}
