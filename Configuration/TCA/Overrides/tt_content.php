<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Graphodata.GdPdfimport',
    'Pi1',
    'Show parsed content'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['gdpdfimport_pi1'] = 'layout,select_key,pages,recursive';