<?php

namespace App\Http\Controllers\AdminControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Auctionproducts;
use Auth;
class AdminController extends Controller
{
    public function index(){
        return view('sellProduct');
    }
    public function allAuctions(){
    	$products = \App\Auctionproducts::orderBy('created_at','desc')->get();
		return view('admin/allAuctions')->with('products',$products);
    }
    public function approve($id){
			$auction = Auctionproducts::find($id);
			$auction->approved = 1;
			$auction->save();
			return response()->json(['message'=>'The auction have been successfully saved']);
	}
	public function approveall(){
			$auction = Auctionproducts::where('approved', 0)->update(['approved' => 1]);
			return response()->json(['message'=>'All auctions have been successfully saved']);
	}
    public function saveAuctionProduct(Request $request)
    {
        if ($request->hasFile('productPicture')) {
            
        }
        $data = $request->all();
        $validate = \Validator::make($data,[
            'productName'=>'required',
            'minimalPrice'=>'required|numeric|min:200',
            'auctionEndTime'=>'required',
            'description'=>'required',
            'productPicture'=>['required','mimes:jpeg,bmp,png',Rule::dimensions()->minWidth(300)->minHeight(300)]
            ]);
        if ($validate->fails()) {
            return redirect()->back()->withInput()->withErrors($validate->errors());
        }
        $filename = str_replace(' ', '_', $request->input("productName"))."_".$request->input("minimalPrice")."_".time().str_random(30).'.'.$request->productPicture->getClientOriginalExtension();
        $request->productPicture->move(public_path().'/img/products',$filename);
        $product = new AuctionProducts();
        $product->user_id = Auth::user()->id;
        $product->product_name = $request->input("productName");
        $product->minimal_price = $request->input("minimalPrice");
        $product->end_date_time = $request->input("auctionEndTime")/*.' '.$request->input("auctionEndTime")*/;
        $product->picture = $filename;
        $product->approved = 0;
        $product->description = $request->input("description");
        $product->save();
        return redirect()->back()->withMessage('Successfully product has put on Auction market');

    }
    public function sold(){
        $products = Auth::user()->auctions();
        return view('soldProducts')->with('products',$products);
    }
    public function soldProductDetails($id){
        $product = AuctionProducts::where('id','=',$id)->get();
        return view('soldProductsDetails')->with('product',$product);
    }
    public function soldProductClose($id){
        $product = Auth::user()->auctions($id);
        $product->sold = 1;
        $product->save();
        return redirect()->back()->withMessage('Your Auction has been successfully closed');
    }
    public function soldProductDelete($id){
        $product = Auth::user()->auctions($id);
        $product->forceDelete();
        return redirect()->route('sold');
    }
}
