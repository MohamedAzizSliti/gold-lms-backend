<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'password',
        'password_traccar',
        'player_id ',
        'is_approved',
        'country_code',
        'created_by_id',
        'system_reserve',
        'profile_image_id',
        'traccar_user_id'
    ];



    protected $guard_name = 'web';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'roles',
        'password',
        'permissions',
        'remember_token',
        'deleted_at',
        'updated_at',
    ];

    protected $with = [
        'profile_image'
    ];

    protected $withCount = [
        'orders'
    ];

    protected $casts = [
        'phone' => 'integer',
        'status' => 'integer',
        'orders_count' => 'integer',
        'created_by_id' => 'integer',
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'role',
    ];

    public function routeNotificationForCall($notification)
    {
        return $this->phone; // Remplacez par le champ contenant le numéro
    }

    public static function booted()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = Helpers::isUserLogin() ? Helpers::getCurrentUserId() : $model->id;
        });
        static::deleted(function($user) {
            $user->orders()->delete();
            $user->store()->delete();
            $user->reviews()->delete();
        });
    }
    public function routeNotificationForSms()
    {
         return $this->country_code.$this->phone; // Assurez-vous que ce champ contient le numéro de téléphone
    }
    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('user')?->id;
    }

    /**
     * @return BelongsTo
     */
    public function profile_image(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'profile_image_id');
    }

    /**
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'consumer_id');
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'consumer_id');
    }

    /**
     * @return HasMany
     */
    public function store(): HasMany
    {
        return $this->hasMany(Store::class, 'vendor_id');
    }

    /**
     * @return HasMany
     */
    public function company(): HasMany
    {
        return $this->hasMany(Company::class, 'client_id');
    }

    /**
     * @return HasMany
     */
    public function address(): HasMany
    {
        return $this->hasMany(Address::class, 'user_id');
    }

    /**
     * @return hasOne
     */
    public function payment_account(): hasOne
    {
        return $this->hasOne(PaymentAccount::class, 'user_id');
    }

    /**
     * @return HasOne
     */
    public function point(): HasOne
    {
        return $this->hasOne(Point::class, 'consumer_id');
    }

    /**
     * @return HasOne
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'consumer_id');
    }

    /**
     * @return HasOne
     */
    public function vendor_wallet(): HasOne
    {
        return $this->hasOne(VendorWallet::class, 'vendor_id');
    }

    /**
     * Get the user who created blog.
     * @return HasMany
     */
    public function created_by(): HasMany
    {
        return $this->hasMany(Blog::class, 'created_by');
    }

    /**
     * @return HasMany
     */
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'consumer_id');
    }

    /**
     * @return HasMany
     */
    public function withdraw_request(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class, 'vendor_id');
    }

    /**
     * @return HasMany
     */
    public function cart(): HasMany
    {
        return $this->hasMany(Cart::class, 'consumer_id');
    }

    /**
     * Get the user's all permissions.
     */
    public function getPermissionAttribute()
    {
        return $this->getAllPermissions();
    }

    /**
     * Get the user's role.
     */
    public function getRoleAttribute()
    {
        return $this->roles->first()?->makeHidden(['created_at', 'updated_at','pivot']);
    }

    /**
     * Get the Store attributes as a admin or vendor.
     */
    public function getStoreAttribute()
    {
        if ($this->hasRole(RoleEnum::VENDOR)) {
            return $this->store()->first();
        }
    }


    /**
     * Get the Store attributes as a admin or vendor.
     */
    public function getCompanyAttribute()
    {
        if ($this->hasRole(RoleEnum::CONSUMER)) {
            return $this->company()->first();
        }
    }


    public function routeNotificationForOneSignal($notification)
    {
        return $this->player_id; // Or however you store the Player ID
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Chat relationships
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
                    ->withPivot('joined_at', 'last_read_at')
                    ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id');
    }
}
