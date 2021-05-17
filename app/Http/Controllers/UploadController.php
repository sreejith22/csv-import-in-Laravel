<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\LazyCollection;
use App\Models\Module;
use App\Mail\NotifyMail;
use Mail;


class UploadController extends Controller
{
    public function index(){
        return view('csv');
      }
    
      public function uploadFile(Request $request){
            $request->validate([
            'file' => 'required|mimetypes:text/csv,text/plain,application/csv,text/comma-separated-values,text/anytext,application/octet-stream,application/txt'
            ]);

            $error1 = 0;
    
            $file = new Module;
    
            if($request->file()) {
                $name = time().'_'.$request->file->getClientOriginalName();
                $filePath = $request->file('file')->storeAs('uploads', $name, 'public');
       
        $fp = file(storage_path('app/public/uploads/'.$name.''), FILE_SKIP_EMPTY_LINES);
        //print_r($fp);die;
        $filePath = storage_path('app/public/uploads/'.$name.'');
        $handle = fopen($filePath, 'r');
        $fp1 = fgetcsv($handle);
        //print_r($fp1);die; 
        if(count($fp) == 1001){
            if(count($fp1) == 3){
                LazyCollection::make(function () use ($name) {
                   
                    $filePath = storage_path('app/public/uploads/'.$name.'');
                    $handle = fopen($filePath, 'r');
                    while ($line = fgetcsv($handle)) {
                        yield $line;
                        
                    }
                })
               
            
                    ->chunk(1000) //split in chunk to reduce the number of queries
                    ->each(function ($lines) {//echo '<pre>';print_r($lines);die;
                        
                        $list = [];
                       
                            $i = 0;
                            foreach ($lines as $line) {
                         
                                if($i!= 0){
                                if (isset($line[0])) {
                                    $list[] = [
                                        'module_code' => $line[0],
                                        'module_name' => $line[1],
                                        'module_term' => $line[2]
                                    ];
                                }
                            }
                            $i++;
                            }
                         
                        
                        Module::insert($list);
                    });

                    //sent maill

                    $user['email'] = 'sreejithgirieeshnair@gmail.com';
                    $maildata = [
                        'title' => 'csv import',
                        'maildata' => 'File has uploaded to the database.'
                    ];
                    Mail::send('emails/demoMail', $maildata, function($message) use ($user) {
	        $message->to($user['email']);
	        $message->subject('CSV IMPORT');
    	});
                

                    
                    return back()
                     ->with('success','File has uploaded to the database.')
                     ->with('file', $name);
                
            }else{
                $user['email'] = 'sreejithgirieeshnair@gmail.com';
                $maildata = [
                    'title' => 'csv import',
                    'maildata' => 'Only 3 columns allowed.'
                ];
                Mail::send('emails/demoMail', $maildata, function($message) use ($user) {
        $message->to($user['email']);
        $message->subject('CSV IMPORT');
    });
                return redirect()->back()->with('success', 'Only 3 columns allowed.');
            }
           
        }else{
            $user['email'] = 'sreejithgirieeshnair@gmail.com';
            $maildata = [
                'title' => 'csv import',
                'maildata' => 'Only 1000 rows allowed.'
            ];
            Mail::send('emails/demoMail', $maildata, function($message) use ($user) {
    $message->to($user['email']);
    $message->subject('CSV IMPORT');
});
            return redirect()->back()->with('success', 'Only 1000 allowed.');

        }
  
       }
    }
}
