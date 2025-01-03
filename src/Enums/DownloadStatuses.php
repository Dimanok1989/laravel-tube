<?php

namespace Kolgaev\Tube\Enums;

enum DownloadStatuses: int
{
    case init_download = 1;
    case received_meta = 2;
    case start_download = 3;
    case download_file = 4;
    case download_file_error = 5;
    case downloaded_file = 6;
    case download_done = 7;
    case download_fail = 8;
}
