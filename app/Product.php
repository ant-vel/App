<?php

namespace app;

/*
 * Antvel - Products Model
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\Category;
use App\Eloquent\Model;

class Product extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';
    protected $appends = ['num_of_reviews'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'user_id',
        'name',
        'description',
        'price',
        'stock',
        'features',
        'barcode',
        'condition',
        'rate_val',
        'rate_count',
        'low_stock',
        'status',
        'parent_id',
    ];

    protected $hidden = ['details', 'created_at'];

    public function details()
    {
        return $this->hasMany('App\OrderDetail');
    }

    public function categories()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function group()
    {
        return $this->hasMany('App\Product', 'products_group', 'products_group');
    }

    public function getLastCommentsAttribute()
    {
        return $this->details->take(5);
    }

    public function getFirstImageAttribute()
    {
        $features_array = $this->features;

        return $features_array['images'][0];
    }

    public function getFeaturesAttribute()
    {
        return json_decode($this->attributes['features'], true);
    }

    public function scopeActives($query)
    {
        return $query->where('status', 1)
                     ->where('stock', '>', 0);
    }

    public function scopeInactives($query)
    {
        return $query->where('status', 0);
    }

    public function scopeSearch($query, $seed)
    {
        return $query->where('name', 'like', '%'.$seed.'%')
            ->orWhere('description', 'like', '%'.$seed.'%')
            ->orWhere('features', 'like', '%'.$seed.'%')
            ->orWhere('brand', 'like', '%'.$seed.'%')
            ->orWhere('tags', 'like', '%'.$seed.'%');
    }

    public function scopeRefine($query, $filters)
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'category':
                    $children = \Cache::remember('progeny_of_'.$value, 15, function () use ($value) {
                        Category::progeny($value, $children, ['id']);

                        return $children;
                    });
                    $children[] = ['id' => $value * 1];
                    $query->whereIn('category_id', $children);
                break;

                case 'conditions':
                    $query->where('condition', 'LIKE', $value);
                break;

                case 'brands':
                   $query->where('brand', 'LIKE', $value);
                break;

                case 'min':
                case 'max':
                    $min = array_key_exists('min', $filters) ? (trim($filters['min']) != '' ? $filters['min'] : '') : '';
                    $max = array_key_exists('max', $filters) ? (trim($filters['max']) != '' ? $filters['max'] : '') : '';

                    if ($min != '' && $max != '') {
                        $query->whereBetween('price', [$min, $max]);
                    } elseif ($min == '' && $max != '') {
                        $query->where('price', '<=', $max);
                    } elseif ($min != '' && $max == '') {
                        $query->where('price', '>=', $min);
                    }
                break;

                default:
                    if ($key != 'category_name' && $key != 'search' && $key != 'page') {

                        //changing url encoded character by the real ones
                        $value = urldecode($value);

                        //applying filter to json field
                        $query->whereRaw("features LIKE '%\"".$key.'":%"%'.str_replace('/', '%', $value)."%\"%'");
                    }
                break;
            }
        }
    }

    public function scopeName($query, $input)
    {
        if (trim($input) != '') {
            $query->where('name', 'LIKE', "%$input%");
        }
    }

    public function scopeType($query, $input)
    {
        if (count($input) > 0) {
            $query->whereIn('category_id', $input);
        }
    }

    public function getStatusLettersAttribute()
    {
        if ($this->status == 0) {
            return trans('globals.inactive');
        }

        return trans('globals.active');
    }

    public function scopeFree($query)
    {
        if (!config('app.offering_free_products')) {
            $query->where('type', '<>', 'freeproduct');
        }
    }

    /**
     * Products tags filter.
     *
     * @param [object] $query, which is the laravel builder
     * @param [string] $attr,  which is used to evaluate the where In (categories requiered)
     * @param [array]  $data,  which is the info to be evaluated
     */
    public function scopeLike($query, $attr = [], $search = [])
    {
        //if the search contains a string of words, we split them in an array
        if (!is_array($search)) {
            $search = explode(' ', preg_replace('/\s+/', ' ', trim($search)));
        }

        $needle = '(';

        if (!is_array($attr)) {
            $attr = [$attr];
        }
        foreach ($attr as $key) {
            foreach ($search as $word) {
                if (trim($word) != '') {
                    $needle .= $key." like '%".$word."%' or ";
                }
            }
        }

        $needle = rtrim($needle, ' or ').')';

        $query->whereRaw($needle);

        return $query;
    }

    /**
     * categories filter.
     *
     * @param [object] $query, which is the laravel builder
     * @param [string] $attr,  which is used to evaluate the where In (categories requiered)
     * @param [array]  $data,  which is the info to be evaluated
     */
    public function scopeInCategories($query, $attr, $data = [])
    {
        if (count($data) > 0 && empty($data['category'])) {
            $query->whereIn($attr, $data);
        } elseif (isset($data['category'])) {
            $query->where($attr, '=', $data['category']);
        }

        return $query;
    }

    public function getNumOfReviewsAttribute()
    {
        return $this->rate_count.' '.\Lang::choice('store.review', $this->rate_count);
    }
}
