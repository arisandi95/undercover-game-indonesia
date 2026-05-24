<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['civilian_word', 'undercover_word', 'difficulty'])]
class WordPair extends Model
{
    use HasFactory;
}
