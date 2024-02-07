<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class AdminProductController extends Controller
{
    public function index()
    {
        $viewData = [];
        $viewData["title"] = "Admin Page - Products - Online Store";
        $viewData["products"] = Product::all();
        return view('admin.product.index')->with("viewData", $viewData);
    }

    public function store(Request $request) 
    { 
        Product::validate($request); 
 
        $newProduct = new Product(); 
        $newProduct->setName($request->input('name')); 
        $newProduct->setDescription($request->input('description')); 
        $newProduct->setPrice($request->input('price')); 
        $newProduct->setImage("game.png"); 
        $newProduct->save(); 

        if ($request->hasFile('image')) { 
            $imageName = $newProduct->getId().".".$request->file('image')->extension(); 
            Storage::disk('public')->put( 
                $imageName, 
                file_get_contents($request->file('image')->getRealPath()) 
            ); 
            $newProduct->setImage($imageName); 
            $newProduct->save();
        }
 
        return back(); // Redirect to the user previous location
    }

    public function delete($id) { 
        Product::destroy($id); 
        return back(); // Redirect to the user previous location
    } 

    // We have an edit method that searches for a product based on its id, and sends it to the 
    // admin.product.edit view. It is the product we are going to edit. 

    public function edit($id) { 
        $viewData = []; 
        $viewData["title"] = "Admin Page - Edit Product - Online Store"; 
        $viewData["product"] = Product::findOrFail($id); 
        return view('admin.product.edit')->with("viewData", $viewData); 
    } 


    // Then, we have the update method. It is like the store method. 
    // 1 We collect the request and the id of the product to be updated. 
    // 2 We search for a product based on that id, and to that product, we set the new name, price, 
    // and description. That data is collected in a form that we will show later. 
    // 3 We set the new product image value if a new image was uploaded. 
    // 4 Finally, we save the new product data, and we redirect to the admin.product.index route (here 
    // is where we list all products).
    
    public function update(Request $request, $id) { 
        Product::validate($request); 
 
        $product = Product::findOrFail($id);
        $product->setName($request->input('name')); 
        $product->setDescription($request->input('description')); 
        $product->setPrice($request->input('price')); 
 
        if ($request->hasFile('image')) { 
            $imageName = $product->getId().".".$request->file('image')->extension(); 
            Storage::disk('public')->put( 
                $imageName, 
                file_get_contents($request->file('image')->getRealPath()) 
            ); 
            $product->setImage($imageName); 
        } 
 
        $product->save(); 
        return redirect()->route('admin.product.index'); 
    }



}
