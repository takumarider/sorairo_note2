<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const DIRECT_RESERVATION_GUEST_EMAIL_PREFIX = 'direct-guest+';

    public const DIRECT_RESERVATION_GUEST_EMAIL_DOMAIN = 'guest.local.invalid';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public static function createDirectReservationGuest(string $name): self
    {
        $displayName = trim($name);

        return self::create([
            'name' => $displayName !== '' ? $displayName : '仮名予約',
            'email' => self::DIRECT_RESERVATION_GUEST_EMAIL_PREFIX.Str::ulid().'@'.self::DIRECT_RESERVATION_GUEST_EMAIL_DOMAIN,
            'password' => Str::random(40),
            'is_admin' => false,
        ]);
    }

    public static function isDirectReservationGuestEmail(?string $email): bool
    {
        if (! $email) {
            return false;
        }

        return str_starts_with($email, self::DIRECT_RESERVATION_GUEST_EMAIL_PREFIX)
            && str_ends_with($email, '@'.self::DIRECT_RESERVATION_GUEST_EMAIL_DOMAIN);
    }

    public static function directReservationGuestEmailLikePattern(): string
    {
        return self::DIRECT_RESERVATION_GUEST_EMAIL_PREFIX.'%@'.self::DIRECT_RESERVATION_GUEST_EMAIL_DOMAIN;
    }

    public function isDirectReservationGuest(): bool
    {
        return self::isDirectReservationGuestEmail($this->email);
    }
}
