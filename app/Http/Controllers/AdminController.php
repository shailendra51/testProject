<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Admin;
use Config;
/**
 * Admin Controller
 *
 * @package                BlogProject
 * @subpackage             AdminController
 * @category               Controller
 * @DateOfCreation         23 March 2018
 * @ShortDescription       This controller handles authenticating admin for the application and
 *                         redirecting them to dashboard screen. The controller uses a trait
 *                         to conveniently provide its functionality to your applications.
 **/
class AdminController extends Controller
{
    use AuthenticatesUsers;
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }
    /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       Load the login view for admin
    * @return                 View
    */
    public function getLogin()
    {
        if (auth()->guard('admin')->user()) return redirect()->route('dashboard');
        return view('admin.login');
    }
    /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       Handle a login request to the application
    * @param1                 App\Http\Requests\LoginRequest  $request
    * @return                 Response
    */
    public function postLogin(Request $request)
    {
        $rules = array(
            'email' => 'required',
            'password' => 'required'
        );
        // set validator
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) 
        {
            return redirect()->back()->withErrors($validator->errors());
        }
        else
        {
            // Get our login input
            $inputData = array(
                'usr_email' => $request->input('email'),
                'password' => $request->input('password'),
                'usr_role_id' => Config::get('constants.ADMIN_ROLE')
            );
            if (Auth::guard('admin')->attempt($inputData)) 
            {
                return redirect("/dashboard")->with(array("message"=>"Login Successful"));
            }
            else
            {
                //Check Email exist in the database or not
                 if (Admin::where(
                        [['usr_email', '=', $inputData['usr_email']],
                        ['usr_role_id', '=', Config::get('constants.ADMIN_ROLE')]])->first())  
                {
                    $validator->getMessageBag()->add('password', 'Wrong password');
                }
                else
                {
                    $validator->getMessageBag()->add('email', 'Any account does not exits with this email address');
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }
    }
}