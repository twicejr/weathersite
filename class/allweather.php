<?php
    
    
    class Allweather
    {

        public static function humanWind($degrees)
        {
            if(!is_numeric($degrees))
            {
                return;
            }
            $degrees = abs($degrees);
            switch(true)
            {
                case $degrees >= 348.75 || $degrees < 11.25:
                    return 'N';
                case $degrees >= 11.25 && $degrees < 33.75:
                    return 'NNO';
                case $degrees >= 33.75 && $degrees < 56.25:
                    return 'NO';
                case $degrees >= 56.25 && $degrees < 78.75:
                    return 'ONO';
                case $degrees >= 78.75 && $degrees < 101.25:
                    return 'O';
                case $degrees >= 101.25 && $degrees < 123.75:
                    return 'OZO';
                case $degrees >= 123.75 && $degrees < 146.25:
                    return 'ZO';
                case $degrees >= 146.25 && $degrees < 168.75:
                    return 'ZZO';
                case $degrees >= 168.75 && $degrees < 191.25:
                    return 'Z';
                case $degrees >= 191.25 && $degrees < 213.75:
                    return 'ZZW';
                case $degrees >= 213.75 && $degrees < 236.25:
                    return 'ZW';
                case $degrees >= 236.25 && $degrees < 258.75:
                    return 'WZW';
                case $degrees >= 258.75 && $degrees < 281.25:
                    return 'W';
                case $degrees >= 281.25 && $degrees < 303.75:
                    return 'WNW';
                case $degrees >= 303.75 && $degrees < 326.25:
                    return 'NW';
                case $degrees >= 326.25 && $degrees < 348.75:
                    return 'NNW';
            }
        }

    }
