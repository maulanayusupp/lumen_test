<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
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

        $users = User::orderBy('created_at', $sort)
            ->where(function ($src) use ($filter, $keyword) {
                $src->where('name', $filter, '%'.$keyword.'%')
                    ->orWhere('email', $filter, '%'.$keyword.'%');
            })
            ->paginate($pageLimit);

        $response['data'] = $users;
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
            'email' => 'required|email|unique:users'
        ]);

		$name = $request->has('name') ? trim($request->input('name')) : false;
		$email = $request->has('email') ? trim($request->input('email')) : false;
		$password = $request->has('password') ? trim($request->input('password')) : 'asdfasdf';
		$language = $request->has('language') ? trim($request->input('language')) : false;
		$gender = $request->has('gender') ? trim($request->input('gender')) : false;
		$role = $request->has('role') ? trim($request->input('role')) : false;

        $is_active = false;
        if ($request->has('is_active')) {
            $is_active = true;
            $is_active_value = (bool) $request->input('is_active');
        }


        if($email){
            // Check email availability
            $isEmailAvailable = self::isEmailAvailable($email);
            if (!$isEmailAvailable) {
                $response = [
                    'message' => 'Email already registered'
                ];
                return response()->json($response, 400);
            }
        }

        $user = new User();
        if ($name) $user->name = $name;
        if ($email) $user->email = $email;
        if ($password) $user->password = app('hash')->make($password);
        if ($language) $user->language = $language;
        if ($gender) $user->gender = $gender;
        if ($role) $user->role = $role;
        if ($is_active) $user->is_active = $is_active_value;
        $user->save();

        $response['message'] = 'New data created';
        $response['data'] = $user;
        return response()->json($response, 200);
    }

    /**
     * Show details
     *
     * @return response
     */
    public function show($id) {
        $user = User::find($id);
		if (!isset($user)) {
            $response['message'] = 'Cannot find the data';
			return response()->json($response, 422);
		}

        $response['data'] = $user;
        return response()->json($response, 200);
    }

    /**
     * Update data
     *
     * @return response
     */
    public function update(Request $request, $id) {
        $name = $request->has('name') ? trim($request->input('name')) : false;
		$email = $request->has('email') ? trim($request->input('email')) : false;
		$password = $request->has('password') ? trim($request->input('password')) : 'asdfasdf';
		$language = $request->has('language') ? trim($request->input('language')) : false;
		$gender = $request->has('gender') ? trim($request->input('gender')) : false;
		$role = $request->has('role') ? trim($request->input('role')) : false;

        $is_active = false;
        if ($request->has('is_active')) {
            $is_active = true;
            $is_active_value = (bool) $request->input('is_active');
        }


        if($email){
            // Check email availability
            $isEmailAvailable = self::isEmailAvailable($email);
            if (!$isEmailAvailable) {
                $response['message'] = 'Email already registered';
                return response()->json($response, 400);
            }
        }

        $user = User::where('id', $id)->first();

        if($user){
            if ($name) $user->name = $name;
            if ($email) $user->email = $email;
            if ($password) $user->password = app('hash')->make($password);
            if ($language) $user->language = $language;
            if ($gender) $user->gender = $gender;
            if ($role) $user->role = $role;
            if ($is_active) $user->is_active = $is_active_value;
            $user->save();

            $response['message'] = 'Data updated';
            $response['data'] = $user;
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
        $user = User::find($id);
		if (isset($user)) {
			$user->delete();
			$response['message'] = 'Data deleted';
			$response['data'] = $user;
			return response()->json($response, 200);
		} else {
			$response['message'] = 'Cant find the data';
            return response()->json($response, 400);
		}
		return $user;
    }

    /**
     * Check email availability
     *
     * @param  string $email
     * @return bool|Response
     */
	public function isEmailAvailable($email)
	{
		$user = User::where('email', $email)->first();
		if (isset($user)) {
			return false;
		} else {
			return true;
		}
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
            'user_ids' => 'required',
        ]);

		$user_ids = gettype($request->input('user_ids')) === 'string' ? json_decode($request->input('user_ids')) : $request->input('user_ids');
		$name = $request->has('name') ? trim($request->input('name')) : false;
		// $email = $request->has('email') ? trim($request->input('email')) : false;
		$password = $request->has('password') ? trim($request->input('password')) : 'asdfasdf';
		$language = $request->has('language') ? trim($request->input('language')) : false;
		$gender = $request->has('gender') ? trim($request->input('gender')) : false;
		$role = $request->has('role') ? trim($request->input('role')) : false;

        $is_active = false;
        if ($request->has('is_active')) {
            $is_active = true;
            $is_active_value = (bool) $request->input('is_active');
        }

        if (gettype($user_ids) === 'array' && count($user_ids) > 0) {
			$users = User::whereIn('id', $user_ids)->get();
			foreach($users as $user) {
				if ($name) $user->name = $name;
                // if ($email) $user->email = $email;
                if ($password) $user->password = app('hash')->make($password);
                if ($language) $user->language = $language;
                if ($gender) $user->gender = $gender;
                if ($role) $user->role = $role;
                if ($is_active) $user->is_active = $is_active_value;
				$user->save();
            }
            $users = User::whereIn('id', $user_ids)->get();
			$response['message'] = count($users) . ' Data updated';
			$response['data'] = $users;
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
            'user_ids' => 'required',
        ]);

        $user_ids = gettype($request->input('user_ids')) === 'string' ? json_decode($request->input('user_ids')) : $request->input('user_ids');
		if (count($user_ids) > 0) {
            $users = User::whereIn('id', $user_ids)->delete();
            $response['message'] = $users . ' Data deleted';
			return response()->json($response, 200);
		} else {
			$response['message'] = 'No data deleted';
			return response()->json($response, 200);
		}
	}
}
