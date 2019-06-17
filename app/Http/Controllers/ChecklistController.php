<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Libraries\HistoryLibrary;
use App\Checklist;
use App\Item;
use Auth;

class ChecklistController extends Controller
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

        $checklists = Checklist::orderBy('created_at', $sort)
            ->where(function ($src) use ($filter, $keyword) {
                $src->where('description', $filter, '%'.$keyword.'%')
                    ->orWhere('due', $filter, '%'.$keyword.'%');
            })
            ->with('items')
            ->paginate($pageLimit);

        $response['data'] = $checklists;
        return response()->json($response, 200);
    }

    /**
     * List all item by checklist
     *
     * @return response
     */
    public function listByChecklist(Request $request, $checklist_id) {
        $filter = $request->has('filter') ? $request->input('filter') : 'like';
        $sort = $request->has('sort') ? $request->input('sort') : 'asc';
        $keyword = $request->input('keyword');
        $pageLimit = $request->input('page_limit');

        $items = Item::where('checklist_id', $checklist_id)
            ->orderBy('created_at', $sort)
            ->where(function ($src) use ($filter, $keyword) {
                $src->where('description', $filter, '%'.$keyword.'%')
                    ->orWhere('due', $filter, '%'.$keyword.'%');
            })
            ->with('items')
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
            'object_domain' => 'required',
            'object_id' => 'required',
            'description' => 'required',
        ]);

		$template_id = $request->has('template_id') ? trim($request->input('template_id')) : false;
		$object_domain = $request->has('object_domain') ? trim($request->input('object_domain')) : false;
		$object_id = $request->has('object_id') ? trim($request->input('object_id')) : false;
		$description = $request->has('description') ? trim($request->input('description')) : false;
        $completed_at = $request->has('completed_at') ? trim($request->input('completed_at')) : false;
        $due = $request->has('due') ? trim($request->input('due')) : false;
        $urgency = $request->has('urgency') ? trim($request->input('urgency')) : false;

		$is_completed = false;
        if ($request->has('is_completed')) {
            $is_completed = true;
            $is_completed_value = (bool) $request->input('is_completed');
        }

        $checklist = new Checklist();
        if ($template_id) $checklist->template_id = $template_id;
        if ($object_domain) $checklist->object_domain = $object_domain;
        if ($object_id) $checklist->object_id = $object_id;
        if ($description) $checklist->description = $description;
        if ($completed_at) $checklist->completed_at = $completed_at;
        if ($due) $checklist->due = $due;
        if ($urgency) $checklist->urgency = $urgency;
        if ($is_completed) $checklist->is_completed = $is_completed_value;
        $checklist->save();

        // Create Log
        $logParams['loggable_type'] = 'checklists';
        $logParams['loggable_id'] = null;
        $logParams['action'] = 'create';
        $logParams['value'] = $description . ' Data created';
        $log = HistoryLibrary::createLog($logParams);

        $checklist = Checklist::where('id', $checklist->id)
            ->with('items')
            ->first();
        $response['message'] = 'New data created';
        $response['data'] = $checklist;
        return response()->json($response, 200);
    }

    /**
     * Show details
     *
     * @return response
     */
    public function show($id) {
        $checklist = Checklist::where('id', $id)->with('items')->first();
		if (!isset($checklist)) {
            $response['message'] = 'Cannot find the data';
			return response()->json($response, 422);
		}

        $response['data'] = $checklist;
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
            'object_domain' => 'required',
            'object_id' => 'required',
            'description' => 'required',
        ]);

        $user = Auth::user();
        $updated_by = $user->name;

		$template_id = $request->has('template_id') ? trim($request->input('template_id')) : false;
		$object_domain = $request->has('object_domain') ? trim($request->input('object_domain')) : false;
		$object_id = $request->has('object_id') ? trim($request->input('object_id')) : false;
		$description = $request->has('description') ? trim($request->input('description')) : false;
        $completed_at = $request->has('completed_at') ? trim($request->input('completed_at')) : false;
        $due = $request->has('due') ? trim($request->input('due')) : false;
        $urgency = $request->has('urgency') ? trim($request->input('urgency')) : false;

		$is_completed = false;
        if ($request->has('is_completed')) {
            $is_completed = true;
            $is_completed_value = (bool) $request->input('is_completed');
        }

        $checklist = Checklist::where('id', $id)->first();
        if($checklist){
            if ($template_id) $checklist->template_id = $template_id;
            if ($object_domain) $checklist->object_domain = $object_domain;
            if ($object_id) $checklist->object_id = $object_id;
            if ($description) $checklist->description = $description;
            if ($completed_at) $checklist->completed_at = $completed_at;
            if ($updated_by) $checklist->updated_by = $updated_by;
            if ($due) $checklist->due = $due;
            if ($urgency) $checklist->urgency = $urgency;
            if ($is_completed) $checklist->is_completed = $is_completed_value;
            $checklist->save();
            $checklist = Checklist::where('id', $id)->with('items')->first();

            // Create Log
            $logParams['loggable_type'] = 'checklists';
            $logParams['loggable_id'] = null;
            $logParams['action'] = 'update';
            $logParams['value'] = $description . ' Data updated';
            $log = HistoryLibrary::createLog($logParams);

            $response['message'] = 'Data updated';
            $response['data'] = $checklist;
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
        $checklist = Checklist::find($id);
		if (isset($checklist)) {
			$checklist->delete();
			$response['message'] = 'Data deleted';
			$response['data'] = $checklist;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'Cant find the data';
            return response()->json($response, 400);
		}
		return $checklist;
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
            'checklist_ids' => 'required',
        ]);
        $user = Auth::user();
        $updated_by = $user->name;

		$checklist_ids = gettype($request->input('checklist_ids')) === 'string' ? json_decode($request->input('checklist_ids')) : $request->input('checklist_ids');
        $template_id = $request->has('template_id') ? trim($request->input('template_id')) : false;
		$object_domain = $request->has('object_domain') ? trim($request->input('object_domain')) : false;
		$object_id = $request->has('object_id') ? trim($request->input('object_id')) : false;
		$description = $request->has('description') ? trim($request->input('description')) : false;
        $completed_at = $request->has('completed_at') ? trim($request->input('completed_at')) : false;
        $due = $request->has('due') ? trim($request->input('due')) : false;
        $urgency = $request->has('urgency') ? trim($request->input('urgency')) : false;

		$is_completed = false;
        if ($request->has('is_completed')) {
            $is_completed = true;
            $is_completed_value = (bool) $request->input('is_completed');
        }

        if (count($checklist_ids) > 0) {
			$checklists = Checklist::whereIn('id', $checklist_ids)->get();
			foreach($checklists as $checklist) {
				if ($template_id) $checklist->template_id = $template_id;
                if ($object_domain) $checklist->object_domain = $object_domain;
                if ($object_id) $checklist->object_id = $object_id;
                if ($description) $checklist->description = $description;
                if ($completed_at) $checklist->completed_at = $completed_at;
                if ($updated_by) $checklist->updated_by = $updated_by;
                if ($due) $checklist->due = $due;
                if ($urgency) $checklist->urgency = $urgency;
                if ($is_completed) $checklist->is_completed = $is_completed_value;
                $checklist->save();

                // Create Log
                $logParams['loggable_type'] = 'checklists';
                $logParams['loggable_id'] = null;
                $logParams['action'] = 'update';
                $logParams['value'] = $description . ' Data updated';
                $log = HistoryLibrary::createLog($logParams);
            }
            $checklists = Checklist::whereIn('id', $checklist_ids)->get();
			$response['message'] = count($checklists) . ' Data updated';
			$response['data'] = $checklists;
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
            'checklist_ids' => 'required',
        ]);

        $checklist_ids = gettype($request->input('checklist_ids')) === 'string' ? json_decode($request->input('checklist_ids')) : $request->input('checklist_ids');
		if (count($checklist_ids) > 0) {
            $checklists = Checklist::whereIn('id', $checklist_ids)->delete();
            $response['message'] = $checklists . ' Data deleted';
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data deleted';
			return response()->json($response, 200);
		}
	}

    /**
     * Delete by template
     *
     * @param Request $request
     * @return array|Response
     */
    public function deleteByTemplate($template_id)
    {
        $checklists = Checklist::where('template_id', $template_id)->delete();
		if ($checklists > 0) {
            $response['message'] = $checklists . ' Data deleted';
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data deleted';
			return response()->json($response, 200);
		}
    }

    /**
     * Assign checklist by template
     *
     * @param Request $request
     * @return array|Response
     */
    public function assignChecklists(Request $request, $template_id)
    {
        $user = Auth::user();
        $updated_by = $user->name;

        $checklists = Checklist::where('template_id', $template_id)->get();
		$object_domain = $request->has('object_domain') ? trim($request->input('object_domain')) : false;
		$object_id = $request->has('object_id') ? trim($request->input('object_id')) : false;
		$description = $request->has('description') ? trim($request->input('description')) : false;
        $completed_at = $request->has('completed_at') ? trim($request->input('completed_at')) : false;
        $due = $request->has('due') ? trim($request->input('due')) : false;
        $urgency = $request->has('urgency') ? trim($request->input('urgency')) : false;

        $is_completed = false;
        if ($request->has('is_completed')) {
            $is_completed = true;
            $is_completed_value = (bool) $request->input('is_completed');
        }

        if (count($checklists) > 0) {
			foreach($checklists as $checklist) {
                if ($object_domain) $checklist->object_domain = $object_domain;
                if ($object_id) $checklist->object_id = $object_id;
                if ($description) $checklist->description = $description;
                if ($completed_at) $checklist->completed_at = $completed_at;
                if ($updated_by) $checklist->updated_by = $updated_by;
                if ($due) $checklist->due = $due;
                if ($urgency) $checklist->urgency = $urgency;
                if ($is_completed) $checklist->is_completed = $is_completed_value;
                $checklist->save();

                // Create Log
                $logParams['loggable_type'] = 'checklists';
                $logParams['loggable_id'] = null;
                $logParams['action'] = 'assign';
                $logParams['value'] = $description . ' Data assigned';
                $log = HistoryLibrary::createLog($logParams);
            }

            $checklists = Checklist::where('template_id', $template_id)
                ->with('items')
                ->get();
			$response['message'] = count($checklists) . ' Data assigned';
			$response['data'] = $checklists;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data assigned';
			return response()->json($response, 200);
		}
	}
}
