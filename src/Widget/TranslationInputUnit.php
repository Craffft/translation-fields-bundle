<?php

/*
 * This file is part of the Translation Fields Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Craffft\TranslationFieldsBundle\Widget;

use Contao\InputUnit;
use Craffft\TranslationFieldsBundle\Service\Languages;
use Craffft\TranslationFieldsBundle\Util\WidgetUtil;
use TranslationFields\TranslationFieldsModel;

class TranslationInputUnit extends InputUnit
{
    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';
    protected $arrUnits = array();

    /**
     * @param string $strKey
     * @param mixed $varValue
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'maxlength':
                if ($varValue > 0) {
                    $this->arrAttributes['maxlength'] = $varValue;
                }
                break;

            case 'mandatory':
                if ($varValue) {
                    $this->arrAttributes['required'] = 'required';
                } else {
                    unset($this->arrAttributes['required']);
                }
                parent::__set($strKey, $varValue);
                break;

            case 'placeholder':
                $this->arrAttributes['placeholder'] = $varValue;
                break;

            case 'options':
                $this->arrUnits = deserialize($varValue);
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * @param mixed $varInput
     * @return mixed
     */
    protected function validator($varInput)
    {
        // Get array with language id
        $arrData = ($this->activeRecord) ? $this->activeRecord->{$this->strName} : $GLOBALS['TL_CONFIG'][$this->strName];

        if (is_array($varInput['value'])) {
            // Check if translation fields should not be empty saved
            if (!isset($GLOBALS['TL_CONFIG']['dontfillEmptyTranslationFields']) || !$GLOBALS['TL_CONFIG']['dontfillEmptyTranslationFields']) {
                // Fill all empty fields with the content of the fallback field
                $varInput['value'] = WidgetUtil::addFallbackValueToEmptyField($varInput['value']);
                parent::validator($varInput['value']);
            } else {
                // Check only the first field
                parent::validator($varInput['value'][key($varInput['value'])]);
            }
        }

        $arrData = deserialize($arrData);

        if (!is_array($arrData) || empty($arrData)) {
            $arrData = array();
        }

        // Save values and return fid
        $varInput['value'] = TranslationFieldsModel::saveValuesAndReturnFid(
            $varInput['value'],
            isset($arrData['value']) ? $arrData['value'] : 0
        );

        return $varInput;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $arrUnits = array();

        foreach ($this->arrUnits as $arrUnit) {
            $arrUnits[] = sprintf('<option value="%s"%s>%s</option>',
                \StringUtil::specialchars($arrUnit['value']),
                $this->isSelected($arrUnit),
                $arrUnit['label']);
        }

        if (!is_array($this->varValue)) {
            $this->varValue = array('value' => $this->varValue);
        }

        // Get languages array with values
        $this->varValue['value'] = TranslationFieldsModel::getTranslationsByFid($this->varValue['value']);

        /* @var $objLanguages Languages */
        $objLanguages = \System::getContainer()->get('craffft.translation_fields.service.languages');
        $arrLngInputs = $objLanguages->getLanguageKeys();

        $arrFields = array();

        foreach ($arrLngInputs as $i => $strLanguage) {
            $arrFields[] = sprintf('<div class="tf_field_wrap tf_field_wrap_%s%s"><input type="text" name="%s[value][%s]" id="ctrl_%s" class="tf_field tl_text_unit" value="%s"%s onfocus="Backend.getScrollOffset()"></div>',
                $strLanguage,
                ($i > 0) ? ' hide' : '',
                $this->strName,
                $strLanguage,
                $this->strId . '_' . $strLanguage,
                \StringUtil::specialchars(@$this->varValue['value'][$strLanguage]),
                $i > 0 ? WidgetUtil::getCleanedAttributes($this->getAttributes()) : $this->getAttributes()
            );
        }

        $strUnit = sprintf('<select name="%s[unit]" class="tl_select_unit" onfocus="Backend.getScrollOffset()">%s</select>',
            $this->strName,
            implode('', $arrUnits));

        // Get language button
        $strLngButton = WidgetUtil::getCurrentTranslationLanguageButton();

        // Get language list
        $strLngList = WidgetUtil::getTranslationLanguagesList(
            isset($this->varValue['value']) && is_array($this->varValue['value']) ? $this->varValue['value'] : array()
        );

        return sprintf('<div id="ctrl_%s" class="tf_wrap tf_text_unit_wrap%s">%s%s%s</div> %s%s',
            $this->strId,
            (($this->strClass != '') ? ' ' . $this->strClass : ''),
            implode(' ', $arrFields),
            $strLngList,
            $strLngButton,
            $strUnit,
            $this->wizard);
    }
}
