<?php

namespace Kolgaev\Tube\Enums;

/**
 * Перечисление с наименованиями полей мета данных видео с ресурсом
 */
enum MetaFormatKeys: string
{
    case id = "format_id";
    case ext = "ext";
    case resolution = "resolution";
    case width = "width";
    case height = "height";
    case fps = "fps";
    case audio_channels = "audio_channels";
    case filesize = "filesize";
    case tbr = "tbr";
    case vcodec = "vcodec";
    case vbr = "vbr";
    case acodec = "acodec";
    case abr = "abr";
    case asr = "asr";
    case format = "format";
    case format_note = "format_note";
}
