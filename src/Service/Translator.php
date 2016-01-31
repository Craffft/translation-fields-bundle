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

use Contao\Controller;
use Contao\DataContainer;
use TranslationFields\TranslationFieldsModel;

class Translator
{
    /**
     * @param $varValue
     * @param string $strForceLanguage
     * @return string
     */
    public function translateValue($varValue, $strForceLanguage = '')
    {
        // Return value if it is already translated
        if (!is_numeric($varValue)) {
            return $varValue;
        }

        $arrLanguages = array();

        // If force language is set than add it as first language param
        if (strlen($strForceLanguage)) {
            $arrLanguages[] = $strForceLanguage;
        }

        // Add current langauge and default language to languages array
        $arrLanguages[] = $GLOBALS['TL_LANGUAGE'];
        $arrLanguages[] = 'en';

        // Get translation by current language and if it doesn't exist use the english translation
        foreach ($arrLanguages as $strLanguage) {
            $objTranslation = TranslationFieldsModel::findOneByFidAndLanguage($varValue, $strLanguage);

            if ($objTranslation !== null) {
                return $objTranslation->content;
            }
        }

        // Get any translation
        $objTranslation = TranslationFieldsModel::findOneByFid($varValue);

        if ($objTranslation !== null) {
            return $objTranslation->content;
        }

        return '';
    }

    /**
     * @param DataContainer $objDC
     * @return DataContainer
     */
    public function translateDCObject(DataContainer $objDC)
    {
        // Get table
        $strTable = $objDC->current()->getTable();

        // Load current data container
        Controller::loadDataContainer($strTable);

        if (count($GLOBALS['TL_DCA'][$strTable]['fields']) > 0) {
            foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $field => $arrValues) {
                $objDC->$field = $this->translateField($arrValues['inputType'], $objDC->$field);
            }
        }

        return $objDC;
    }

    /**
     * @param array $arrDC
     * @param $strTable
     * @return array
     */
    public function translateDCArray(array $arrDC, $strTable)
    {
        if (count($GLOBALS['TL_DCA'][$strTable]['fields']) > 0) {
            foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $field => $arrValues) {
                $arrDC[$field] = $this->translateField($arrValues['inputType'], $arrDC[$field]);
            }
        }

        return $arrDC;
    }

    /**
     * @param $strInputType
     * @param $varValue
     * @return array|mixed|string
     */
    protected function translateField($strInputType, $varValue)
    {
        switch ($strInputType) {
            case 'TranslationInputUnit':
                $varValue = deserialize($varValue);

                if (is_array($varValue)) {
                    if (is_array($varValue['value']) && count($varValue['value']) > 0) {
                        $varValue['value'] = $this->translateValue($varValue['value']);
                    }
                }
                break;

            case 'TranslationTextArea':
            case 'TranslationTextField':
                $varValue = $this->translateValue($varValue);
                break;
        }

        return $varValue;
    }
}
