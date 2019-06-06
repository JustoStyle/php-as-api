<?php
class HostAlias extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];

    public function node()
    {
        return $this->belongsTo('Host');
    }
}