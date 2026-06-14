<?php

namespace tests\unit\models\forms;

use app\models\forms\VkEmailForm;
use tests\unit\DbTestCase;

class VkEmailFormTest extends DbTestCase
{
    public function testExistingEmailIsRejected(): void
    {
        $form = new VkEmailForm(['email' => 'customer@example.com']);

        verify($form->validate())->false();
        verify($form->errors)->arrayHasKey('email');
    }

    public function testNewEmailIsAccepted(): void
    {
        $form = new VkEmailForm(['email' => 'new-vk@example.com']);

        verify($form->validate())->true();
    }
}
