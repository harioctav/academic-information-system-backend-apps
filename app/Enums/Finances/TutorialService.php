<?php

namespace App\Enums\Finances;

use App\Traits\EnumsToArray;

enum TutorialService: string
{
    use EnumsToArray;

    case TatapMuka = 'tatap_muka';
    case TutorialOnlineELearning = 'tutorial_online_elearning';
    case TutorialOnlineTMK = 'tutorial_online_tmk';

    public function label(): string
    {
        return match ($this) {
            self::TatapMuka => 'Tatap Muka (Online / Offline)',
            self::TutorialOnlineELearning => 'Tutorial Online (E-Learning.ut.ac.id)',
            self::TutorialOnlineTMK => 'Tutorial Online (TMK.ut.ac.id)',
        };
    }
}
