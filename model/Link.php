<?php
class Link extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];

    public function node()
    {
        return $this->belongsToMany('Node')->withPivot('interface_id');
    }
}