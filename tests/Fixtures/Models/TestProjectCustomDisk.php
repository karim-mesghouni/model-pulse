<?php

namespace Tests\Fixtures\Models;

class TestProjectCustomDisk extends TestProject
{
    public function getModelPulseAttachmentDisk(): ?string
    {
        return 's3';
    }
}
