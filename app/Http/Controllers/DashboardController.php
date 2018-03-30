<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Admin;
use App\Blog;
use Config;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
/**
 * Dashboard Controller
 *
 * @package                BlogProject
 * @subpackage             DashboardController
 * @category               Controller
 * @DateOfCreation         23 March 2018
 * @ShortDescription       This controller contain all the Method for the super admin ,
 *                         Only access by the admin after login , admin can manage users and
 *                         blogs from here.
 **/
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
     /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       Load the dashboard view 
    * @return                 View
    */
    public function index(){
        /**
        *@ShortDescription Blank array for the count for sending the array to the view.
        *
        * @var Array
        */
    	$count = [];
    	$count['users']  = Admin::where('usr_role_id', Config::get('constants.USER_ROLE'))->count();
    	$count['blogs_publish']  = Blog::where('blg_status', Config::get('constants.ACTIVE_STATUS'))->count();
    	$count['blogs_pending']  = Blog::where('blg_status', Config::get('constants.DEACTIVE_STATUS'))->count();
    	return view('admin.dashboard',compact('count'));
    }
     /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       Load users view with list of all users 
    * @return                 View
    */
    public function users()
    {
        /**
        *@ShortDescription Blank array for the data for sending the array to the view.
        *
        * @var Array
        */
    	$data = [];
    	$data['users'] = Admin::where('usr_role_id', '!=' , Config::get('constants.ADMIN_ROLE'))->get();
    	return view('admin.users',$data);
    }
    /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       Load blogs view with list of all blogs  
    * @return                 View
    */
    public function blogs()
    {
     	$data['blogs'] = Blog::all();
    	return view('admin.blogs',$data);
    }
    /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       Function run according to the parameter if $blog_id is NUll 
    *                          then it return add view If we get ID it will return edit view 
    * @return                 View
    */
    public function getBlog($blog_id = NULL)
    {   
        if(!empty($blog_id)){
            try {
                $id = Crypt::decrypt($blog_id);
                $check = Blog::where('blg_id','=',$id)->count();
                if(is_int($id) && $check > 0){
                $data['blog'] = Blog::find($id);
                return view('admin.editBlog',$data);
                }
                else{
                return redirect()->back()->withErrors(__('messages.Id_incorrect'));
                }
            }
            catch (DecryptException $e) {
                return view("admin.errors");
            }
        }else{
    	   return view('admin.addBlog');
        }
    }
    /**
    * @DateOfCreation         23 March 2018
    * @ShortDescription       This function handle the post request which get after submit the 
    *                         and function run according to the parameter if $blog_id is NUll 
    *                         then it will insert the value If we get ID it will update the value
    *                         according to the ID 
    * @return                 Response
    */
    public function postBlog(Request $request, $blog_id = null)
    {   
        $rules = array(
            'blog_title' => 'required|min:5|max:50', 
            'blog_description' => 'required|min:5|max:500', 
            'blog_category'=>'required|min:5'
        );
        // set validator
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            // redirect our admin back to the form with the errors from the validator
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }
        else
        {
            if(empty($blog_id)){    
                //final array of the data from the request
                $insertData = array(
                'blg_title' => $request->input('blog_title'), 
                'blg_description' => $request->input('blog_description'), 
                'blg_category'=> $request->input('blog_category'),
                'blg_status' => $request->input('status')
                );
                $blog = Blog::create($insertData); //insert data in blogs table
                if($blog){
                    return redirect('adminBlogs')->with('message',__('messages.Record_added'));
                }else{
                    return redirect()->back()->withInput()->withErrors(__('messages.try_again')); 
                }
            }else{
                $id = Crypt::decrypt($blog_id);
                //final array of the data from the request
                $updateData = array(
                    'blg_title' => $request->input('blog_title'), 
                    'blg_description' => $request->input('blog_description'), 
                    'blg_category' => $request->input('blog_category'),
                    'blg_status' => $request->input('status')
                );
                if(is_int($id)){
                    $blog = Blog::where(array('blg_id' => $id))->update($updateData); //update data in blogs table
                    return redirect('adminBlogs')->with('message',__('messages.Record_updated'));
                }else{
                    return redirect()->back()->withInput()->withErrors(__('messages.try_again')); 
                }
                
            }   
        }
    }
     /**
    * @DateOfCreation         27 March 2018
    * @ShortDescription       Get the ID from the ajax and pass it to the function to delete it 
    * @return                 Response
    */
    public function deleteBlog(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->input('id'));
            if(is_int($id)){
                $blog = Blog::findOrFail($id);
                if($blog->delete()){
                    return Config::get('constants.OPERATION_CONFIRM');
                }else{
                    return Config::get('constants.OPERATION_FAIED');
                }
            }else{
                return Config::get('constants.ID_NOT_CORRECT');
            } 
        }
        catch (DecryptException $e) {
            return Config::get('constants.ID_NOT_CORRECT');
        }
    }
}
