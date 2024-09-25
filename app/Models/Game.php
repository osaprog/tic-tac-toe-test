<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    // Set the primary key
    protected $primaryKey = 'id';

    // Indicate that the ID is not auto-incrementing
    public $incrementing = false;
    
    // Fillable properties for mass assignment
    protected $fillable = [
        'board',
        'player_x',
        'player_o',
        'turn',
    ];

    // Cast board to JSON
    protected $casts = [
        'board' => 'array',
    ];
}
