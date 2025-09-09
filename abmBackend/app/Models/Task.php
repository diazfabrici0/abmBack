<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'table_tasks';

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
    ];

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id');
    }

    public function confirmations()
    {
        return $this->hasMany(Confirmation::class);
    }
}
