<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // List all resources from user entity
        $users = User::all();

        // Response json(data, code_status)
        return request()->json(200, ["data" => $users]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Valdiation rules
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];

        // Validate request
        $this->validate($request, $rules);

        // Save a new user
        $fields = $request->all();
        $fields['passord'] = bcrypt($request->password);
        $fields['verified'] = User::USER_UNVERIFIED;
        $fields['verification_token'] = User::generarVerificationToken();
        $fields['admin'] = User::USER_REGULAR;

        $user = User::create($fields);

        // Response json(data, code_status)
        return response()->json(['data' => $user], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // find a user by id
        $user = User::findOrFail($id);

        // Response json(data, code_status)
        return response()->json(['data' => $user], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // Find user to update
        $user = User::findOrFail($id);

        // Rules
        $rules = [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::USER_ADMIN . ',' . User::USER_REGULAR,
        ];

        // Validate request
        $this->validate($request, $rules);

        // Update fields if exists in request
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && ($user->email != $request->email)) {
            $user->verified = User::USER_UNVERIFIED;
            $user->verification_token = User::generateVerificationToken();
            $user->email = $request->email;
        }

        if ( $request->has('password') ) {
            $user->password = bcrypt($request->password);
        }

        // TODO: Validate role admin, this action only be executed by admin user
        if ($request->has('admin')) {
            if (!$user->esVerified) {

                // Response json(data, code_status)
                return response()->json( 
                    [
                        'error' => 'Solo los usuatios verificados puden cambiar su rol a administrador', 
                        'code' => 409
                    ], 
                    409
                );
            }

            $user->admin = $request->admin;
        }

        // Check if data don`t  have new changes
        if (!$user->isDirty()) {
            // Response json(data, code_status)
            return response()->json(
                [
                    'error' => 'Se debe especificar al menos un valor diferente para actualizar',
                    'code' => 422 
                ],
                422
            );
        }

        // Save changes if pass all validations
        $user->save();

        // Response json(data, code_status)
        return response()->json(['data' => $user], 200);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find user to delete
        $user = User::findOrFail($id);

        $user->delete();

        // Return user deleted
        // Response json(data, code_status)
        return response()->json(['data' => $user], 200);
    }
}
