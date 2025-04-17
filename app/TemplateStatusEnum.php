<?php

namespace App;

enum TemplateStatusEnum
{
    const CREATED = 'CREATED';
    const UPDATED = 'UPDATED';
    const CUSTOMIZABLE_1 = 'true';
    const CUSTOMIZABLE_0 = 'false';
    const MEDIA_FILE_TYPE_URL = 'url';
    const MEDIA_FILE_TYPE_IMAGE = 'image';
    const MEDIA_FILE_TYPE_VIDEO = 'video';
    const MEDIA_FILE_TYPE_DOCUMENT = 'document';
}
