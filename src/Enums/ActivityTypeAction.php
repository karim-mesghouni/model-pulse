<?php

namespace Karim\ModelPulse\Enums;

enum ActivityTypeAction: string
{
    case NONE = 'none';

    case UPLOAD_FILE = 'upload_file';

    case DEFAULT = 'default';

    case PHONE_CALL = 'phone_call';

    case MEETING = 'meeting';


}
