namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaReserva extends Model
{
    protected $table = 'auditoria_reservas';

    protected $fillable = [
        'accion',
        'tabla',
        'registro_id',
        'usuario_id',
        'usuario_nombre',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
        'user_agent',
        'fecha_accion'
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha_accion' => 'datetime',
    ];
}
