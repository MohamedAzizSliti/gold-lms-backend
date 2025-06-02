<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $casts = [
        'values' => 'json',
    ];

    /**
     * The values that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'values',
    ];

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('settings');
    }

    public function getValuesAttribute($value)
    {
        $values = json_decode($value, true);

        // Safely get values with null checks
        $lightLogoImage = isset($values['general']['light_logo_image_id'])
            ? Attachment::find($values['general']['light_logo_image_id'])
            : null;
        $darkLogoImage = isset($values['general']['dark_logo_image_id'])
            ? Attachment::find($values['general']['dark_logo_image_id'])
            : null;
        $faviconImage = isset($values['general']['favicon_image_id'])
            ? Attachment::find($values['general']['favicon_image_id'])
            : null;
        $tinyImage = isset($values['general']['tiny_logo_image_id'])
            ? Attachment::find($values['general']['tiny_logo_image_id'])
            : null;
        $defaultCurrency = isset($values['general']['default_currency_id'])
            ? Currency::find($values['general']['default_currency_id'])
            : null;
        $maintenanceImage = isset($values['maintenance']['maintenance_image_id'])
            ? Attachment::find($values['maintenance']['maintenance_image_id'])
            : null;

        // Set the values safely
        if (!isset($values['general'])) {
            $values['general'] = [];
        }
        if (!isset($values['maintenance'])) {
            $values['maintenance'] = [];
        }

        $values['general']['light_logo_image'] = $lightLogoImage;
        $values['general']['dark_logo_image'] = $darkLogoImage;
        $values['general']['favicon_image'] = $faviconImage;
        $values['general']['tiny_logo_image'] = $tinyImage;
        $values['general']['default_currency'] = $defaultCurrency;
        $values['maintenance']['maintenance_image'] = $maintenanceImage;

        return $values;
    }

    public function setValuesAttribute($value)
    {

        $this->attributes['values'] = json_encode($value);
    }
}
