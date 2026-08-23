<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\MailTestRedirect;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Tests\TestCase;

/**
 * MAIL_TEST_REDIRECT_TO sends every message of a deployment to one address.
 * See App\Mail\MailTestRedirect.
 *
 * Each test boots the application with the variable set or unset, exactly as
 * a revision would, and sends through the real Mailer on the array transport,
 * so what is asserted is the delivered message: its To, Cc, Bcc, and the
 * envelope recipients the transport would hand to the server.
 */
final class MailTestRedirectTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('MAIL_TEST_REDIRECT_TO');
        putenv('MAIL_MAILER');

        parent::tearDown();
    }

    public function test_when_set_every_message_goes_to_the_redirect_address_only(): void
    {
        $this->bootWith(['MAIL_MAILER=array', 'MAIL_TEST_REDIRECT_TO=inbox@example.test']);

        $this->assertSame('inbox@example.test', config('mail.to.address'),
            'The config key must have carried the variable into the global to-address.');

        $sent = $this->sendOne();
        $email = $sent->getOriginalMessage();

        $this->assertSame(['inbox@example.test'], $this->addresses($email->getTo()),
            'To must be replaced by the redirect address.');
        $this->assertSame([], $this->addresses($email->getCc()), 'Cc must be dropped.');
        $this->assertSame([], $this->addresses($email->getBcc()), 'Bcc must be dropped.');
        $this->assertSame(['inbox@example.test'], $this->addresses($sent->getEnvelope()->getRecipients()),
            'The envelope the transport would hand to the server must name only the redirect address.');
        $this->assertSame(MailTestRedirect::RECIPIENT_NAME, $email->getTo()[0]->getName());
    }

    public function test_when_unset_recipients_are_left_exactly_as_addressed(): void
    {
        $this->bootWith(['MAIL_MAILER=array']);

        $this->assertNull(config('mail.to'), 'No global to-address may exist without the variable.');

        $sent = $this->sendOne();
        $email = $sent->getOriginalMessage();

        $this->assertSame(['customer@example.test'], $this->addresses($email->getTo()));
        $this->assertSame(['kitchen@example.test'], $this->addresses($email->getCc()));
        $this->assertSame(['owner@example.test'], $this->addresses($email->getBcc()));
        $this->assertSame(
            ['customer@example.test', 'kitchen@example.test', 'owner@example.test'],
            $this->addresses($sent->getEnvelope()->getRecipients()),
        );
    }

    /**
     * A value that is not an address must stop the application rather than
     * be ignored, because "ignored" on a test revision means real recipients.
     */
    public function test_an_invalid_value_refuses_to_boot(): void
    {
        putenv('MAIL_MAILER=array');
        putenv('MAIL_TEST_REDIRECT_TO=not-an-address');

        $this->expectException(InvalidArgumentException::class);

        $this->refreshApplication();
    }

    /** @param list<string> $env */
    private function bootWith(array $env): void
    {
        foreach ($env as $pair) {
            putenv($pair);
        }

        $this->refreshApplication();
    }

    /**
     * One message addressed like an order alert would be: a customer in To,
     * the kitchen in Cc, the owner in Bcc.
     */
    private function sendOne(): SentMessage
    {
        Mail::raw('Redirect test body', function (Message $message): void {
            $message->to('customer@example.test', 'Customer')
                ->cc('kitchen@example.test', 'Kitchen')
                ->bcc('owner@example.test', 'Owner')
                ->subject('Redirect test');
        });

        $messages = Mail::getSymfonyTransport()->messages();
        $this->assertCount(1, $messages, 'Exactly one message should have been handed to the array transport.');

        return $messages->first();
    }

    /**
     * @param  array<int, Address>  $addresses
     * @return list<string>
     */
    private function addresses(array $addresses): array
    {
        return array_values(array_map(static fn ($address): string => $address->getAddress(), $addresses));
    }
}
