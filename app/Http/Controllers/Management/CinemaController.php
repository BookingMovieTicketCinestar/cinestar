<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cinema;
use App\Models\City;
use Auth;
class CinemaController extends Controller
{
    public function CinemaIndex()
    {
        $cinemas = Cinema::select('*')->leftJoin('city','cinema.CityID','=','city.CityID')->get();
        return view('admin.cinema.index',compact('cinemas'));        
    }

    public function getDataOption()
    {
        $citys = City::all();
        return response()->json($citys);
    }

    public function addCinema(Request $request)
    {
        $request->validate([
            'add_name' => 'required',
            'add_address' => 'required',
            'add_toltalcinemahalls' => 'required',
            'add_thumbnail' => 'mimes:jpeg,jpg,png|required|max:10000',
        ],[
            'add_name.required' => 'Chưa nhập tên rạp',
            'add_address.required' => 'Chưa nhập địa chỉ',
            'add_toltalcinemahalls.required' => 'Chưa nhập đạo diễn phim',
            'add_thumbnail.mimes' => 'Hãy chọn file ảnh đúng định dạng jpeg,jpg,png',
            'add_thumbnail.required' => 'Hãy chọn file ảnh',
            'add_thumbnail.max' => 'File ảnh quá lớn',
        ]);
        $originalFileName = $request->file('add_thumbnail')->getClientOriginalName();
        $user_id = Auth::id();
        $img = 'image'.$user_id.'-'.time().'-'.$originalFileName;
        $request->file('add_thumbnail')->move(public_path('/imgCinema'),$img);
        try{
            $cinema = new Cinema();
            $cinema->CinemaID = $request->addCinemaID;
            $cinema->Name = $request->add_name;
            $cinema->Thumbnail = $img;
            $cinema->Address = $request->add_address;
            $cinema->TotalCinemaHalls = $request->add_toltalcinemahalls;
            $cinema->CityID = $request->add_city_id;
            $cinema->save();
            return redirect()->back()->with('mess','Thêm thành công');

        }catch(\Illuminate\Database\QueryException $e)
        {
            return redirect()->back()->withErrors('Thêm không thành công: '.$e->getMessage());
        }
    }
    public function deleteCinema(Request $request)
    {
        try{
            $cinema = Cinema::find($request->deleteCinemaID);
            if(!$cinema)
                return redirect()->back()->withErorrs('Không tìm thấy rạp muốn xóa');
            $cinema->delete();
            return redirect()->back()->with('mess','Xóa thành công');    
        }catch(\Exception $e)
        {
            $mess = "Xóa không thành công ".$e->getMessage();
            return redirect()->back()->withErrors($mess);
        }
    }
}