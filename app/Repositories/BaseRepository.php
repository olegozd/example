<?php

namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     *
     * @throws \Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable();

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @return Model
     * @throws \Exception
     *
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());
        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*'])
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @return Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null)
    {
        $query = $this->model->newQuery();

        if (count($search)) {
            foreach ($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable())) {
                    $query->where($key, $value);
                }
            }
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Build a query by request for retrieving records.
     *
     * @param Request|null $request
     * @return Builder
     */
    public function queryByRequest(?Request $request = null): Builder
    {
        $query = $this->model->newQuery();

        $this->limitToQueryByRequest($query, $request);
        $this->orderToQueryByRequest($query, $request);

        return $query;
    }

    /**
     * Upgrade a query by request for pagination and sort records.
     *
     * @param Builder $query
     * @param Request|null $request
     * @return Builder
     */
    public function orderToQueryByRequest(Builder $query, ?Request $request = null): Builder
    {
        if (isset($request)) {
            $orderBy   = $request->input('order');
            $direction = $request->input('dir');

            if (!is_null($orderBy)) {
                if (!is_null($direction)) {
                    $query->orderBy($orderBy, $direction);
                } else {
                    $query->orderBy($orderBy);
                }
            }
        }

        return $query;
    }

    /**
     * Upgrade a query by request for pagination and sort records.
     *
     * @param Builder $query
     * @param Request|null $request
     * @return Builder
     */
    public function limitToQueryByRequest(Builder $query, ?Request $request = null): Builder
    {
        if (isset($request)) {
            $page  = $request->input('page');
            $limit = $request->input('limit');

            if (!is_null($limit)) {
                $query->limit($limit);

                if (!is_null($page)) {
                    $skip = ($page - 1) * $limit;
                    $query->skip($skip);
                }
            }
        }

        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'])
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input)
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     *
     * @return Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find($id, $columns = ['*'])
    {
        $query = $this->model->newQuery();

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update($input, $id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * Update model record for given id
     *
     * @param array $array
     * @param array $values
     *
     * @return Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */

    public function updateOrCreate($array, $values)
    {
        $query = $this->model->newQuery();

        $model = $query->updateOrCreate($array, $values);

        return $model;
    }

    /**
     * @param int $id
     *
     * @return bool|mixed|null
     * @throws \Exception
     *
     */
    public function delete($id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }
}
