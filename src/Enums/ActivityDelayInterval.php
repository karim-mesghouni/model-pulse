<?php

namespace Karim\ModelPulse\Enums;

enum ActivityDelayInterval: string
{
    case BEFORE_PLAN_DATE = 'before_plan_date';
    case AFTER_PLAN_DATE = 'after_plan_date';


}
