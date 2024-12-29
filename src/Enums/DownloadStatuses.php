<?php

namespace Kolgaev\Tube\Enums;

enum DownloadStatuses: int
{
    case start_download = 1;
    case gets_metadata = 2;
}
