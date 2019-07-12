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

use Contao\TextArea;
use Craffft\TranslationFieldsBundle\Service\Languages;
use Craffft\TranslationFieldsBundle\Util\WidgetUtil;
use TranslationFields\TranslationFieldsModel;

class TranslationTextArea extends TextArea
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $intRows = 12;
    protected $intCols = 80;
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

            case 'rows':
                $this->intRows = $varValue;
                break;

            case 'cols':
                $this->intCols = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * @param $varInput
     * @return mixed
     */
    protected function validator($varInput)
    {
        // Get language id
        $intId = ($this->activeRecord) ? $this->activeRecord->{$this->strName} : $GLOBALS['TL_CONFIG'][$this->strName];

        // Check if translation fields should not be empty saved
        if (!$GLOBALS['TL_CONFIG']['dontfillEmptyTranslationFields']) {
            // Fill all empty fields with the content of the fallback field
            $varInput = WidgetUtil::addFallbackValueToEmptyField($varInput);
            parent::validator($varInput);
        } else {
            // Check only the first field
            parent::validator($varInput[key($varInput)]);
        }

        // Check if array
        if (is_array($varInput)) {
            if (!parent::hasErrors()) {
                // Save values and return fid
                return TranslationFieldsModel::saveValuesAndReturnFid(
                    $varInput,
                    $intId
                );
            }
        }

        return $intId;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generate()
    {
        // Get post array
        $arrPost = \Input::post($this->strName);

        // Get languages array with values
        $this->varValue = TranslationFieldsModel::getTranslationsByFid($this->varValue);

        /* @var $objLanguages Languages */
        $objLanguages = \System::getContainer()->get('craffft.translation_fields.service.languages');
        $arrLngInputs = $objLanguages->getLanguageKeys();

        $arrFields = array();

        foreach ($arrLngInputs as $i => $strLanguage) {
            $strRte = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['rte'];
            $key = 'ctrl_' . $this->strId . '_' . $strLanguage;

            $strScript = $this->getRteScriptByTranslatedField($strRte, $key);

            $arrFields[] = sprintf('<div class="tf_field_wrap tf_field_wrap_%s%s"><textarea name="%s[%s]" id="%s" class="tf_field tl_textarea" rows="%s" cols="%s"%s onfocus="Backend.getScrollOffset()">%s</textarea>%s</div>',
                $strLanguage,
                ($i > 0) ? ' hide' : '',
                $this->strName,
                $strLanguage,
                $key,
                $this->intRows,
                $this->intCols,
                $i > 0 ? WidgetUtil::getCleanedAttributes($this->getAttributes()) : $this->getAttributes(),
                \StringUtil::specialchars((isset($arrPost[$strLanguage]) && $arrPost[$strLanguage] !== null) ? $arrPost[$strLanguage] : @$this->varValue[$strLanguage]),
                $strScript
            );
        }

        // Get language button
        $strLngButton = WidgetUtil::getCurrentTranslationLanguageButton();

        // Get language list
        $strLngList = WidgetUtil::getTranslationLanguagesList(
            is_array($this->varValue) ? $this->varValue : array()
        );

        return sprintf('<div id="ctrl_%s_wrap" class="tf_wrap tf_textarea_wrap%s%s">%s%s%s</div>%s',
            $this->strId,
            (($this->strClass != '') ? ' ' . $this->strClass : ''),
            (!empty($this->rte) ? ' rte' : ''),
            implode(' ', $arrFields),
            $strLngList,
            $strLngButton,
            $this->wizard);
    }

    /**
     * @param $rte
     * @param $selector
     * @return string
     * @throws \Exception
     */
    protected function getRteScriptByTranslatedField($rte, $selector)
    {
        $updateMode = '';

        if (!empty($rte))
        {
            list ($file, $type) = explode('|', $rte, 2);

            $fileBrowserTypes = array();
            $pickerBuilder = \System::getContainer()->get('contao.picker.builder');

            foreach (array('file' => 'image', 'link' => 'file') as $context => $fileBrowserType)
            {
                if ($pickerBuilder->supportsContext($context))
                {
                    $fileBrowserTypes[] = $fileBrowserType;
                }
            }

            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_' . $file);
            $objTemplate->selector = $selector;
            $objTemplate->type = $type;
            $objTemplate->fileBrowserTypes = $fileBrowserTypes;

            // Deprecated since Contao 4.0, to be removed in Contao 5.0
            $objTemplate->language = \Backend::getTinyMceLanguage();

            $updateMode = $objTemplate->parse();

            unset($file, $type, $pickerBuilder, $fileBrowserTypes, $fileBrowserType);
        }

        return $updateMode;
    }
}
