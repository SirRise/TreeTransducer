<?php

declare(strict_types=1);

namespace Graphodata\GdPdfimport\Utility;

final class ListTypes
{
    const UL_1 = 1;
    const UL_2 = 2;
    const OL_1 = 11;
    const OL_2 = 12;
    const OL_3 = 13;
    const OL_4 = 14;

    const OL_1_PATTERN = '/\(\d{1,2}\)/';
    const OL_2_PATTERN = '/\d{1,2}\./';
    const OL_3_PATTERN = '/\w{1,2}\)/';
    const OL_4_PATTERN = '/\(\w{1,2}\)/';
}