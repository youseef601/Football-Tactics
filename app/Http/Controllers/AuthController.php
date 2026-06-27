<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Admin;
use App\Models\Product;
use App\Models\AdminWork;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\coupons;
use App\Models\Category;
use App\Models\Leagues;
use App\Models\codes;
use App\Models\BillingData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;  // Correct import for HTTP requests

class AuthController extends Controller
{



    // Method for user signup
    public function signup(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // If validation fails, return with errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new user in the database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a token for the user
        $token = $user->createToken('API_TOKEN')->plainTextToken;

        // Return success response with the token
        return response()->json([
            'message' => 'Account created successfully!',
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    // Method for user login
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the login data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // If validation fails, return with errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and the password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Generate token for the user
        $token = $user->createToken('API_TOKEN')->plainTextToken;

        // Return success response with token
        return response()->json([
            'message' => 'Login successful!',
            'user' => $user,
            'token' => $token,
        ], 200);
    }



// Method for adding a product
    public function addProduct(Request $request): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add product.',
            ], 403);
        }
        // Validate input, including the 'imgs' field for images
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer', // Ensure price is required
            'discount' => 'required|integer|min:0|max:100', // Ensure discount is required
            'final_price' => 'required|integer', // Ensure final price is required
            'size' => 'required|string|max:50', // Ensure size is required
            'stock' => 'required|integer', // Ensure stock is required
            'status' => 'required|string|max:50',
            'category_id' => 'nullable|integer|exists:categories,id',
            'leagues_id' => 'nullable|integer|exists:leagues,id', // Validate leagues_id
            'imgs' => 'nullable|array|max:4', // Ensure the array has a maximum of 4 images
            'imgs.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Ensure each image is valid
        ]);

        // Get the authenticated user (admin)
        $admin = Auth::user();

        // Find the Admin associated with the user
        $adminDetails = Admin::where('admin_id', $admin->id)->first();

        if (!$adminDetails) {
            return response()->json([
                'status' => false,
                'message' => 'User is not associated with any Admin record.',
            ], 403);
        }

        // Check if the product already exists (ignore size)
        $existingProduct = Product::where('name', $request->name)->first();

        if ($existingProduct) {
            // If it exists, use the existing price
            $price = $existingProduct->price;
        } else {
            // If not, use the provided price
            $price = $request->price;
        }

        // Handle image uploads
        $imagePaths = [];
        if ($request->has('imgs')) {
            foreach ($request->file('imgs') as $image) {
                $path = $image->store('product_images', 'public'); // Store image in 'public/product_images'
                $imagePaths[] = $path;
            }
        }

        // Create the product for the Admin
        $product = Product::create([
            'name' => $request->name,
            'admin_id' => $adminDetails->id,
            'price' => $price, // Use the determined price
            'discount' => $request->discount, // Store discount
            'final_price' => $request->final_price, // Store final price
            'size' => $request->size,
            'stock' => $request->stock,
            'status' => $request->status,
            'category_id' => $request->category_id,
            'leagues_id' => $request->leagues_id, // Include leagues_id in product creation
            'imgs' => json_encode($imagePaths), // Save the image paths as JSON
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product added successfully!',
            'product' => $product,
        ], 201);
    }


    // Method for adding an Admin with an existing user
    public function addAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        // Get the authenticated user (who is trying to add another admin)
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add another admin.',
            ], 403);
        }

        // Validate the input for adding a new admin
        $request->validate([
            'admin_id' => 'required|exists:users,id|unique:admins,admin_id', // Ensure the user exists and is unique in the admins table
            'admin_name' => 'required|string|max:255',
        ]);

        // Create the Admin and associate it with the specified user
        $admin = Admin::create([
            'admin_id' => $request->admin_id, // Use the provided user ID
            'admin_name' => $request->admin_name, // Name for the new admin
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Admin added successfully!',
            'admin' => $admin,
        ], 201);
    }


    // Method for showProducts
    public function showProducts(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate input
        $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id', // Validate each ID in the array
            'min_price' => 'nullable|integer|min:0', // Minimum price must be a non-negative integer
            'max_price' => 'nullable|integer|min:0', // Maximum price must be a non-negative integer
        ]);

        // Start building the query for the Product model
        $query = Product::query();

        // Check if category_ids are provided and filter products accordingly
        if ($request->has('category_ids') && !empty($request->category_ids)) {
            $query->whereIn('category_id', $request->category_ids); // Filter by category
        }

        // Check for min_price and max_price and apply filters if provided
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price); // Filter products with price greater than or equal to min_price
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price); // Filter products with price less than or equal to max_price
        }

        // Use aggregate functions and group by name to avoid duplicates
        $products = $query->selectRaw('name, MAX(id) as id, MAX(price) as price, MAX(discount) as discount, MAX(final_price) as final_price, MAX(category_id) as category_id, MAX(admin_id) as admin_id, MAX(imgs) as imgs, MAX(leagues_id) as leagues_id')
            ->groupBy('name')
            ->orderBy('name')
            ->get();


        // Format the products to include the first image from the 'imgs' field
        $products = $products->map(function ($product) {
            // Decode the imgs field assuming it's stored as a JSON array of URLs
            $images = json_decode($product->imgs, true);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'discount' => $product->discount, // Include discount
                'final_price' => $product->final_price, // Include final price
                'first_image' => $images && count($images) > 0 ? $images[0] : null,
                'category_id' => $product->category_id,
                'leagues_id' => $product->leagues_id,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully!',
            'products' => $products,
        ], 200);
    }


    // Method for productDetails
    public function productDetails($productId): \Illuminate\Http\JsonResponse
    {
        // Find the product by its ID and join with the categories table to get the category name
        $product = Product::with('category') // Load the category relationship
        ->where('id', $productId)
            ->first();

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found!',
            ], 404);
        }

        // Prepare product details
        $productDetails = [
            'product_name' => $product->name,
            'availability' => $product->stock > 0 ? 'Available' : 'Out of stock',
            'price' => $product->price,
            'discount' => $product->discount, // Include discount
            'final_price' => $product->final_price, // Include final price
            'size' => $product->size,
            'quantity' => $product->stock,
            'category_name' => $product->category ? $product->category->cat_name : 'No category',
            'images' => $product->imgs,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Product details retrieved successfully!',
            'product' => $productDetails,
        ], 200);
    }



