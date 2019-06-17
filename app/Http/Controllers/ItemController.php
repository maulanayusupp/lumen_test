<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Item;
use App\Checklist;
use Auth;
use Carbon\Carbon;

class ItemController extends Controller
{
    /**
     * List all
     *
     * @return response
     */
    public function list(Request $request) {
        $filter = $request->has('filter') ? $request->input('filter') : 'like';
        $sort = $request->has('sort') ? $request->input('sort') : 'asc';
        $keyword = $request->input('keyword');
        $pageLimit = $request->input('page_limit');

        $items = Item::orderBy('created_at', $sort)
            ->where(function ($src) use ($filter, $keyword) {
                $src->where('description', $filter, '%'.$keyword.'%')
                    ->orWhere('due', $filter, '%'.$keyword.'%');
            })
            ->paginate($pageLimit);

        $response['data'] = $items;
        return response()->json($response, 200);
    }

    /**
     * Store data / insert data to the database
     *
     * @return response
     */
    public function store(Request $request) {
        $this->validate($request, [
            'template_id' => 'required',
            'due' => 'required',
        ]);

		$template_id = $request->has('template_id') ? trim($request->input('template_id')) : false;
		$checklist_id = $request->has('checklist_id') ? trim($request->input('checklist_id')) : false;
		$due = $request->has('due') ? trim($request->input('due')) : false;
		$description = $request->has('description') ? trim($request->input('description')) : false;
        $completed_at = $request->has('completed_at') ? trim($request->input('completed_at')) : false;
        $urgency = $request->has('urgency') ? trim($request->input('urgency')) : false;
        $assignee_id = $request->has('assignee_id') ? trim($request->input('assignee_id')) : false;
        $task_id = $request->has('task_id') ? trim($request->input('task_id')) : false;

		$is_completed = false;
        if ($request->has('is_completed')) {
            $is_completed = true;
            $is_completed_value = (bool) $request->input('is_completed');
        }

        $item = new Item();
        if ($template_id) $item->template_id = $template_id;
        if ($checklist_id) $item->checklist_id = $checklist_id;
        if ($due) $item->due = $due;
        if ($description) $item->description = $description;
        if ($completed_at) $item->completed_at = $completed_at;
        if ($urgency) $item->urgency = $urgency;
        if ($assignee_id) $item->assignee_id = $assignee_id;
        if ($task_id) $item->task_id = $task_id;
        if ($is_completed) $item->is_completed = $is_completed_value;
        $item->save();

        $item = Item::where('id', $item->id)->first();
        $response['message'] = 'New data created';
        $response['data'] = $item;
        return response()->json($response, 200);
    }

    /**
     * Show details
     *
     * @return response
     */
    public function show($id) {
        $item = Item::find($id);
		if (!isset($item)) {
            $response['message'] = 'Cannot find the data';
			return response()->json($response, 422);
		}

        $response['data'] = $item;
        return response()->json($response, 200);
    }

    /**
     * Update data
     *
     * @return response
     */
    public function update(Request $request, $id) {
        $this->validate($request, [
            'template_id' => 'required',
            'due' => 'required',
        ]);

        $user = Auth::user();
        $updated_by = $user->name;

		$template_id = $request->has('template_id') ? trim($request->input('template_id')) : false;
		$checklist_id = $request->has('checklist_id') ? trim($request->input('checklist_id')) : false;
		$due = $request->has('due') ? trim($request->input('due')) : false;
		$description = $request->has('description') ? trim($request->input('description')) : false;
        $completed_at = $request->has('completed_at') ? trim($request->input('completed_at')) : false;
        $urgency = $request->has('urgency') ? trim($request->input('urgency')) : false;
        $assignee_id = $request->has('assignee_id') ? trim($request->input('assignee_id')) : false;
        $task_id = $request->has('task_id') ? trim($request->input('task_id')) : false;

		$is_completed = false;
        if ($request->has('is_completed')) {
            $is_completed = true;
            $is_completed_value = (bool) $request->input('is_completed');
        }

        $item = Item::where('id', $id)->first();
        if($item){
            if ($template_id) $item->template_id = $template_id;
            if ($checklist_id) $item->checklist_id = $checklist_id;
            if ($due) $item->due = $due;
            if ($description) $item->description = $description;
            if ($completed_at) $item->completed_at = $completed_at;
            if ($urgency) $item->urgency = $urgency;
            if ($assignee_id) $item->assignee_id = $assignee_id;
            if ($task_id) $item->task_id = $task_id;
            if ($updated_by) $item->updated_by = $updated_by;
            if ($is_completed) $item->is_completed = $is_completed_value;
            $item->save();

            $response['message'] = 'Data updated';
            $response['data'] = $item;
            return response()->json($response, 200);

        } else {
            $response['message'] = 'Cant find the data';
            return response()->json($response, 400);
        }
    }

    /**
     * Delete
     *
     * @param  string $email
     * @return bool|Response
     */
    public function delete($id) {
        $item = Item::find($id);
		if (isset($item)) {
			$item->delete();
			$response['message'] = 'Data deleted';
			$response['data'] = $item;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'Cant find the data';
            return response()->json($response, 400);
		}
		return $item;
    }

