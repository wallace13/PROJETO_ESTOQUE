<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogger;

class Fornecedor extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected static $logName = 'fornecedores';

    protected $table = 'fornecedores';
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
    public function getFormattedNameAttribute()
    {
        return $this->cnpj . ' - ' . $this->razao_social;
    }

    protected static function boot()
    {
        parent::boot();
        // Exemplo de registro de atividade no método 'created'
        static::created(function ($fornecedores) {
            $causador = backpack_auth()->user();
            $eventName = 'created';
            $descricao = "Novo fornecedor criado por {$causador->name}";
            ActivityLogger::logActivity($fornecedores, $eventName, $causador, $descricao,static::$logName,$fornecedores->attributes);
        });

        // Exemplo de registro de atividade no método 'updated'
        static::updated(function ($fornecedores) {
            $causador = backpack_auth()->user();
            $eventName = 'updated';
            $descricao = "Fornecedor atualizado por {$causador->name}";
            ActivityLogger::logActivity($fornecedores, $eventName, $causador, $descricao,static::$logName,$fornecedores->attributes);
        });

        // Exemplo de registro de atividade no método 'deleted'
        static::deleted(function ($fornecedores) {
            $causador = backpack_auth()->user();
            $eventName = 'deleted';
            $descricao = "Fornecedor excluído por {$causador->name}";
            ActivityLogger::logActivity($fornecedores, $eventName, $causador, $descricao,static::$logName,$fornecedores->attributes);
        });

    }

    public function getCnpjAttribute($value)
    {
        // Formatar CNPJ: 11222333444455 para 11.222.333/4444-55
        return vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($value));
    }
    public function getCepAttribute($value)
    {
        // Formatar CEP: 12345678 para 12345-678
        return preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $value);
    }
    public function getTelefoneAttribute($value)
    {
        // Formatar telefone: (12) 3456-7890 para (12) 3456-7890
        return preg_replace('/^(\d{2})(\d{4,5})(\d{4})$/', '($1) $2-$3', $value);
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
