<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string name
 * @property string agent
 * @property string address
 * @property string phone
 * @property string email
 * @property bool is_enable
 * @property string logo
 */
class SchoolUser extends Pivot
{
    protected $table = 'schools_user';
}
