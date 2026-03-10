<?php

namespace Makraz\VvvebJsBundle\DTO\Enums;

enum VvvebJsComponentGroup: string
{
    case COMMON = 'common';
    case HTML = 'html';
    case ELEMENTS = 'elements';
    case BOOTSTRAP5 = 'bootstrap5';
    case WIDGETS = 'widgets';
    case EMBEDS = 'embeds';
}
