<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class TranslationTest extends BaseTestCase
{
    public function testTranslationsIsWorking(): void
    {
        $messages = [
            'ru' => ['kalkan::messages.document_not_found', 'Документ не найден.'],
            'en' => ['kalkan::messages.document_not_found', 'Document not found.'],
            'kk' => ['kalkan::messages.document_not_found', 'Құжат табылмады.'],
        ];

        foreach ($messages as $locale => $message) {

            $this->app->setLocale($locale);

            $status = $this->get(route('check-document', Str::random()));
            $this->assertEquals(404, $status->getStatusCode());
            $this->assertEquals($status['error'], $message[1]);
        }
    }
}
