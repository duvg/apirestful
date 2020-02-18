<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\User;
use Illuminate\Http\Request;

class UserController extends ApiController
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

        return $this->showAll($users);
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
        $fields['verification_token'] = User::generateVerificationToken();
        $fields['admin'] = User::USER_REGULAR;

        $user = User::create($fields);

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
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

                return $this->errorResponse('Solo los usuatios verificados puden cambiar su rol a administrador', 409);
            }

            $user->admin = $request->admin;
        }

        // Check if data don`t  have new changes
        if (!$user->isDirty()) {

            return $this->errorResponse('Se debe especificar al menos un valor diferente para actualizar', 422);
        }

        // Save changes if pass all validations
        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        // Return user deleted
        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::USER_VERIFIED;

        $user->verification_token = null;

        $user->save();

        return $this->showMessage('La cuenta ha sido verificada', 200);
    }
}
