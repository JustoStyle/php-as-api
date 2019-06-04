<?php
class NetworkInterface extends Illuminate\Database\Eloquent\Model
{
    protected $guarded = [];
    protected $table = 'interfaces';

    public function host()
    {
        return $this->belongsTo('Host');
    }
}