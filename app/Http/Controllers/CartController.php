<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order; 
use App\Models\Item; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class CartController extends Controller
{
    public function index(Request $request)
    {
        $total = 0;
        $productsInCart = [];

        $productsInSession = $request->session()->get("products");
        if ($productsInSession) {
            $productsInCart = Product::findMany(array_keys($productsInSession));
            $total = Product::sumPricesByQuantities($productsInCart, $productsInSession);  // --> WHY IS THIS FUNCTION IN MODELS???
        }

        $viewData = [];
        $viewData["title"] = "Cart - Online Store";
        $viewData["subtitle"] =  "Shopping Cart";
        $viewData["total"] = $total;
        $viewData["products"] = $productsInCart;
        return view('cart.index')->with("viewData", $viewData);
    }

    // The index method defines a total variable with a zero value and an empty productsInCart array. First, 
    // we check if the current request has products stored in session. If there are productsInSession, we 
    // extract the related products from the database. In this case, we use the model findMany method, 
    // which receives an array with primary keys and returns a collection of objects. We send 
    // array_keys($productsInSession) to this method, remember we store the products id as keys and the 
    // quantities as values. Then, we update the total value by invoking the Product::sumPricesByQuantities 
    // method (which will be implemented next). Finally, we send the total and products to the cart.index 
    // view. 


    public function add(Request $request, $id)
    {
        $products = $request->session()->get("products");
        $products[$id] = $request->input('quantity');
        $request->session()->put('products', $products);

        return redirect()->route('cart.index');
    }

    //  The add method receives the request (which receives the quantity of product) and the product id 
    // (the id of the product to be added to the cart). Then, we get the products stored in the session 
    // through the request->session()->get("products") method. The first time, request->session()
    // >get("products") won’t exist, so we assign it to an empty object. Next, we include in products variable 
    // the collected product id with its quantity (id as key, quantity as value). We then update the products 
    // stored in the session (with the use of the request->session()->put method). Finally, we redirect the 
    // user to the cart.index route. 

    public function delete(Request $request)
    {
        $request->session()->forget('products');
        return back();
    }

    // The delete method receives the request and removes the products stored in the session for that 
    // request (using the request->session()->forget method). Then, we return to the previous route.



    public function purchase(Request $request) 
    { 
        $productsInSession = $request->session()->get("products"); 
        if ($productsInSession) { 
            $userId = Auth::user()->getId(); 
            $order = new Order(); 
            $order->setUserId($userId); 
            $order->setTotal(0); 
            $order->save(); 
        
            $total = 0; 
            $productsInCart = Product::findMany(array_keys($productsInSession)); 
            foreach ($productsInCart as $product) { 
                $quantity = $productsInSession[$product->getId()]; 
                $item = new Item(); 
                $item->setQuantity($quantity); 
                $item->setPrice($product->getPrice()); 
                $item->setProductId($product->getId()); 
                $item->setOrderId($order->getId()); 
                $item->save(); 
                $total = $total + ($product->getPrice()*$quantity); 
            } 
        $order->setTotal($total); 
        $order->save(); 


        $newBalance = Auth::user()->getBalance() - $total; 
        Auth::user()->setBalance($newBalance); 
        Auth::user()->save(); 
 
        $request->session()->forget('products'); 
 
        $viewData = []; 
        $viewData["title"] = "Purchase - Online Store"; 
        $viewData["subtitle"] = "Purchase Status"; 
        $viewData["order"] = $order; 
        return view('cart.purchase')->with("viewData", $viewData); 
    } else { 
        return redirect()->route('cart.index'); 
        }
    }
}