    /**
     * Bulk Update
     *
     * @param Request $request
     * @return array|Response
     */
    public function bulkUpdate(Request $request)
    {
        $this->validate($request, [
            'item_ids' => 'required',
        ]);
        $user = Auth::user();
        $updated_by = $user->name;

		$item_ids = gettype($request->input('item_ids')) === 'string' ? json_decode($request->input('item_ids')) : $request->input('item_ids');
        $template_id = $request->has('template_id') ? trim($request->input('template_id')) : false;
		$checklist_id = $request->has('checklist_id') ? trim($request->input('checklist_id')) : false;
		$due = $request->has('due') ? trim($request->input('due')) : false;
		$description = $request->has('description') ? trim($request->input('description')) : false;
        $completed_at = $request->has('completed_at') ? trim($request->input('completed_at')) : false;
        $urgency = $request->has('urgency') ? trim($request->input('urgency')) : false;
        $assignee_id = $request->has('assignee_id') ? trim($request->input('assignee_id')) : false;
        $task_id = $request->has('task_id') ? trim($request->input('task_id')) : false;

		$is_completed = false;
        if ($request->has('is_completed')) {
            $is_completed = true;
            $is_completed_value = (bool) $request->input('is_completed');
        }

        if (count($item_ids) > 0) {
			$items = Item::whereIn('id', $item_ids)->get();
			foreach($items as $item) {
				if ($template_id) $item->template_id = $template_id;
                if ($checklist_id) $item->checklist_id = $checklist_id;
                if ($due) $item->due = $due;
                if ($description) $item->description = $description;
                if ($completed_at) $item->completed_at = $completed_at;
                if ($urgency) $item->urgency = $urgency;
                if ($assignee_id) $item->assignee_id = $assignee_id;
                if ($task_id) $item->task_id = $task_id;
                if ($updated_by) $item->updated_by = $updated_by;
                if ($is_completed) $item->is_completed = $is_completed_value;
				$item->save();
            }
            $items = Item::whereIn('id', $item_ids)->get();
			$response['message'] = count($items) . ' Data updated';
			$response['data'] = $items;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data updated';
			return response()->json($response, 200);
		}
	}

	/**
     * Bulk Delete
     *
     * @param Request $request
     * @return array|Response
     */
    public function bulkDelete(Request $request)
    {
        $this->validate($request, [
            'item_ids' => 'required',
        ]);

        $item_ids = gettype($request->input('item_ids')) === 'string' ? json_decode($request->input('item_ids')) : $request->input('item_ids');
		if (count($item_ids) > 0) {
            $items = Item::whereIn('id', $item_ids)->delete();
            $response['message'] = $items . ' Data deleted';
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data deleted';
			return response()->json($response, 200);
		}
    }

    /**
     * Bulk Complete Item(s)
     *
     * @param Request $request
     * @return array|Response
     */
    public function complete(Request $request)
    {
        $this->validate($request, [
            'item_ids' => 'required',
        ]);

        // Get user
        $user = Auth::user();
        $updated_by = $user->name;

        // Get current time now
        $now = Carbon::now()->toDateTimeString();

		$item_ids = gettype($request->input('item_ids')) === 'string' ? json_decode($request->input('item_ids')) : $request->input('item_ids');
        $completed_at = $now;

        if (count($item_ids) > 0) {
			$items = Item::whereIn('id', $item_ids)->get();
			foreach($items as $item) {
                if ($completed_at) $item->completed_at = $completed_at;
                if ($updated_by) $item->updated_by = $updated_by;
                $item->is_completed = 1;
				$item->save();
            }
            $items = Item::whereIn('id', $item_ids)->get();
			$response['message'] = count($items) . ' Data changed to completed';
			$response['data'] = $items;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data updated';
			return response()->json($response, 200);
		}
    }

    /**
     * Bulk Incomplete Item(s)
     *
     * @param Request $request
     * @return array|Response
     */
    public function incomplete(Request $request)
    {
        $this->validate($request, [
            'item_ids' => 'required',
        ]);

        // Get user
        $user = Auth::user();
        $updated_by = $user->name;

        // Get current time now
        $now = Carbon::now()->toDateTimeString();

		$item_ids = gettype($request->input('item_ids')) === 'string' ? json_decode($request->input('item_ids')) : $request->input('item_ids');
        $completed_at = $now;

        if (count($item_ids) > 0) {
			$items = Item::whereIn('id', $item_ids)->get();
			foreach($items as $item) {
                if ($completed_at) $item->completed_at = $completed_at;
                if ($updated_by) $item->updated_by = $updated_by;
                $item->is_completed = 0;
				$item->save();
            }
            $items = Item::whereIn('id', $item_ids)->get();
			$response['message'] = count($items) . ' Data changed to incomplete';
			$response['data'] = $items;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data updated';
			return response()->json($response, 200);
		}
    }

    /**
     * Summaries
     *
     * @return response
     */
    public function summaries(Request $request) {

        // Get current time now
        $now = Carbon::now()->toDateTimeString();

        $total = Item::count();
        return $total;

        $response['today'] = $today;
        $response['past_due'] = $past_due;
        $response['this_week'] = $this_week;
        $response['past_week'] = $past_week;
        $response['this_month'] = $this_month;
        $response['past_month'] = $past_month;
        $response['total'] = $total;
        return response()->json($response, 200);
    }
}
