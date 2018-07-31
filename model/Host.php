<?php
class Host extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];

    public function node()
    {
        return $this->belongsTo('Node');
    }

    public function subnet()
    {
        return $this->belongsTo('Subnet');
    }
}