<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Blog;
use Session;
use \Crypt;
use Config;
use App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
/**
 * Home Controller
 *
 * @package                BlogProject
 * @subpackage             HomeController
 * @category               Controller
 * @DateOfCreation         22 March 2018
 * @ShortDescription       Default controller when site load index method called
 *                         
 **/
class HomeController extends Controller
{
   /**
    * @DateOfCreation         21 March 2018
    * @ShortDescription       Display home page
    * @return                 View
    */
    public function index()
    {   
        return view('home.welcome');
    }
    /**
    * @DateOfCreation         21 March 2018
    * @ShortDescription       Get All blogs Which have status Active 
    * @return                 View
    */
    public function getBlogs(){
        $data['blogs'] = Blog::where('blg_status',Config::get('constants.ACTIVE_STATUS'))->get();
        return view('home.blogs',$data);
    }
    /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       Get All blogs Which have status Active 
    * @return                 View
    */
    public function getBlogdetails($blog_id = NULL){
        try {
            $id = Crypt::decrypt($blog_id);
            $check = Blog::where('blg_id','=',$id)->count();
            if(is_int($id) && $check > 0){
                $data['blog'] = Blog::find($id);
                return view('home.blogdetail',$data);
            }
            else{
                return redirect()->back()->withErrors(__('messages.Id_incorrect'));
            }
        }
        catch (DecryptException $e) {
            return view("home.errors");
        }
    }
    /**
    * @DateOfCreation         29 March 2018
    * @ShortDescription       change the language according to the input 
    * @return                 Response
    */
    public function postChangeLanguage(Request $request) 
	{
        \Session::put('locale', $request->input('lang'));
        return redirect()->back();
	}
    /**
    * @DateOfCreation         22 March 2018
    * @ShortDescription       Distroy the session and Make the Auth Logout 
    * @return                 Response
    */
    public function getLogout(){
	    Auth::logout();
    	Session::flush();
    	return redirect('/');
    }
}
