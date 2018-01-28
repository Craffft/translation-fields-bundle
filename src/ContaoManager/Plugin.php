<?php

namespace Craffft\TranslationFieldsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Craffft\TranslationFieldsBundle\CraffftTranslationFieldsBundle;

class Plugin
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(CraffftTranslationFieldsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['translation-fields']),
        ];
    }
}
