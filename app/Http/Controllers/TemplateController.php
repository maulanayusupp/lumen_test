<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Template;

class TemplateController extends Controller
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

        $templates = Template::orderBy('created_at', $sort)
            ->where(function ($src) use ($filter, $keyword) {
                $src->where('name', $filter, '%'.$keyword.'%');
            })
            ->paginate($pageLimit);

        $response['data'] = $templates;
        return response()->json($response, 200);
    }

    /**
     * Store data / insert data to the database
     *
     * @return response
     */
    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required',
        ]);

		$name = $request->has('name') ? trim($request->input('name')) : false;

        $template = new Template();
        if ($name) $template->name = $name;
        $template->save();

        $response['message'] = 'New data created';
        $response['data'] = $template;
        return response()->json($response, 200);
    }

    /**
     * Show details
     *
     * @return response
     */
    public function show($id) {
        $template = Template::find($id);
		if (!isset($template)) {
            $response['message'] = 'Cannot find the data';
			return response()->json($response, 422);
		}

        $response['data'] = $template;
        return response()->json($response, 200);
    }

    /**
     * Update data
     *
     * @return response
     */
    public function update(Request $request, $id) {
        $name = $request->has('name') ? trim($request->input('name')) : false;

        $template = Template::where('id', $id)->first();
        if($template){
            if ($name) $template->name = $name;
            $template->save();

            $response['message'] = 'Data updated';
            $response['data'] = $template;
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
        $template = Template::find($id);
		if (isset($template)) {
			$template->delete();
			$response['message'] = 'Data deleted';
			$response['data'] = $template;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'Cant find the data';
            return response()->json($response, 400);
		}
		return $template;
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
            'template_ids' => 'required',
        ]);

		$template_ids = gettype($request->input('template_ids')) === 'string' ? json_decode($request->input('template_ids')) : $request->input('template_ids');
        $name = $request->has('name') ? trim($request->input('name')) : false;

        if (count($template_ids) > 0) {
			$templates = Template::whereIn('id', $template_ids)->get();
			foreach($templates as $template) {
				if ($name) $template->name = $name;
				$template->save();
            }
            $templates = Template::whereIn('id', $template_ids)->get();
			$response['message'] = count($templates) . ' Data updated';
			$response['data'] = $templates;
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
            'template_ids' => 'required',
        ]);

        $template_ids = gettype($request->input('template_ids')) === 'string' ? json_decode($request->input('template_ids')) : $request->input('template_ids');
		if (count($template_ids) > 0) {
            $templates = Template::whereIn('id', $template_ids)->delete();
            $response['message'] = $templates . ' Data deleted';
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data deleted';
			return response()->json($response, 200);
		}
	}
}
