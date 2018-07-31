<?php
class Status extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];

    public function node()
    {
        return $this->hasMany('Node');
    }
}