<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\History;

class HistoryController extends Controller
{
    /**
     * List all
     *
     * @return response
     */
    public function list(Request $request, $type) {
        $filter = $request->has('filter') ? $request->input('filter') : 'like';
        $sort = $request->has('sort') ? $request->input('sort') : 'asc';
        $keyword = $request->input('keyword');
        $pageLimit = $request->input('page_limit');

        $histories = History::where('loggable_type', $type)
            ->where(function ($src) use ($filter, $keyword) {
                $src->where('value', $filter, '%'.$keyword.'%');
            })
            ->orderBy('created_at', $sort)
            ->paginate($pageLimit);

        $response['data'] = $histories;
        return response()->json($response, 200);
    }

    /**
     * Show details
     *
     * @return response
     */
    public function show($id) {
        $history = History::find($id);
		if (!isset($history)) {
            $response['message'] = 'Cannot find the data';
			return response()->json($response, 422);
		}

        $response['data'] = $history;
        return response()->json($response, 200);
    }
}
