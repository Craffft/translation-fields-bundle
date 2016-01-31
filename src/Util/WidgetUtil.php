<?php

/*
 * This file is part of the Translation Fields Bundle.
 *
 * (c) Daniel Kiesel <https://github.com/iCodr8>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Craffft\TranslationFieldsBundle\Util;

use Craffft\TranslationFieldsBundle\Service\Languages;

class WidgetUtil
{
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
