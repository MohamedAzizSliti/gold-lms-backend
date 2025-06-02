<?php
namespace App\Helpers;

use App\Api\ApiSmsGateway;
use App\Models\Company;
use App\Models\School;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Theme;
use App\Models\Store;
use App\Models\Coupon;
use App\Models\Review;
use App\Models\Product;
use App\Models\Setting;
use App\Enums\RoleEnum;
use App\Models\Currency;
use App\Enums\OrderEnum;
use App\Models\Category;
use App\Models\Variation;
use App\Enums\SortByEnum;
use App\Enums\StockStatus;
use App\Models\Attachment;
use App\Models\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Models\PaymentAccount;
use ClickSend\Api\AccountApi;
use ClickSend\Configuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class Helpers
{

  // Get Current User Values
  public static function isUserLogin()
  {
    return Auth::guard('api')->check();
  }

  public static function getCurrentUserId()
  {
    if (self::isUserLogin()) {
      return Auth::guard('api')->user()?->id;
    }
  }

    public static function getCurrentUser()
    {
        if (self::isUserLogin()) {
            return Auth::guard('api')->user();
         }
    }

  public static function getCurrentRoleName()
  {
    if (self::isUserLogin()) {
      return Auth::guard('api')->user()?->tokens->first()->role_type;
    }
  }

  public static function getCurrentVendorStoreId()
  {
    if (self::isUserLogin()) {
      return Auth::guard('api')->user()?->store?->id;
    }
  }

  // Attachments
  public static function createAttachment()
  {
    $attachment = new Attachment();
    $attachment->save();
    return $attachment;
  }

  public static function addMedia($model, $media, $collectionName)
  {
    return $model->addMedia($media)->toMediaCollection($collectionName);
  }

  public static function storeImage($request, $model, $collectionName)
  {
    foreach ($request as $media) {
      $attachments[] = self::addMedia($model, $media, $collectionName);
    }
    $model->forcedelete($model->id);
    return $attachments;
  }

  public static function deleteImage($model)
  {
    return $model->delete($model->id);
  }

  // Get queary base data
  public static function getSettings()
  {
    return Setting::pluck('values')->first();
  }

  public static function getAdmin()
  {
    return User::whereHas('roles', function($q) {
      $q->where('name',RoleEnum::ADMIN);
    })?->first();
  }

  public static function getAttachmentId($file_name)
  {
    return Attachment::where('file_name',$file_name)->pluck('id')->first();
  }

  public static function getRoleNameByUserId($user_id)
  {
    return User::find($user_id)?->role?->name;
  }

  public static function getCoupon($data)
  {
    return Coupon::where([['code', 'LIKE', '%'.$data.'%'],['status', true]])
	  ->orWhere('id', 'LIKE', '%'.$data.'%')
      ->with(['products', 'exclude_products'])
	  ->first();
  }

  public static function getDefaultCurrencySymbol()
  {
    $settings = self::getSettings();
    if (isset($settings['general']['default_currency'])) {
      $currency = $settings['general']['default_currency'];
      return $currency->symbol;
    }
  }

    public static function getPriceCarburant()
    {
        $settings = self::getSettings();
        if (isset($settings['general']['price_diesel'])) {
            $price_diesel = $settings['general']['price_diesel'];
            $price_essence = $settings['general']['price_essence'];
            return ['price_diesel'=>$price_diesel,'price_essence'=>$price_essence];
        }
    }

    /**
     * @return array
     */
    public static function getGlobalSpeedLimit()
    {
        $settings = self::getSettings();

        if (isset($settings['general']['speed_max'])) {
            $speed_max = $settings['general']['speed_max'];
            return $speed_max;
        }
    }

    public static function getApiKeyGoogleMap()
    {
        $settings = self::getSettings();

        if (isset($settings['google_cloud']['api_key'])) {
            $speed_max = $settings['google_cloud']['api_key'];
            return $speed_max;
        }
    }

  public static function getActiveTheme()
  {
    return Theme::where('status',true)->pluck('slug');
  }

  public static function getStoreById($store_id)
  {
    return Store::where('id', $store_id)->first();
  }

  public static function getCompanyById($company_id)
  {
    return School::where('id', $company_id)->first();
  }

  public static function getVendorIdByStoreId($store_id)
  {
    return self::getStoreById($store_id)?->vendor_id;
  }

  public static function getClientIdByCompanyId($company_id)
  {
    return self::getCompanyById($company_id)?->client_id;
  }

  public static function getStoreIdByProductId($product_id)
  {
    return Product::where('id',$product_id)->pluck('store_id')->first();
  }

  public static function getProductByStoreSlug($store_slug)
  {
    return Product::whereHas('store', function (Builder $stores) use ($store_slug) {
      $stores->where('slug',$store_slug);
    });
  }

  public static function getRelatedProductId($model, $category_id, $product_id = null)
  {
    return $model->whereRelation('categories',
      function ($categories) use ($category_id) {
        $categories->Where('category_id',$category_id);
      }
    )->whereNot('id', $product_id)->inRandomOrder()->limit(6)->pluck('id')->toArray();
  }

  public static function getDefaultCurrencyCode()
  {
    $settings = Helpers::getSettings();
    $currency_id = $settings['general']['default_currency_id'];
    return Currency::whereId($currency_id)->pluck('code')->first();
  }

  public static function getCurrencyExchangeRate($currencyCode)
  {
    return Currency::where('code', $currencyCode)?->pluck('exchange_rate')?->first();
  }

  public static function convertToINR($amount)
  {
    $exchangeRate = self::getCurrencyExchangeRate('INR') ?? 1;
    $price = $amount * $exchangeRate;
    return self::roundNumber($price);
  }

  public static function getConsumerOrderByProductId($consumer_id, $product_id)
  {
    return Order::where('consumer_id',$consumer_id)->whereHas('products', function ($products) use ($product_id) {
        $products->where('product_id',$product_id);
    });
  }

  public static function getStoreWiseLastThreeProductImages($store_id)
  {
    return Product::where('store_id',$store_id)->whereNull('deleted_at')
      ->latest()->limit(3)->with('product_thumbnail')->get()
      ->pluck('product_thumbnail.original_url')
      ->toArray();
  }

  public static function roundNumber($numb)
  {
    return number_format($numb, 2, '.', '');
  }

  public static function formatDecimal($value)
  {
    return floor($value * 100) / 100;
  }

  public static function removeCart(Order $order)
  {
    $productIds = [];
    $variationIds = [];
    $cartItems = Cart::where('consumer_id',$order->consumer_id)->get();

    if ($cartItems) {
      foreach ($order->products as $product) {
        $product = $product->pivot;
        if (isset($product->variation_id)) {
          $variationIds[] = $product->variation_id;
        }

        if (isset($product->product_id)) {
          $productIds[] = $product->product_id;
        }
      }

      $cart = Cart::where('consumer_id',self::getCurrentUserId())
        ->whereIn('product_id',$productIds);

      if (!empty($variationIds)) {
        $cart = Cart::where('consumer_id',self::getCurrentUserId())
          ->whereIn('variation_id',$variationIds);
      }

      $cart->delete();
    }
  }

  public static function getProductPrice($product_id)
  {
    return Product::where('id',$product_id)->first(['price', 'discount']);
  }

  public static function getVariationPrice($variation_id)
  {
    return Variation::where('id',$variation_id)->first(['price', 'discount']);
  }

  public static function getSalePrice($product)
  {
    $productPrices = self::getPrice($product);
    return $productPrices->price - (($productPrices->price * $productPrices->discount)/100);
  }

  public static function getSubTotal($price, $quantity)
  {
    return $price * $quantity;
  }

  public static function getTotalAmount($products)
  {
    $subtotal = [];
    foreach ($products as $product) {
      $singleProductPrice = self::getSalePrice($product);
      $subtotal[] = self::getSubTotal($singleProductPrice, $product['quantity']);
    }

    return array_sum($subtotal);
  }

  public static function getPrice($product)
  {
    if (isset($product['variation_id'])) {
      return self::getVariationPrice($product['variation_id']);
    }

    return self::getProductPrice($product['product_id']);
  }

  public static function pointIsEnable()
  {
    $settings = self::getSettings();
    return $settings['activation']['point_enable'];
  }

  public static function walletIsEnable()
  {
    $settings = self::getSettings();
    return $settings['activation']['wallet_enable'];
  }

  public static function isMultiVendorEnable()
  {
    $settings = self::getSettings();
    return $settings['activation']['multivendor'];
  }

  public static function couponIsEnable()
  {
    $settings = self::getSettings();
    return $settings['activation']['coupon_enable'];
  }

  public static function getCategoryCommissionRate($categories)
  {
    return Category::whereIn('id', $categories)->pluck('commission_rate');
  }

  public static function getOrderStatusIdByName($name)
  {
    return OrderStatus::where('name',$name)->pluck('id')->first();
  }

  public static function getPaymentAccount($user_id)
  {
    return PaymentAccount::where('user_id',$user_id)->first();
  }

  public static function getTopSellingProducts($product)
  {
    $orders_count = $product->withCount(['orders'])->get()->sum('orders_count');
    $product = $product->orderByDesc('orders_count');
    if (!$orders_count) {
      $product = (new Product)->newQuery();
      $product->whereRaw('1 = 0');
      return $product;
    }

    return $product;
  }

  public static function getTopVendors($store)
  {
    $store = $store->orderByDesc('orders_count');
    $orders_count = $store->withCount(['orders'])->get()->sum('orders_count');
    if (!$orders_count) {
      $store = (new Store)->newQuery();
      $store->whereRaw('1 = 0');
      return $store;
    }

    return $store;
  }

  public static function getVariationStock($variation_id)
  {
    return Variation::where([['id', $variation_id],['stock_status', 'in_stock'],['quantity', '>', 0], ['status', true]])->first();
  }

  public static function getProductStock($product_id)
  {
    return Product::where([['id', $product_id],['stock_status', 'in_stock'], ['quantity', '>', 0], ['status', true]])->first();
  }

  public static function getCountUsedPerConsumer($consumer, $coupon)
  {
    return Order::where([['consumer_id', $consumer],['coupon_id', $coupon]])->count();
  }

  public static function getOrderByOrderNumber($order_number)
  {
    return Order::with(config('enums.order.with'))->where('order_number',$order_number)->first();
  }

  public static function decrementProductQuantity($product_id, $quantity)
  {
    $product = Product::findOrFail($product_id);
    $product->decrement('quantity', $quantity);
    $product = $product->fresh();
    if ($product->quantity <= 0) {
      $product->quantity = 0;
      self::updateProductStockStatus($product_id, StockStatus::OUT_OF_STOCK);
    }
  }

  public static function updateProductStockStatus($id, $stock_status)
  {
    return Product::where('id',$id)->update(['stock_status' => $stock_status]);
  }

  public static function incrementProductQuantity($product_id, $quantity)
  {
    $product = Product::findOrFail($product_id);
    if ($product->stock_status == StockStatus::OUT_OF_STOCK) {
      self::updateProductStockStatus($product_id, StockStatus::IN_STOCK);
    }
    $product->increment('quantity', $quantity);
  }

  public static function updateVariationStockStatus($id, $stock_status)
  {
    return Variation::findOrFail($id)->update(['stock_status' => $stock_status]);
  }

  public static function decrementVariationQuantity($variation_id, $quantity)
  {
    $variation = Variation::findOrFail($variation_id);
    $variation->decrement('quantity', $quantity);
    $variation = $variation->fresh();
    if ($variation->quantity <= 0) {
      $variation->quantity = 0;
      self::updateVariationStockStatus($variation_id, StockStatus::OUT_OF_STOCK);
    }
  }

  public static function incrementVariationQuantity($variation_id, $quantity)
  {
    $variation = Variation::findOrFail($variation_id);
    if ($variation->stock_status == StockStatus::OUT_OF_STOCK) {
      self::updateVariationStockStatus($variation_id, StockStatus::IN_STOCK);
    }
    $variation->increment('quantity', $quantity);
  }

  public static function isAlreadyReviewed($consumer_id, $product_id)
  {
    return Review::where([
      ['consumer_id', $consumer_id],
      ['product_id', $product_id]
    ])->first();
  }

  public static function countOrderAmount($product_id, $filter_by)
  {
    return self::getCompletedOrderByProductId($product_id, $filter_by)->get()->sum('total');
  }

  public static function getStoreOrderCount($store_id, $filter_by)
  {
    return self::getCompleteOrderByStoreId($store_id, $filter_by)?->get()->count();
  }

  public static function countStoreOrderAmount($store_id, $filter_by)
  {
    return self::getCompleteOrderByStoreId($store_id, $filter_by)?->sum('total');
  }

  public static function getProductCountByStoreId($store_id, $filter_by)
  {
    return self::getProductByStoreId($store_id, $filter_by)?->count();
  }

  public static function getProductByStoreId($store_id, $filter_by)
  {
    $product = Product::where('store_id', $store_id)->whereNull('deleted_at');
    return self::getFilterBy($product, $filter_by);
  }

  public static function getCompleteOrderByStoreId($store_id, $filter_by)
  {
    $order = Order::where('store_id',$store_id)->where('payment_status',PaymentStatus::COMPLETED);
    return self::getFilterBy($order, $filter_by);
  }

  public static function getFilterBy($model, $filter_by)
  {
    switch($filter_by) {
      case SortByEnum::TODAY:
        $model = $model->where('created_at', Carbon::now());
        break;

      case SortByEnum::LAST_WEEK:
        $startWeek = Carbon::now()->subWeek()->startOfWeek();
        $endWeek = Carbon::now()->subWeek()->endOfWeek();
        $model = $model->whereBetween('created_at', [$startWeek, $endWeek]);
        break;

      case SortByEnum::LAST_MONTH:
        $model = $model->whereMonth('created_at', Carbon::now()->subMonth()->month);
        break;

      case SortByEnum::THIS_YEAR:
        $model = $model->whereYear('created_at', Carbon::now()->year);
        break;
    }

    return $model;
  }

  public static function getCompletedOrderByProductId($product_id, $filter_by)
  {
    $order = Order::whereHas('products', function ($query) use($product_id) {
      $query->where('product_id',$product_id);
    })->whereNull('deleted_at')->where('payment_status',PaymentStatus::COMPLETED);

    return self::getFilterBy($order, $filter_by);
  }

  public static function getOrderCount($product_id, $filter_by)
  {
    return self::getCompletedOrderByProductId($product_id, $filter_by)?->count();
  }

  public static function isOrderCompleted($order)
  {
    if ($order->payment_status == PaymentStatus::COMPLETED &&
      $order->order_status->name == OrderEnum::DELIVERED) {
      return true;
    }

   return false;
  }

  public static function user_review($consumer_id, $product_id)
  {
    return Review::where('consumer_id',$consumer_id)
      ->where('product_id',$product_id)->whereNull('deleted_at')->first();
  }

  public static function canReview($consumer_id, $product_id)
  {
    $orders = self::getConsumerOrderByProductId($consumer_id, $product_id);
    foreach($orders as $order) {
      if (isset($order->sub_orders)) {
        if (!$order->sub_orders->isEmpty()) {
          $tempOrder = null;
          foreach($order->sub_orders as $sub_order) {
            foreach($sub_order->products as $product) {
              if ($product->id == $product_id) {
                $tempOrder = $sub_order;
              }
            }
          }

          $order = $tempOrder;
        }
      }

      if ($order) {
        if (self::isOrderCompleted($order)) {
          return true;
        }
      }
    }

    return false;
  }

  public static function getReviewRatings($product_id)
  {
    $review = Review::where('product_id', $product_id)->get();
    return [
      $review->where('rating', 1)->count(),
      $review->where('rating', 2)->count(),
      $review->where('rating', 3)->count(),
      $review->where('rating', 4)->count(),
      $review->where('rating', 5)->count(),
    ];
  }

  public static function updateProductStock(Order $order)
  {
    if ($order->payment_status == PaymentStatus::COMPLETED ||
      $order->payment_method == PaymentMethod::COD) {
      foreach ($order->products as $product) {
        $product = $product->pivot;
        if (isset($product->variation_id)) {
          self::decrementVariationQuantity($product->variation_id, $product->quantity);
        } else {
          self::decrementProductQuantity($product->product_id, $product->quantity);
        }
      }
    }
  }

  public static function traccar_call($service,$data,$method=false,$debug = false){

        $credentials = base64_encode(Auth::guard('api')->user()?->email.':'.Auth::guard('api')->user()?->password_traccar);
        $path = env('TRACCAR_URL').'/'.$service;
        Log::info($path);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($method) {
            curl_setopt($ch, CURLOPT_URL, $path);
            if($data) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            if($method == 'PUT'){
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }
            if($method == 'DELETE'){
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            }
        }
        $headers = array(
            'Content-Type:application/json',
            'Authorization: Basic '. $credentials
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
    if ($debug)
    die($path);
      // Get HTTP response status code
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      // Check if the cURL request failed
      if (!$result) {
          $error = curl_error($ch);
          curl_close($ch);
          Log::error($error);
        //  die("cURL error: $error");
      }

      curl_close($ch);
      // Handle the HTTP response
//      if ($httpCode >= 200 && $httpCode < 300) {
//          echo "User added successfully:\n";
//          echo $result; // This will output the JSON response from the server
//      } elseif ($httpCode == 400) {
//          echo "Bad Request (400): The server could not understand the request due to invalid syntax.\n";
//          echo "Response: $result\n";
//      } elseif ($httpCode >= 400 && $httpCode < 500) {
//          echo "Client error occurred. HTTP Status Code: $httpCode\n";
//          echo "Response: $result\n";
//      } elseif ($httpCode >= 500 && $httpCode < 600) {
//          echo "Server error occurred. HTTP Status Code: $httpCode\n";
//          echo "Response: $result\n";
//      } else {
//          echo "Unexpected HTTP response code: $httpCode\n";
//          echo "Response: $result\n";
//      }
         Log::info($result);
        return json_decode($result);
    }

    public static function convertToISO8601($dateStr)
    {
        // Expression régulière pour vérifier le format ISO 8601
        $iso8601Regex = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})?$/';

        if ( preg_match($iso8601Regex, $dateStr) === 1){
            return $dateStr;
        }else{
            try {
                // Tenter de parser la date avec Carbon
                $date = Carbon::parse($dateStr);

                // Retourner la date en format ISO 8601
                return $date->toISOString(); // Résultat : YYYY-MM-DDTHH:mm:ss+00:00
            } catch (\Exception $e) {
                // Retourner une erreur si la date est invalide
                return "Date invalide ou format non reconnu.";
            }
        }
    }
    public static function generateRandomColor()
    {
        return '#' . substr(str_shuffle('0123456789ABCDEF'), 0, 6);
    }

    public static function isFromAdmin(){
        return request()->header('Fromadmin') ? true : false;
   }


    /**
     * @param $numbers
     * @param $from
     * @param $message
     */
    public static function sendSms($numbers, $message, $from = '')
    {

        // Array of mobile phone numbers (starting with the "+" sign and country code):
        $recipients = ['+21692702009'];

        if (env('SMS_GATEWAY') == 'twilio') {
            $account_sid = env('ACCOUNT_SID');
            $auth_token = env('AUTH_TOKEN');
            $twilio_phone_number = env('Twilio_Number');
            try {
              //  $client = new Client($account_sid, $auth_token);

//                foreach ($numbers as $number) {
//                    $client->messages->create(
//                        $number,
//                        array(
//                            "from" => $twilio_phone_number,
//                            "body" => $message
//                        )
//                    );
//                }
            } catch (\Exception $e) {
                //   dd($e);
            }
            $message = "SMS sent successfully";
        }
        if (env('SMS_GATEWAY') == 'SMS_FACTOR') {
            $account = app('SMSFactor\Message');
            try {
                $response = $account->send([
                    'to' => $numbers,
                    'text' => $message
                ]);
            } catch (\Exception $e) {
                $reponse['code'] = 0;
                $reponse['message'] = $e->getMessage();
            }

            //   print_r($response->getJson());
        }


        // Click Send
        if (env('SMS_GATEWAY') == 'clickSend') {
            // https://github.com/ClickSend/clicksend-php?utm_source=https://integrations.clicksend.com&utm_campaign=integrations&utm_content=&utm_medium=laravel

//            // Configure HTTP basic authorization: BasicAuth
//            $config = Configuration::getDefaultConfiguration()
//                ->setUsername(env('clicksend_api_username'))
//                ->setPassword(env('clicksend_api_password'));
//
//            $apiInstance = new  AccountApi(
//            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
//            // This is optional, `GuzzleHttp\Client` will be used as default.
//                new \GuzzleHttp\Client(),
//                $config
//            );
//
//
//            try {
//                $result = $apiInstance->accountGet();
//                print_r($result);
//            } catch (\Exception $e) {
//                echo 'Exception when calling AccountApi->accountGet: ', $e->getMessage(), PHP_EOL;
//            }
        }

        // Click Send
        if (env('SMS_GATEWAY') == 'clickatell') {
//            try {
//                $clickatell = new \Clickatell\Rest(env('CLICKATELL_API_KEY'));
//                foreach ($numbers as $number) {
//                    $result = $clickatell->sendMessage(['to' => [$number], 'content' => $message]);
//                }
//            } catch (ClickatellException $e) {
//                return redirect()->back()->with('not_permitted', 'Please setup your <a href="sms_setting">SMS Setting</a> to send SMS.');
//            }
//            $message = "SMS sent successfully";
        }

        // Click Send
        // zender not used for now i used another sms gateway
        if (env('SMS_GATEWAY') == 'zender') {
            try {
                $apiSmsGateway = new ApiSmsGateway();
                foreach ($numbers as $number) {
                    // Send a message using the SIM in slo t 1 of Device ID 1 (Represented as "1|0").
                    // SIM slot is an index so the index of the first SIM is 0 and the index of the second SIM is 1.
                    // In this example, 1 represents Device ID and 0 represents SIM slot index.
                    $apiSmsGateway->sendSingleMessage($number, $message, 3 | 2);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            $message = "SMS sent successfully";
        }
    }


}
