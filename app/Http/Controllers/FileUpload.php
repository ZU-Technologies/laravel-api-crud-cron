<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;

class FileUpload extends Controller
{
  public function createForm(){
    return view('file-upload');
  }
  public function fileUpload(Request $req){
        $req->validate([
        'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        $fileModel = new File;
        if($req->file()) {
            $fileName = time().'_'.$req->file->getClientOriginalName();
            $filePath = $req->file('file')->storeAs('uploads', $fileName, 'public');
            $fileModel->name = time().'_'.$req->file->getClientOriginalName();
            $fileModel->file_path = '/storage/' . $filePath;
            $fileModel->save();
            return back()
            ->with('success','File has been uploaded.')
            ->with('file', $fileName);
        }
   }

   public function index(Request $request, Schedule $schedule)
    {
        // $data=array ('name'=>"Ahmed",'email'=>"hi4q2i6aaa2@7aqhausda.com", 'password'=>"abdjhksnas", 'created_at'=>Carbon::now());
        // DB::table('users')->insert($data);
        // echo "data store";


        $users = User::paginate(10);

        return view('users', compact('users'));
        $schedule->call(function () {
            User::delete();
        })->everyFiveMinutes();
    }
}