// Method for getProductSizeAndQuantity
    public function getProductSizeAndQuantity(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the input to ensure the product name and size are provided
        $request->validate([
            'product_name' => 'required|string|max:255',
            'size' => 'required|string|max:50',
        ]);

        // Find the product by name and size
        $product = Product::where('name', $request->product_name)
            ->where('size', $request->size)
            ->first();

        // Check if the product exists
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Return the product size and quantity (stock)
        return response()->json([
            'status' => true,
            'message' => 'Product details retrieved successfully!',
            'product' => [
                'name' => $product->name,
                'size' => $product->size,
                'quantity' => $product->stock,
            ],
        ], 200);
    }



    public function relatedProducts($id): \Illuminate\Http\JsonResponse
    {
        // Validate the product ID directly from the URL parameter
        $validatedData = Validator::make(['product_id' => $id], [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validatedData->errors()->first(),
            ], 400);
        }

        // Fetch the product to get its category_id
        $product = Product::findOrFail($id);

        // Fetch at most 4 related products in the same category, excluding the current product
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->select('id', 'name', 'price', 'discount', 'final_price', 'imgs')
            ->limit(4)
            ->get();

        // Include first image for related products
        $relatedProducts = $relatedProducts->map(function ($relatedProduct) {
            $images = json_decode($relatedProduct->imgs, true);
            return [
                'id' => $relatedProduct->id,
                'name' => $relatedProduct->name,
                'price' => $relatedProduct->price,
                'discount' => $relatedProduct->discount, // Include discount
                'final_price' => $relatedProduct->final_price, // Include final price
                'first_image' => $images && count($images) > 0 ? $images[0] : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Related products retrieved successfully!',
            'related_products' => $relatedProducts,
        ], 200);
    }


