<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Graphodata PDF Importer',
    'description' => 'Imports PDF that\'s been converted to HTML by Adobe Acrobat and generates pages based on content',
    'category' => 'misc',
    'shy' => 0,
    'version' => '1.0.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'clearCacheOnLoad' => 0,
    'author' => 'Gordon Fassbender',
    'author_email' => 'gordon.fassbender@gmx.de',
    'author_company' => 'Graphodata AG',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.00-9.5.99',
            'typo3' => '9.5.0-10.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];