use Illuminate\Support\Facades\Password;

public function boot(): void
{
    parent::boot();

    // THIS IS THE MISSING PIECE
    Password::resetUrlUsing(function ($notifiable, $token) {
        return url('/resetpassword/' . $to  ken . '?email=' . $notifiable->getEmailForPasswordReset());
    });
}
