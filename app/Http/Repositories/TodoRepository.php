<?php

namespace App\Http\Repositories;

use App\Models\Todo;
use App\Traits\EloquentPaginate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TodoRepository
{
    use EloquentPaginate;

    /**
     * Todo list
     * @param array $query
     * @param bool $paginate
     * @param int|null $limit
     * @return LengthAwarePaginator|Collection
     */
    public function todoList(array $query = [], bool $paginate = false, int $limit = null)
    {
        $data   =   Todo::select("*");

        if (array_key_exists('search', $query) && !empty($query['search']))
        {
            $data   =   $data->where("title", 'LIKE', '%'.$query['search'].'%');
        }

        if (array_key_exists('uid', $query) && !empty($query['uid']))
        {
            $data   =   $data->where("title", $query['uid']);
        }

        return $this->execute($data, $paginate, $limit);
    }

    /**
     * Find todo item
     * @param string $id
     * @throw ModelNotFoundException
     * @return mixed
     */
    public function findTodo(string $id, string $uid)
    {
        // Could extend to where condition(s) easily. In this case, only findOrFail is needed
        return Todo::where('id', $id)->where('uid', $uid)->firstOrFail();
    }

    /**
     * Save todo item
     * @param array $formData
     * @param string $uid
     * @param string|null $id
     * @return Todo|mixed
     */
    public function saveTodo(array $formData, string $uid, string $id = null)
    {
        /**
         * Only update the data if the formData key is found.
         * For e.g: user may just need to change status, hence, only $formData['status'] field is required instead of
         * multiple functions for different column update
         * This function will reduce query redundancy and easier to maintain database column
         */

        // ID not empty means this is edit action
        if ($id) {
            $data = $this->findTodo($id, $uid);
        } else {
            $data = new Todo();
            $data->uid = $uid;
        }

        if (array_key_exists('title', $formData)) {
            $data->title = $formData['title'];
        }

        if (array_key_exists('status', $formData)) {
            $data->status = $formData['status'];
        }

        if (array_key_exists('completedAt', $formData)) {
            $data->completed_at = $formData['completedAt'];
        }

        $data->save();

        return $data;
    }

    /**
     * Delete todo
     * @param string $id
     * @param string $uid
     * @return Todo
     * @throw ModelNotFoundException
     */
    public function deleteTodo(string $id, string $uid)
    {
        // automatic Throw ModelNotFoundException if not found
        $todo   =   $this->findTodo($id, $uid);
        $todo->delete();

        return $todo;
    }
}
