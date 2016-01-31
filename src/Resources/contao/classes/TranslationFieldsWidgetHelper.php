<?php

/*
 * This file is part of the Translation Fields Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TranslationFields;

use Craffft\TranslationFieldsBundle\Service\Languages;

class TranslationFieldsWidgetHelper
{
    /**
     * @var array
     */
    private static $arrLanguages = array();

    /**
     * @param $varInput
     * @return array
     */
    public static function addFallbackValueToEmptyField($varInput)
    {
        if (is_array($varInput)) {
            // Add fallback text to other languages
            if (count($varInput) > 0) {
                $strFallbackValue = $varInput[key($varInput)];

                foreach ($varInput as $key => $value) {
                    if (strlen($value) < 1) {
                        $varInput[$key] = $strFallbackValue;
                    }
                }
            }
        }

        return $varInput;
    }

    /**
     * @param $strAttributes
     * @return string
     */
    public static function getCleanedAttributes($strAttributes)
    {
        $strAttributes = preg_replace('/(^|\W)required(=".*"|)/', '', $strAttributes);
        $strAttributes = str_replace('  ', ' ', $strAttributes);

        return $strAttributes;
    }

    /**
     * @param array $arrValues
     * @param int $intFid
     * @return int
     */
    public static function saveValuesAndReturnFid(array $arrValues, $intFid = 0)
    {
        /* @var $objLanguages Languages */
        $objLanguages = \System::getContainer()->get('craffft.translation_fields.service.languages');
        $arrLanguageKeys = $objLanguages->getLanguageKeys();

        // Check if translation fields should not be empty saved
        if (!$GLOBALS['TL_CONFIG']['dontfillEmptyTranslationFields']) {
            // Add fallback text to empty values
            $arrValues = self::addFallbackValueToEmptyField($arrValues);
        }

        if (is_array($arrLanguageKeys) && count($arrLanguageKeys)) {
            foreach ($arrLanguageKeys as $strLanguageKey) {
                // If current fid is correct
                if (is_numeric($intFid) && $intFid > 0) {
                    // Get existing translation object by fid
                    $objTranslation = \TranslationFieldsModel::findOneByFidAndLanguage($intFid, $strLanguageKey);

                    // Get new translation object by fid
                    if ($objTranslation === null) {
                        // Create translation object
                        $objTranslation = new \TranslationFieldsModel();
                        $objTranslation->language = $strLanguageKey;
                        $objTranslation->fid = $intFid;
                    }
                }

                // Get new translation object with new fid
                if ($objTranslation === null) {
                    // Get next fid
                    $intFid = \TranslationFieldsModel::getNextFid();

                    // Create translation object
                    $objTranslation = new \TranslationFieldsModel();
                    $objTranslation->language = $strLanguageKey;
                    $objTranslation->fid = $intFid;
                }

                // Set content value
                if (isset($arrValues[$strLanguageKey])) {
                    $objTranslation->content = $arrValues[$strLanguageKey];
                }

                // Set current timestamp
                $objTranslation->tstamp = time();

                // Save
                $objTranslation->save();
            }
        }

        return $intFid;
    }

    /**
     * @param $intFid
     * @param bool $onlyActiveLanguages
     * @return array
     */
    public static function getTranslationsByFid($intFid, $onlyActiveLanguages = false)
    {
        /* @var $objLanguages Languages */
        $objLanguages = \System::getContainer()->get('craffft.translation_fields.service.languages');
        $arrData = $objLanguages->getLanguagesWithEmptyValue();

        if (is_numeric($intFid) && $intFid > 0) {
            $objTranslation = \TranslationFieldsModel::findByFid($intFid);

            if ($objTranslation !== null) {
                while ($objTranslation->next()) {
                    $arrData[$objTranslation->language] = $objTranslation->content;
                }
            }
        }

        // If only active languages should be returned
        if ($onlyActiveLanguages) {
            $arrActiveData = array();
            $arrLanguageKeys = $objLanguages->getLanguageKeys();

            if (is_array($arrLanguageKeys) && count($arrLanguageKeys) > 0) {
                foreach ($arrLanguageKeys as $strLanguageKey) {
                    $arrActiveData[$strLanguageKey] = (!isset($arrData[$strLanguageKey]) ? '' : $arrData[$strLanguageKey]);
                }
            }

            // Replace data with active data
            $arrData = $arrActiveData;
        }

        // Return data array
        return $arrData;
    }




    /**
     * @return string
     */
    public static function getCurrentTranslationLanguageButton()
    {
        /* @var $objLanguages Languages */
        $objLanguages = \System::getContainer()->get('craffft.translation_fields.service.languages');
        $arrLanguageKeys = $objLanguages->getLanguageKeys();
        $strFlagname = (strtolower(strlen($arrLanguageKeys[0]) > 2 ? substr($arrLanguageKeys[0], -2) : $arrLanguageKeys[0]));

        // Set empty flagname, if flag doesn't exist
        if (!file_exists(sprintf('%s/web/%s/images/flag-icons/%s.png',
            TL_ROOT,
            CRAFFFT_TRANSLATION_FIELDS_PUBLIC_PATH,
            $strFlagname))
        ) {
            $strFlagname = 'xx';
        }

        $arrLanguages = $objLanguages->getLanguages();

        // Generate current translation language button
        $strButton = sprintf('<span class="tf_button"><img src="%s/images/flag-icons/%s.png" width="16" height="11" alt="%s"></span>',
            CRAFFFT_TRANSLATION_FIELDS_PUBLIC_PATH,
            $strFlagname,
            $arrLanguages[$arrLanguageKeys[0]]
        );

        return $strButton;
    }

    /**
     * @param array $arrItems
     * @return string
     */
    public static function getTranslationLanguagesList(array $arrItems)
    {
        // Generate langauge list
        $arrLanguagesList = array();
        $i = 0;

        /* @var $objLanguages Languages */
        $objLanguages = \System::getContainer()->get('craffft.translation_fields.service.languages');
        $arrLanguages = $objLanguages->getLanguages();

        foreach ($arrLanguages as $key => $value) {
            $strFlagname = (strtolower(strlen($key) > 2 ? substr($key, -2) : $key));

            // Set empty flagname, if flag doesn't exist
            if (!file_exists(sprintf('%s/web/%s/images/flag-icons/%s.png',
                TL_ROOT,
                CRAFFFT_TRANSLATION_FIELDS_PUBLIC_PATH,
                $strFlagname))
            ) {
                $strFlagname = 'xx';
            }

            $strLanguageIcon = sprintf('<img src="%s/images/flag-icons/%s.png" width="16" height="11" alt="%s">',
                CRAFFFT_TRANSLATION_FIELDS_PUBLIC_PATH,
                $strFlagname,
                $value
            );

            $arrLanguagesList[] = sprintf('<li id="lng_list_item_%s" class="tf_lng_item%s">%s%s</li>',
                $key,
                (isset($arrItems[$key]) && strlen(specialchars($arrItems[$key])) > 0) ? ' translated' : '',
                $strLanguageIcon,
                $value);
            $i++;
        }

        $strLanguageList = sprintf('<ul class="tf_lng_list">%s</ul>',
            implode(' ', $arrLanguagesList));

        return $strLanguageList;
    }
}
