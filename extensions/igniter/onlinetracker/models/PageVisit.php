<?php namespace Igniter\OnlineTracker\Models;

use Carbon\Carbon;
use Country;
use Jenssegers\Agent\Agent;
use Model;

/**
 * PageVisit Model Class
 *
 * @package Admin
 */
class PageVisit extends Model
{
    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_onlinetracker_tracker';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'activity_id';

    protected $guarded = [];

    public $timestamps = TRUE;

    public $relation = [
        'belongsTo' => [
            'geoip' => ['Igniter\OnlineTracker\Models\GeoIp'],
            'customer' => ['Admin\Models\Customers_model', 'foreignKey' => 'customer_id'],
        ],
    ];

    public $casts = [
        'headers' => 'array',
    ];

    /**
     * @var Agent
     */
    protected $agentClass;

    protected function afterFetch()
    {
        $this->applyAgentClass();
    }

    //
    // Accessors & Mutators
    //

    public function getAccessTypeAttribute($value)
    {
        return ucwords($value);
    }

    public function getDateAddedAttribute($value)
    {
        return time_elapsed($value);
    }

    public function getCountryNameAttribute()
    {
        if (!$this->geoip)
            return null;

        return Country::getCountryNameByCode($this->geoip->country_iso_code_2);
    }

    public function getCountryCityAttribute()
    {
        return $this->country_name.' - '.($this->geoip ? $this->geoip->city : null);
    }

    public function getCustomerNameAttribute()
    {
        if (!$this->customer)
            return lang('igniter.onlinetracker::default.text_guest');

        return $this->customer->full_name;
    }

    protected function getPlatformAttribute()
    {
        if (!$this->agentClass)
            return null;

        $platform = $this->agentClass->platform();

        if ($this->agentClass->isRobot()) {
            return lang('igniter.onlinetracker::default.text_robot')
                .' ['.$platform.']';
        }

        if ($this->agentClass->isTablet()) {
            return lang('igniter.onlinetracker::default.text_tablet')
                .' ['.$platform.'] ['.$this->agentClass->device().']';
        }

        if ($this->agentClass->isMobile()) {
            return lang('igniter.onlinetracker::default.text_tablet')
                .' ['.$platform.'] ['.$this->agentClass->device().']';
        }

        if ($this->agentClass->isDesktop()) {
            return lang('igniter.onlinetracker::default.text_computer')
                .' ['.$platform.']';
        }
    }

    //
    // Scopes
    //

    public function scopeIsOnline($query, $value)
    {
        if ($value) {
            $onlineTimeOut = Settings::get('online_time_out', 5);
            $query->where('created_at', '>=', Carbon::now()->subMinutes($onlineTimeOut));
        }

        return $query;
    }

    //
    // Helpers
    //

    protected function applyAgentClass()
    {
        if (empty($this->user_agent) OR !count($this->headers))
            return;

        $agent = new Agent;

        $agent->setUserAgent($userAgent = $this->user_agent);
        $agent->setHttpHeaders($headers = $this->headers);

        $this->agentClass = $agent;
    }

    public function getAgent()
    {
        return $this->agentClass;
    }

    /**
     * Find when a customer was last online by ip
     *
     * @param string $ip the IP address of the current user
     *
     * @return array
     */
    public function getLastOnline($ip)
    {
        return $this->selectRaw('*, MAX(created_at) as created_at')->where('ip_address', $ip)->first();
    }

    /**
     * Return the last online dates of all customers
     *
     * @return array
     */
    public function getOnlineDates()
    {
        return $this->pluckDates('created_at');
    }
}