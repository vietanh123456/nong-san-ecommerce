<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_user_can_reset_password_with_emailed_otp(): void
    {
        Mail::fake();
        config(['queue.default' => 'database']);

        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => 'old-password-123',
        ]);

        $this->post(route('password.otp.send'), [
            'email' => $user->email,
        ])
            ->assertRedirect(route('password.otp.form', [
                'email' => $user->email,
            ]));

        $otpData = Cache::get(
            'password-reset-otp:'.hash('sha256', $user->email)
        );
        $createdAt = Carbon::parse($otpData['created_at']);
        $expiresAt = Carbon::parse($otpData['expires_at']);

        $this->assertSame(25200, $createdAt->timezone->getOffset($createdAt));
        $this->assertEquals(
            300,
            $createdAt->diffInSeconds($expiresAt)
        );

        $otp = null;

        Mail::assertSent(
            PasswordResetOtpMail::class,
            function (PasswordResetOtpMail $mail) use (&$otp, $user): bool {
                $otp = $mail->code;

                return $mail->hasTo($user->email);
            }
        );
        Mail::assertNothingQueued();

        $otpScreen = $this->get(route('password.otp.form', [
            'email' => $user->email,
        ]));
        $otpScreen
            ->assertOk()
            ->assertSee('Mã OTP có hiệu lực trong:', false)
            ->assertSee('id="otp-timer"', false)
            ->assertSee('id="update-password"', false)
            ->assertDontSee('id="update-password" type="submit" disabled', false);

        $this->post(route('password.otp.reset'), [
            'email' => $user->email,
            'otp' => $otp,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check(
            'new-password-123',
            $user->fresh()->password
        ));

        $this->assertNull(Cache::get(
            'password-reset-otp:'.hash('sha256', $user->email)
        ));
    }

    public function test_unknown_email_shows_error_on_otp_screen_without_sending_mail(): void
    {
        Mail::fake();

        $response = $this->post(route('password.otp.send'), [
            'email' => 'missing@example.com',
        ]);

        $response
            ->assertRedirect(route('password.otp.form', [
                'email' => 'missing@example.com',
            ]))
            ->assertSessionHasErrors([
                'email' => 'Email không tồn tại trong hệ thống hoặc không đúng định dạng!',
            ]);

        $this->get(route('password.otp.form', [
            'email' => 'missing@example.com',
        ]))
            ->assertOk()
            ->assertSeeText('Email không tồn tại trong hệ thống hoặc không đúng định dạng!');

        Mail::assertNothingOutgoing();
    }

    public function test_invalid_email_format_shows_error_on_otp_screen_without_sending_mail(): void
    {
        Mail::fake();

        $response = $this->post(route('password.otp.send'), [
            'email' => 'not-an-email',
        ]);

        $response
            ->assertRedirect(route('password.otp.form', [
                'email' => 'not-an-email',
            ]))
            ->assertSessionHasErrors([
                'email' => 'Email không tồn tại trong hệ thống hoặc không đúng định dạng!',
            ]);

        $this->get(route('password.otp.form', [
            'email' => 'not-an-email',
        ]))
            ->assertOk()
            ->assertSeeText('Email không tồn tại trong hệ thống hoặc không đúng định dạng!');

        Mail::assertNothingOutgoing();
    }

    public function test_mail_delivery_failure_displays_error_and_discards_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer@example.com',
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with($user->email)
            ->andThrow(new \RuntimeException('SMTP connection failed.'));

        $this->post(route('password.otp.send'), [
            'email' => $user->email,
        ])
            ->assertRedirect(route('password.otp.form', [
                'email' => $user->email,
            ]))
            ->assertSessionHasErrors([
                'email' => 'Gửi OTP qua Gmail SMTP thất bại. Vui lòng kiểm tra App Password 16 ký tự và cấu hình SMTP rồi thử lại.',
            ]);

        $this->assertNull(Cache::get(
            'password-reset-otp:'.hash('sha256', $user->email)
        ));
    }

    public function test_password_reset_rejects_passwords_that_do_not_meet_strength_rules(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => 'old-password-123',
        ]);

        $this->post(route('password.otp.send'), [
            'email' => $user->email,
        ]);

        $otp = null;
        Mail::assertSent(
            PasswordResetOtpMail::class,
            function (PasswordResetOtpMail $mail) use (&$otp): bool {
                $otp = $mail->code;

                return true;
            }
        );

        foreach ([
            ['password' => '12345678', 'confirmation' => '12345678'],
            ['password' => 'abcdefgh', 'confirmation' => 'abcdefgh'],
            ['password' => 'Abc123', 'confirmation' => 'Abc123'],
        ] as $invalidPassword) {
            $this->from(route('password.otp.form', ['email' => $user->email]))
                ->post(route('password.otp.reset'), [
                    'email' => $user->email,
                    'otp' => $otp,
                    'password' => $invalidPassword['password'],
                    'password_confirmation' => $invalidPassword['confirmation'],
                ])
                ->assertRedirect(route('password.otp.form', ['email' => $user->email]))
                ->assertSessionHasErrors([
                    'password' => 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm cả chữ và số.',
                ]);
        }

        $this->from(route('password.otp.form', ['email' => $user->email]))
            ->post(route('password.otp.reset'), [
                'email' => $user->email,
                'otp' => $otp,
                'password' => 'Newpassword123',
                'password_confirmation' => 'Differentpassword123',
            ])
            ->assertRedirect(route('password.otp.form', ['email' => $user->email]))
            ->assertSessionHasErrors([
                'password' => 'Mật khẩu xác nhận không khớp.',
            ]);

        $this->from(route('password.otp.form', ['email' => $user->email]))
            ->post(route('password.otp.reset'), [
                'email' => $user->email,
                'otp' => $otp,
                'password' => 'Newpassword123',
            ])
            ->assertRedirect(route('password.otp.form', ['email' => $user->email]))
            ->assertSessionHasErrors('password_confirmation');

        $this->assertTrue(Hash::check(
            'old-password-123',
            $user->fresh()->password
        ));
    }

    public function test_user_must_reauthenticate_before_changing_email_and_confirm_new_address(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
            'email_verified_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->post(route('profile.email.request'), [
                'email' => 'new@example.com',
                'current_password' => 'incorrect-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame('old@example.com', $user->fresh()->email);
        Notification::assertNothingSent();

        $this->actingAs($user)
            ->post(route('profile.email.request'), [
                'email' => 'new@example.com',
                'current_password' => 'current-password-123',
            ])
            ->assertRedirect(route('profile'));

        $this->assertSame('old@example.com', $user->fresh()->email);
        $this->assertSame('new@example.com', $user->fresh()->pending_email);

        $profile = $this->actingAs($user)->get(route('profile'));
        $profile
            ->assertOk()
            ->assertSee('Email hiện tại')
            ->assertSee('Email mới chờ xác thực')
            ->assertSee('Email hiện tại vẫn được sử dụng cho đến khi xác thực email mới thành công.')
            ->assertSee('new@example.com')
            ->assertSee('id="otp-expiry-countdown"', false);

        $otp = null;

        Notification::assertSentOnDemand(
            OtpCodeNotification::class,
            function (OtpCodeNotification $notification) use (&$otp): bool {
                if ($notification->purpose !== 'email_change') {
                    return false;
                }

                $otp = $notification->code;

                return true;
            }
        );

        $this->actingAs($user)
            ->post(route('profile.email.verify.submit'), [
                'otp' => $otp,
            ])
            ->assertRedirect(route('profile'));

        $this->assertSame('new@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->pending_email);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_email_change_rejects_current_or_already_used_email_and_requires_password(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
        ]);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user)
            ->from(route('profile'))
            ->post(route('profile.email.request'), [
                'email' => 'old@example.com',
                'current_password' => 'current-password-123',
            ])
            ->assertSessionHasErrors('email');

        $this->post(route('profile.email.request'), [
            'email' => 'taken@example.com',
            'current_password' => 'current-password-123',
        ])->assertSessionHasErrors('email');

        $this->post(route('profile.email.request'), [
            'email' => 'new@example.com',
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertSame('old@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->pending_email);
        Notification::assertNothingSent();
    }

    public function test_profile_updates_personal_information_without_changing_email_and_keeps_email_form_collapsed(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Thông tin cá nhân')
            ->assertSee('Đổi email')
            ->assertSee('id="email-change-form"', false)
            ->assertSee('id="show-email-change-form"', false)
            ->assertSee('class="mt-4 space-y-4 hidden"', false);

        $this->post(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'ignored@example.com',
        ])->assertRedirect(route('profile'));

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_resending_email_otp_invalidates_the_previous_code(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
        ]);

        $this->actingAs($user)->post(route('profile.email.request'), [
            'email' => 'new@example.com',
            'current_password' => 'current-password-123',
        ])->assertRedirect(route('profile'));

        $this->post(route('profile.email.verify.resend'))
            ->assertRedirect(route('profile'));

        $codes = [];
        Notification::assertSentOnDemand(
            OtpCodeNotification::class,
            function (OtpCodeNotification $notification) use (&$codes): bool {
                if ($notification->purpose !== 'email_change') {
                    return false;
                }

                $codes[] = $notification->code;

                return true;
            }
        );
        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0], $codes[1]);

        $this->from(route('profile'))
            ->post(route('profile.email.verify.submit'), ['otp' => $codes[0]])
            ->assertSessionHasErrors('otp');
        $this->post(route('profile.email.verify.submit'), ['otp' => $codes[1]])
            ->assertRedirect(route('profile'));

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_canceling_email_change_removes_pending_email_and_otp(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
        ]);

        $this->actingAs($user)->post(route('profile.email.request'), [
            'email' => 'new@example.com',
            'current_password' => 'current-password-123',
        ]);

        $this->delete(route('profile.email.cancel'))
            ->assertRedirect(route('profile'));

        $this->assertNull($user->fresh()->pending_email);
        $this->assertNull(Cache::get('profile-email-otp:'.$user->getKey()));
        $this->get(route('profile'))->assertDontSee('Email mới chờ xác thực');
    }

    public function test_expired_email_change_otp_is_reported_and_pending_email_remains_visible(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
        ]);

        $this->actingAs($user)->post(route('profile.email.request'), [
            'email' => 'new@example.com',
            'current_password' => 'current-password-123',
        ]);

        $this->travel(301)->seconds();

        $this->from(route('profile'))
            ->post(route('profile.email.verify.submit'), ['otp' => '123456'])
            ->assertSessionHasErrors('otp');

        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('Mã OTP đã hết hạn hoặc không còn hiệu lực.')
            ->assertSee('new@example.com')
            ->assertSee('Email hiện tại vẫn được sử dụng cho đến khi xác thực email mới thành công.');
        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_profile_otp_countdown_uses_remaining_seconds_after_reload(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
        ]);

        $this->actingAs($user)->post(route('profile.email.request'), [
            'email' => 'new@example.com',
            'current_password' => 'current-password-123',
        ])->assertRedirect(route('profile'));

        $firstPage = $this->get(route('profile'))
            ->assertOk()
            ->assertSee('padStart(2,', false)
            ->getContent();
        preg_match('/id="otp-expiry-countdown"\s*data-seconds="(\d+)"/', $firstPage, $firstMatch);
        $this->assertNotEmpty($firstMatch[1] ?? null);

        $this->travel(75)->seconds();
        $reloadedPage = $this->get(route('profile'))
            ->assertOk()
            ->getContent();
        preg_match('/id="otp-expiry-countdown"\s*data-seconds="(\d+)"/', $reloadedPage, $reloadedMatch);

        $this->assertLessThan((int) $firstMatch[1], (int) $reloadedMatch[1]);
        $this->assertGreaterThan(0, (int) $reloadedMatch[1]);
    }

    public function test_email_otp_attempt_limit_is_reported_and_resending_unlocks_verification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
        ]);

        $this->actingAs($user)->post(route('profile.email.request'), [
            'email' => 'new@example.com',
            'current_password' => 'current-password-123',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->from(route('profile'))
                ->post(route('profile.email.verify.submit'), ['otp' => '123456']);
        }

        $response->assertSessionHasErrors([
            'otp' => 'Bạn đã nhập sai quá 5 lần. Mã OTP đã bị khóa; vui lòng gửi lại mã.',
        ]);

        $this->post(route('profile.email.verify.resend'))
            ->assertRedirect(route('profile'));
        $codes = [];
        Notification::assertSentOnDemand(
            OtpCodeNotification::class,
            function (OtpCodeNotification $notification) use (&$codes): bool {
                if ($notification->purpose !== 'email_change') {
                    return false;
                }

                $codes[] = $notification->code;

                return true;
            }
        );
        $this->assertCount(2, $codes);

        $this->post(route('profile.email.verify.submit'), ['otp' => $codes[1]])
            ->assertRedirect(route('profile'));
        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_email_otp_send_limit_is_shown_and_enforced_on_profile(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old@example.com',
            'password' => 'current-password-123',
        ]);

        $this->actingAs($user)->post(route('profile.email.request'), [
            'email' => 'new@example.com',
            'current_password' => 'current-password-123',
        ]);
        $this->post(route('profile.email.verify.resend'));
        $this->post(route('profile.email.verify.resend'));

        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('id="resend-countdown"', false)
            ->assertSee('3 mã trong 5 phút')
            ->assertSee('id="otp-expiry-countdown"', false);

        $this->from(route('profile'))
            ->post(route('profile.email.verify.resend'))
            ->assertSessionHasErrors('email');
    }

    public function test_profile_address_add_and_delete_routes_still_work(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('address.store'), [
                'recipient_name' => 'Nguyễn Văn A',
                'phone' => '0900000000',
                'address_detail' => '1 Đường X, Quận 1',
                'is_default' => '1',
            ])
            ->assertRedirect(route('profile'));

        $address = $user->addresses()->firstOrFail();
        $this->assertDatabaseHas('addresses', [
            'id' => $address->getKey(),
            'user_id' => $user->getKey(),
            'is_default' => true,
        ]);

        $this->delete(route('address.destroy', $address))
            ->assertRedirect(route('profile'));
        $this->assertDatabaseMissing('addresses', ['id' => $address->getKey()]);
    }

    public function test_expired_password_reset_otp_cannot_change_password(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => 'old-password-123',
        ]);

        $this->post(route('password.otp.send'), [
            'email' => $user->email,
        ]);

        $otp = null;
        Mail::assertSent(
            PasswordResetOtpMail::class,
            function (PasswordResetOtpMail $mail) use (&$otp): bool {
                $otp = $mail->code;

                return true;
            }
        );

        $this->travel(301)->seconds();

        $this->from(route('password.otp.form', ['email' => $user->email]))
            ->post(route('password.otp.reset'), [
                'email' => $user->email,
                'otp' => $otp,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('password.otp.form', ['email' => $user->email]))
            ->assertSessionHasErrors('otp');

        $expiredScreen = $this->get(route('password.otp.form', [
            'email' => $user->email,
        ]));
        $expiredScreen
            ->assertOk()
            ->assertSeeText('Mã OTP đã hết hạn')
            ->assertSeeText('Gửi lại mã OTP');
        $this->assertMatchesRegularExpression(
            '/<button\s+id="update-password"\s+type="submit"\s+disabled/s',
            $expiredScreen->getContent()
        );

        $this->assertTrue(Hash::check(
            'old-password-123',
            $user->fresh()->password
        ));
    }
}
