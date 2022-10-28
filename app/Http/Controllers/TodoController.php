<?php

namespace App\Http\Controllers;

use App\Http\Repositories\TodoRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{

    protected Request $request;
    protected TodoRepository $todoRepo;

    public function __construct(Request $request)
    {
        $this->request = $request; // Global access request within controller
        $this->todoRepo = new TodoRepository(); // Repository to handle database query
    }

    /**
     * Render todo list
     * @return Application|ResponseFactory|Response
     */
    public function todoList()
    {
        $pageLimit = 10;
        $paginate = true;

        if ($this->request->has('paginate') && $this->request->input('paginate') == 'false')
        {
            $paginate = false;
        }

        if ($this->request->has('limit') && (int)$this->request->input('limit') > 0)
        {
            $pageLimit = (int)$this->request->input('limit');
        }

        $formData   =   [
            'search'    =>  $this->request->input('search'),
            'uid'   =>  auth('api')->id()
        ];

        $data   =   $this->todoRepo->todoList($formData, $paginate, $pageLimit);

        return response($data);
    }

    /**
     * Todo Store/ Update
     * @param string|null $id
     * @return Application|ResponseFactory|RedirectResponse|Response
     */
    public function todoStore(string $id = null)
    {
        $validation = [
            'title' => ['required', 'max:255'],
            'catId' => ['required', 'uuid'],
            'certId' => ['nullable', 'uuid'],
            'learningHour' => ['nullable', 'numeric'],
            'description' => ['nullable', 'max:255'],
            'status' => ['nullable', 'in:1'],
            'hidePublic' => ['nullable', 'in:1'],
            'recurrence' => ['nullable', 'in:1,3,6,9,12,24'],
            'retainCompletedUser' => ['nullable', 'in:1'],
        ];

        $validator = Validator::make($this->request->all(), $validation);

        // Return validation error
        if ($validator->fails())
        {
            // If request want json
            if ($this->request->wantsJson())
            {
                return $this->errorResponse($validator->errors(), 422);
            }
            else
            {
                // Return back with errors and old input
                return back()->withErrors($validator)->withInput($this->request->all());
            }
        }

        $formData   =   [
            'title' =>  $this->request->input('title'),
            'status'   =>  0,
            'completedAt'   =>  null,
        ];

        // Save todo
        $this->todoRepo->saveTodo($formData, auth('api')->id(), $id);

        // Default message
        $message    =   "Todo is saved";

        if ($id)
        {
            $message    =   "Todo is updated";
        }

        return response([
            'message'   =>  $message
        ]);
    }

    /**
     * Delete todo item
     * @param string $id
     * @return Application|ResponseFactory|Response
     */
    public function todoDelete(string $id)
    {
        // Auto throw exception error if id not found
        $this->todoRepo->deleteTodo($id, auth('api')->id());

        return response([
            'message'   =>  "Todo is deleted successfully"
        ]);
    }

    /**
     * Update status to complete
     * @param string $id
     * @return Application|ResponseFactory|Response
     */
    public function statusUpdate(string $id)
    {
        $data   =   $this->todoRepo->findTodo($id, auth('api')->id());

        if ($data->status === 0)
        {
            $formData   =   [
                'status'    =>  1,
                'completedAt'   =>  Carbon::now()
            ];

            // Update todo item to complete status
            // Auto throw ModelNotFoundException if not found
            $this->todoRepo->saveTodo($formData, $id);
        }

        return response([
            'message'   =>  'Item has been updated successfully'
        ]);
    }
}
