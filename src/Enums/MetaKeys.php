<?php

namespace Kolgaev\Tube\Enums;

/**
 * Перечисление с наименованиями полей мета данных видео с ресурсом
 */
enum MetaKeys: string
{
    case id = "id";
    case url = "original_url";
    case title = "title";
    case fulltitle = "fulltitle";
    case description = "description";
    case thumbnail = "thumbnail";
    case channel = "channel";
    case channel_id = "channel_id";
    case channel_url = "channel_url";
    case uploader_id = "uploader_id";
    case uploader_url = "uploader_url";
    case upload_date = "upload_date";
    case timestamp = "timestamp";
    case extractor = "extractor";
    case duration = "duration";
    case duration_string = "duration_string";
    case tags = "tags";
    case formats = "formats";
}
