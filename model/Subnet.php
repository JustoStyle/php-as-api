<?php
class Subnet extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];

    public function host()
    {
        return $this->hasMany('Host');
    }

    public function node()
    {
        return $this->belongsToMany('Node');
    }
}