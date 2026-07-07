<?php

namespace Tests\Feature\Mail;

use Tests\TestCase;

class EmailDeliveryResponsiveUiTest extends TestCase
{
    public function test_email_delivery_page_has_responsive_provider_cards_history_cards_and_status_language(): void
    {
        $editView = (string) file_get_contents(resource_path('views/school/mail-settings/edit.blade.php'));
        $historyView = (string) file_get_contents(resource_path('views/school/mail-settings/history.blade.php'));

        foreach ([
            'data-provider-card',
            'sm:grid-cols-2 xl:grid-cols-6',
            'lg:grid-cols-[1.2fr_1fr]',
            'md:hidden',
            'break-all',
            'Delivery status meanings',
            'Accepted by SMTP',
            'Delivery unconfirmed',
            'Deferred',
            'Rejected',
            'Non-delivery fallback',
            'does not guarantee Inbox placement',
            '$deliveryStatusLabel',
            '$deliveryStatusTone',
        ] as $expected) {
            $this->assertStringContainsString($expected, $editView);
        }

        foreach ([
            'lg:hidden',
            'overflow-x-auto',
            '$deliveryStatusLabel',
            '$deliveryStatusTone',
            'Message bodies and credentials are never stored',
        ] as $expected) {
            $this->assertStringContainsString($expected, $historyView);
        }

        $this->assertStringNotContainsString('SMTP acceptance means Inbox delivery', $editView);
    }
}
