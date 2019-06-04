<?php
class Node extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function status()
    {
        return $this->belongsTo('Status');
    }

    public function suburb()
    {
        return $this->belongsTo('Suburb');
    }

    public function subnet()
    {
        return $this->belongsToMany('Subnet');
    }

    public function link()
    {
        return $this->belongsToMany('Link')->withPivot('interface_id');
    }
}