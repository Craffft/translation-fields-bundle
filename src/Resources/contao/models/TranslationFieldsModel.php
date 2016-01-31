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

use Craffft\TranslationFieldsBundle\Util\WidgetUtil;

class TranslationFieldsModel extends \Model
{
    protected static $strTable = 'tl_translation_fields';

    /**
     * @param $intFid
     * @param $strLanguage
     * @param array $arrOptions
     * @return mixed
     */
    public static function findOneByFidAndLanguage($intFid, $strLanguage, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrColumns = array("$t.fid=? AND $t.language=?");
        $arrValues = array($intFid, $strLanguage);

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }

    /**
     * @return int|mixed|null
     */
    public static function getNextFid()
    {
        $t = static::$strTable;

        $intFid = \Database::getInstance()->prepare("SELECT (fid + 1) AS nextFid FROM $t ORDER BY fid DESC")->limit(1)->execute()->nextFid;
        $intFid = ($intFid === null) ? 1 : $intFid;

        return $intFid;
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
            $arrValues = WidgetUtil::addFallbackValueToEmptyField($arrValues);
        }

        if (is_array($arrLanguageKeys) && count($arrLanguageKeys)) {
            foreach ($arrLanguageKeys as $strLanguageKey) {
                // If current fid is correct
                if (is_numeric($intFid) && $intFid > 0) {
                    // Get existing translation object by fid
                    $objTranslation = self::findOneByFidAndLanguage($intFid, $strLanguageKey);

                    // Get new translation object by fid
                    if ($objTranslation === null) {
                        // Create translation object
                        $objTranslation = new self();
                        $objTranslation->language = $strLanguageKey;
                        $objTranslation->fid = $intFid;
                    }
                }

                // Get new translation object with new fid
                if ($objTranslation === null) {
                    // Get next fid
                    $intFid = self::getNextFid();

                    // Create translation object
                    $objTranslation = new self();
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
            $objTranslation = self::findByFid($intFid);

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
}