// Method for updating slides in the homepage swiper
    public function addSlide(Request $request): \Illuminate\Http\JsonResponse
    {
        // Check if the authenticated user is an admin
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add a slide!',
            ], 403);
        }

        try {
            // Validate the input for slide images
            $request->validate([
                'slide1' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Validate slide1 image
                'slide2' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Validate slide2 image
                'slide3' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Validate slide3 image
                'text1'  => 'nullable|string|max:255',
                'text2'  => 'nullable|string|max:255',
                'text3'  => 'nullable|string|max:255',
            ]);

            // Get the authenticated admin
            $admin = Auth::user();

            // Find the existing AdminWork record for the admin
            $adminWork = AdminWork::where('admin_id', $admin->id)->first();

            if (!$adminWork) {
                return response()->json([
                    'status' => false,
                    'message' => 'No slides found for this admin.',
                ], 404);
            }

            // Handle image uploads and update the existing record
            if ($request->hasFile('slide1')) {
                $adminWork->slide1 = $request->file('slide1')->store('slides', 'public');
            }
            if ($request->hasFile('slide2')) {
                $adminWork->slide2 = $request->file('slide2')->store('slides', 'public');
            }
            if ($request->hasFile('slide3')) {
                $adminWork->slide3 = $request->file('slide3')->store('slides', 'public');
            }

            $adminWork->text1 = $request->text1;
            $adminWork->text2 = $request->text2;
            $adminWork->text3 = $request->text3;


            // Save the updated AdminWork record to the database
            $adminWork->save();

            return response()->json([
                'status' => true,
                'message' => 'Slides updated successfully!',
                'admin_work' => $adminWork,
            ], 200);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error updating slides: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error updating slides. Please check the logs.',
            ], 500);
        }
    }



    public function getProductDataFromAdminWork(AdminWork $adminWork): \Illuminate\Support\Collection
    {
        // Fetch the product IDs from the adminWork instance
        $productIds = [
            $adminWork->best1,
            $adminWork->best2,
            $adminWork->best3,
            $adminWork->new1,
            $adminWork->new2,
            $adminWork->new3,
        ];

        // Filter out null values to avoid unnecessary queries
        $productIds = array_filter($productIds);

        // Retrieve products that match the IDs
        return Product::whereIn('id', $productIds)->get()->map(function ($product) {
            // Decode the JSON string if imgs is stored as a JSON string in the database
            $imageArray = json_decode($product->imgs, true);

            // Get the first image or set to null if not available
            $firstImage = !empty($imageArray) ? $imageArray[0] : null;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'size' => $product->size,
                'imgs' => $firstImage, // Change to return just the first image without brackets
                'discount' => $product->discount,
                'final_price' => $product->final_price,
            ];
        });
    }



    public function showAllUsers(Request $request): \Illuminate\Http\JsonResponse
    {
        // Check if the authenticated user is an admin
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to show users.',
            ], 403);
        }


        // Retrieve all users except for the password
        $users = User::select('id', 'name', 'email', 'created_at', 'updated_at')->get();

        return response()->json($users);
    }



    // Method to show admin work data
    public function showAdminWork(): \Illuminate\Http\JsonResponse
    {
        // Retrieve all records from the admin_work table
        $adminWorks = AdminWork::all();

        $adminWorksData = $adminWorks->map(function ($adminWork) {
            // Fetch the associated product data
            $products = $this->getProductDataFromAdminWork($adminWork);

            return [
                'id' => $adminWork->id,
                'admin_id' => $adminWork->admin_id,
                'slide1' => $adminWork->slide1,
                'slide2' => $adminWork->slide2,
                'slide3' => $adminWork->slide3,
                'text1' => $adminWork->text1,
                'text2' => $adminWork->text2,
                'text3' => $adminWork->text3,
                'best1' => $adminWork->best1,
                'best2' => $adminWork->best2,
                'best3' => $adminWork->best3,
                'best4' => $adminWork->best4,
                'new1' => $adminWork->new1,
                'new2' => $adminWork->new2,
                'new3' => $adminWork->new3,
                'new4' => $adminWork->new4,
                'products' => $products, // Use the products directly
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Admin work data retrieved successfully!',
            'admin_work' => $adminWorksData,
        ], 200);
    }



    public function addOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request data
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Begin a database transaction
        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create([
                'user_id' => $validatedData['user_id'],
                'status' => 'pending', // Set default status
                'total_price' => 0, // Initialize total price
                'currency' => 'USD', // Set your desired currency or retrieve from the request
            ]);

            // Initialize an array to hold the order items
            $orderItems = [];
            $totalPrice = 0; // Variable to calculate total order price

            // Loop through the items and create order items
            foreach ($validatedData['items'] as $item) {
                // Retrieve the product
                $product = Product::findOrFail($item['product_id']);

                // Check if enough stock is available
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'status' => false,
                        'message' => "Not enough stock for product ID {$product->id}.",
                    ], 400);
                }

                // Calculate the price at purchase and total price
                $priceAtPurchase = $product->final_price;
                $quantity = $item['quantity'];
                $itemTotalPrice = $priceAtPurchase * $quantity;

                // Prepare order item data
                $orderItems[] = [
                    'order_id' => $order->id, // Reference the new order ID
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'price_at_purchase' => $priceAtPurchase, // Store the price at purchase
                ];

                // Decrease the product stock
                $product->stock -= $quantity;
                $product->save();

                // Add to total price
                $totalPrice += $itemTotalPrice;
            }

            // Update the order's total price
            $order->total_price = $totalPrice;
            $order->save();

            // Bulk insert the order items
            OrderItem::insert($orderItems);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully!',
                'order_id' => $order->id,
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function showOrder($id): \Illuminate\Http\JsonResponse
    {
        Log::info("Fetching order with ID: {$id}");

        // Fetch the order with associated user and order items
        $order = Order::with(['user', 'items.product'])->find($id);

        if (!$order) {
            Log::warning("Order with ID {$id} not found");
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        // Prepare response data
        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully!',
            'order_id' => $order->id,
            'total_price' => $order->total_price,
            'user' => [                        // Include user information
                'id' => $order->user->id,     // User ID
                'name' => $order->user->name, // User name
                'email' => $order->user->email, // User email (if needed)
            ],
            'status' => $order->status,       // Order status
            'created_at' => $order->created_at, // Order creation date
            'updated_at' => $order->updated_at, // Order update date
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'size' => $item->product->size,
                        'imgs' => $item->product->imgs,
                        'discount' => $item->product->discount, // Include discount
                        'final_price' => $item->product->final_price, // Include final price
                    ],
                ];
            }),
        ], 200);
    }



    public function showOrderAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        // Check if the authenticated user is an admin
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to show users.',
            ], 403);
        }


        // Retrieve all orders with user and order item details
        $orders = Order::with(['user', 'items.product'])->get();

        // Prepare the response data
        $response = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'size' => $item->product->size,
                            'imgs' => $item->product->imgs,
                            'discount' => $item->product->discount,
                            'final_price' => $item->product->final_price,
                        ],
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Orders retrieved successfully!',
            'orders' => $response,
        ], 200);
    }



    public function updateAdminWorkWithProducts(Request $request, $adminWorkId): \Illuminate\Http\JsonResponse
    {
        // Check if the authenticated user is an admin
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add admin work.',
            ], 403);
        }
        // Validate incoming request
        $validated = $request->validate([
            'best1' => 'nullable|exists:products,id',
            'best2' => 'nullable|exists:products,id',
            'best3' => 'nullable|exists:products,id',
            'best4' => 'nullable|exists:products,id',
            'new1'  => 'nullable|exists:products,id',
            'new2'  => 'nullable|exists:products,id',
            'new3'  => 'nullable|exists:products,id',
            'new4'  => 'nullable|exists:products,id',
        ]);

        // Find the admin_work record
        $adminWork = AdminWork::findOrFail($adminWorkId);

        // Update only the fields provided in the request
        foreach ($validated as $field => $value) {
            if ($value !== null) {
                $adminWork->$field = $value;
            }
        }

        // Save the changes
        $adminWork->save();

        return response()->json([
            'message' => 'Admin work updated successfully',
            'data' => $adminWork
        ]);
    }



    public function storeBillingData(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'apartment' => 'nullable|string',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'street' => 'required|string',
                'building' => 'nullable|string',
                'phone_number' => 'required|string|max:20',
                'city' => 'required|string',
                'country' => 'required|string|max:3',
                'email' => 'required|email', // Allow email to repeat
                'floor' => 'nullable|string',
                'total_price' => 'required|numeric|min:0',
                'currency' => 'required|string|max:3',
                'payment_methods' => 'required|string', // Expect an array of payment methods
                'order_id' => 'required|exists:orders,id|unique:billing_data,order_id', // Validate order_id as unique
                'user_id' => 'required|exists:users,id', // Validate user_id
            ]);

            // Create a new billing data record in the database
            $billingData = BillingData::create([
                'apartment' => $validatedData['apartment'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'street' => $validatedData['street'],
                'building' => $validatedData['building'],
                'phone_number' => $validatedData['phone_number'],
                'city' => $validatedData['city'],
                'country' => $validatedData['country'],
                'email' => $validatedData['email'],
                'floor' => $validatedData['floor'],
                'total_price' => $validatedData['total_price'],
                'currency' => $validatedData['currency'],
                'payment_methods' => $validatedData['payment_methods'],
                'order_id' => $validatedData['order_id'], // Store order_id
                'user_id' => $validatedData['user_id'], // Store user_id
            ]);

            // Return a response
            return response()->json([
                'message' => 'Billing data stored successfully',
                'data' => $billingData
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Check for specific unique constraint violation
            if ($e->validator->errors()->has('order_id')) {
                return response()->json([
                    'message' => 'The order ID has already been used for another billing record.',
                    'errors' => $e->validator->errors()
                ], 422);
            }

            // Return other validation errors
            return response()->json([
                'message' => 'Validation errors occurred',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'message' => 'An error occurred while storing billing data',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function addCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add a code.',
            ], 403);
        }

        // Validate the request data
        $request->validate([
            'code' => 'required|string',
        ]);

        // Check if the code already exists
        if (Codes::where('code', $request->code)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This code already exists. Please enter a unique code.',
            ], 422);
        }

        // Validate the request data
        $request->validate([
            'code' => 'required|string|unique:codes,code',
        ]);

        // Add code without setting the user_id
        $code = Codes::create([
            'admin_id' => $currentUser->id,
            'code' => $request->code,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Code added successfully.',
            'code' => $code,
        ]);
    }



    public function checkCode(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate that the code field is present in the request
        $request->validate([
            'code' => 'required|string',
        ]);

        // Retrieve the code from the request
        $inputCode = $request->code;

        // Find the code entry in the database
        $codeEntry = Codes::where('code', $inputCode)->first();

        if ($codeEntry) {
            // Check if user_id has already been set
            if ($codeEntry->user_id === null) {
                // If not, update the user_id to the current user's ID
                $currentUser = Auth::user();
                $codeEntry->update(['user_id' => $currentUser->id]);

                return response()->json([
                    'status' => true,
                    'message' => 'Code found and user_id updated.',
                ]);
            } else {
                // If user_id is already set, indicate the code has already been used
                return response()->json([
                    'status' => false,
                    'message' => 'Code has already been used.',
                ], 400);
            }
        } else {
            // If the code does not exist, return an error response
            return response()->json([
                'status' => false,
                'message' => 'Code not found. Please enter a valid code.',
            ], 404);
        }
    }



    public function deleteProduct($id): \Illuminate\Http\JsonResponse
    {
        // Find the product by ID
        $product = Product::find($id);

        // Check if product exists
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Check if the authenticated user is the admin who created this product
        if ($product->admin_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'You do not have permission to delete this product.',
            ], 403);
        }

        // Delete the product
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }



    public function addCoupon(Request $request): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add a coupon.',
            ], 403);
        }

        // Validate the request
        $request->validate([
            'coupon' => 'required|string',
            'discount' => 'required', // Validation rule for the discount can vary based on requirements
        ]);

        // Check if the coupon already exists
        $existingCoupon = Coupons::where('coupon', $request->input('coupon'))->first();

        if ($existingCoupon) {
            return response()->json([
                'status' => false,
                'message' => 'This coupon code already exists.',
            ], 409); // Conflict status code
        }

        // Add the coupon with admin_id from the current admin
        Coupons::create([
            'coupon' => $request->input('coupon'),
            'discount' => $request->input('discount'),
            'admin_id' => $currentUser->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Coupon added successfully.',
        ], 201);
    }


    // Function for users to check if a coupon exists and retrieve discount
    public function checkCoupon(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request
        $request->validate([
            'coupon' => 'required|string',
            'order_id' => 'required|exists:orders,id', // Validate that the order_id exists
        ]);

        // Check if the coupon exists
        $coupon = Coupons::where('coupon', $request->coupon)->first();

        if ($coupon) {
            // Check if the coupon has already been used
            if ($coupon->used) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon has already been used.',
                ], 400);
            }

            // Retrieve the order
            $order = Order::findOrFail($request->order_id);

            // Calculate the discount amount
            $discountAmount = 0;

            // Handle both numeric discount and text description
            if (is_numeric($coupon->discount)) {
                $discountAmount = ($order->total_price * ($coupon->discount / 100));
            } else {
                // If discount is a text description, handle logic for such cases
                $discountAmount = 0; // Adjust this based on your business logic
            }

            // Update the order's total price
            $newTotalPrice = max(0, $order->total_price - $discountAmount); // Ensure total price doesn't go negative
            $order->total_price = $newTotalPrice;
            $order->save();

            // Mark the coupon as used
            $coupon->used = true;
            $coupon->save();

            return response()->json([
                'status' => true,
                'message' => 'Coupon is valid and applied.',
                'discount' => $coupon->discount,
                'new_total_price' => $newTotalPrice,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Coupon not found or is invalid.',
            ], 404);
        }
    }



    public function showCodes(): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add a coupon.',
            ], 403);
        }

        // Fetch the data from the codes table
        $codes = codes::all(); // or use any appropriate query

        return response()->json([
            'status' => true,
            'data' => $codes,
        ]);
    }



    public function showCoupons(): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();

        // Check if the authenticated user is an admin
        $currentAdmin = Admin::where('admin_id', $currentUser->id)->first();

        if (!$currentAdmin) {
            // If the user is not an admin, return an error response
            return response()->json([
                'status' => false,
                'message' => 'You must be an admin to add a coupon.',
            ], 403);
        }

        // Fetch all coupons from the coupons table
        $coupons = Coupons::all(); // You can also use pagination or filters if needed


        $couponsData = $coupons->map(function ($coupon) {
            return [
                'id' => $coupon->id,
                'coupon' => $coupon->coupon,
                'discount' => $coupon->discount,
                'admin_id' => $coupon->admin_id,
                'used' => $coupon->used ? true : false, // Ensure it returns a boolean
                'created_at' => $coupon->created_at,
                'updated_at' => $coupon->updated_at,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $couponsData,
        ]);
    }



    public function getLastOrderWithItems($userId)
    {
        $lastOrder = Order::where('user_id', $userId)->latest('id')->with('items.product')->first();

        if ($lastOrder) {
            return response()->json([
                'status' => true,
                'last_order' => [
                    'order_id' => $lastOrder->id,
                    'status' => $lastOrder->status,
                    'total_price' => $lastOrder->total_price,
                    'currency' => $lastOrder->currency,
                    'created_at' => $lastOrder->created_at,
                    'items' => $lastOrder->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'price_at_purchase' => $item->price_at_purchase,
                            'product_name' => $item->product->name ?? null, // Assuming `name` exists in Product
                            'product_image' => $item->product->image ?? null, // Assuming `image` exists in Product
                        ];
                    }),
                ],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No orders found for this user.',
        ]);
    }}
