<?php

declare(strict_types=1);

namespace Markocupic\ContaoTinymcePluginNewslinkBundle;

use Contao\Database;
use Contao\File;
use Contao\Files;
use Contao\System;

class TinymceNewslink
{
    /**
     * Get all News items as json_encoded array
     */
    public static function getContaoNewsArchivesAsJSON(): array
    {
        $arrNews = [];
        $arrArchives = [];
        $oArchive = Database::getInstance()->execute("SELECT * FROM tl_news_archive");
        while ($oArchive->next()) {
            $oNews = Database::getInstance()->prepare("SELECT * FROM tl_news WHERE pid=?")->execute($oArchive->id);
            while ($oNews->next()) {
                if ($oNews->published) {
                    $arrNews['archive_'.$oArchive->id][] = ['value' => $oNews->id, 'text' => htmlspecialchars(html_entity_decode($oNews->headline))];
                }
            }
            // Do not list archive, if there is no item
            if (isset($arrNews['archive_'.$oArchive->id])) {
                $arrArchives[] = ['value' => $oArchive->id, 'text' => htmlspecialchars(html_entity_decode(strtoupper($oArchive->title)))];
            }
        }

        return ['archives' => $arrArchives, 'news' => $arrNews];
    }

    /**
     * loadLanguageData-Hook
     */
    public function loadLanguageData(string $strName, string $strLanguage): void
    {
        if ($strName === 'default') {
            $GLOBALS['TINYMCE']['SETTINGS']['CONFIG_ROW']['newslink_language_data'] = json_encode($GLOBALS['TL_LANG']['TINYMCE']['NEWSLINK']);
        }

    }

    /**
     * Initialize System Hook
     * Runonce
     * Copy plugin sources to assets/tinymce4/js/plugins/newslink
     */
    public function movePluginFiles(): void
    {
        $oFiles = Files::getInstance();

        if (!is_file(System::getContainer()->getParameter('kernel.project_dir').'/vendor/markocupic/contao-tinymce-plugin-newslink-bundle/src/Resources/tinymce4/js/plugins/newslink/copied.txt')) {
            $oFiles->rcopy('vendor/markocupic/contao-tinymce-plugin-newslink-bundle/src/Resources/tinymce4/js/plugins/newslink', 'assets/tinymce4/js/plugins/newslink');
            $objFile = new File('vendor/markocupic/contao-tinymce-plugin-newslink-bundle/src/Resources/tinymce4/js/plugins/newslink/copied.txt');
            $objFile->append('Plugin files "assets/tinymce4/js/plugins/newslink/plugin.min.js" already copied to the assets directory in "assets/tinymce4/js/plugins/newslink".');
            $objFile->close();
        }

    }

}
