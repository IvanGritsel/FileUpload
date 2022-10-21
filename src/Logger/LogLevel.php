<?php

namespace App\Logger;

enum LogLevel: string
{
    case INFO = 'INFO';
    case DEBUG = 'DEBUG';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
}
