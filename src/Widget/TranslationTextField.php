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

use Contao\TextField;

class TranslationTextField extends TextField
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = false;
    protected $strTemplate = 'be_widget';

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
        // Get language id
        $intId = ($this->activeRecord) ? $this->activeRecord->{$this->strName} : $GLOBALS['TL_CONFIG'][$this->strName];

        // Check if translation fields should not be empty saved
        if (!$GLOBALS['TL_CONFIG']['dontfillEmptyTranslationFields']) {
            // Fill all empty fields with the content of the fallback field
            $varInput = \TranslationFieldsWidgetHelper::addFallbackValueToEmptyField($varInput);
            parent::validator($varInput);
        } else {
            // Check only the first field
            parent::validator($varInput[key($varInput)]);
        }

        // Check if array
        if (is_array($varInput)) {
            if (!parent::hasErrors()) {
                // Save values and return fid
                return \TranslationFieldsWidgetHelper::saveValuesAndReturnFid(
                    $varInput,
                    $intId
                );
            }
        }

        return $intId;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $type = $this->hideInput ? 'password' : 'text';

        // Get post array
        $arrPost = \Input::post($this->strName);

        // Get languages array with values
        $this->varValue = \TranslationFieldsWidgetHelper::getTranslationsByFid($this->varValue);

        // Generate langauge fields
        $arrLngInputs = \System::getContainer()->get('craffft.translation_fields.service.languages')->getLanguageKeys();

        $arrFields = array();

        foreach ($arrLngInputs as $i => $strLanguage) {
            $arrFields[] = sprintf('<div class="tf_field_wrap tf_field_wrap_%s%s"><input type="%s" name="%s[%s]" id="ctrl_%s" class="tf_field tl_text" value="%s"%s onfocus="Backend.getScrollOffset()"></div>',
                $strLanguage,
                ($i > 0) ? ' hide' : '',
                $type,
                $this->strName,
                $strLanguage,
                $this->strId . '_' . $strLanguage,
                specialchars((isset($arrPost[$strLanguage]) && $arrPost[$strLanguage] !== null) ? $arrPost[$strLanguage] : @$this->varValue[$strLanguage]),
                $i > 0 ? \TranslationFieldsWidgetHelper::getCleanedAttributes($this->getAttributes()) : $this->getAttributes()
            );
        }

        // Get language button
        $strLngButton = \TranslationFieldsWidgetHelper::getCurrentTranslationLanguageButton();

        // Get language list
        $strLngList = \TranslationFieldsWidgetHelper::getTranslationLanguagesList(
            is_array($this->varValue) ? $this->varValue : array()
        );

        return sprintf('<div id="ctrl_%s" class="tf_wrap tf_text_wrap%s">%s%s%s</div>%s',
            $this->strId,
            (($this->strClass != '') ? ' ' . $this->strClass : ''),
            implode(' ', $arrFields),
            $strLngList,
            $strLngButton,
            $this->wizard);
    }
}
