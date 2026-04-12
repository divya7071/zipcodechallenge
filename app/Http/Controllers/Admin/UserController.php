<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Hash;
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
        return view('admin.account.profile');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'notification_email' => 'email',

        ]);
        $user = User::find($user);
        $user->update($request->all());
        return redirect()->route('admin.account.index')->with('success_message', 'Profile Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
    public function updatePassword(Request $request)
    {

        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|min:6|same:password',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = User::find(Auth::user()->id);

        if (!Hash::check($request->current_password, $user->password)) {

            return redirect()->route('admin.account.index')->with('error_message', 'Current password does not match');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('admin.account.index')->with('success_message', 'Password Updated Successfully');
    }
}
