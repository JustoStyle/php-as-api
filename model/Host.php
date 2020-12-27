<?php
class Host extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];

    public function node()
    {
        return $this->belongsTo('Node');
    }

    public function alias()
    {
        return $this->hasMany('HostAlias');
    }

    public function interface()
    {
        return $this->hasMany('NetworkInterface');
    }

    public function subnet()
    {
        return $this->belongsTo('Subnet');
    }
}