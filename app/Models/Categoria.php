<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLoggingService;

class Categoria extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected static $logName = 'categorias';

    protected $table = 'categorias';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    
    protected static function boot()
    {
        parent::boot();

        static::created(fn($categorias) => ActivityLoggingService::logActivity($categorias, 'created', static::$logName));

        static::updated(fn($categorias) => ActivityLoggingService::logActivity($categorias, 'updated', static::$logName));

        static::deleted(fn($categorias) => ActivityLoggingService::logActivity($categorias, 'deleted', static::$logName));
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
