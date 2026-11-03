# جرد مسارات SolarHub API

إجمالي المسارات الفعلية: 136

> الحالات تميّز صراحة بين طلب HTTP، اختبار Laravel، وفحص الكود فقط.

| # | HTTP | المسار | المصادقة/الدور/الصلاحية/الملكية | حالة التنفيذ | المصدر |
|---:|---|---|---|---|---|
| 1 | GET | `/api/admin/engineers/pending` | Authenticate:sanctum; PermissionMiddleware:engineers.view-pending | اختبار Laravel Feature | `App\Http\Controllers\Auth\Admin\AdminApprovalController@pendingEngineers` |
| 2 | PATCH | `/api/admin/engineers/{engineer}/approve` | Authenticate:sanctum; PermissionMiddleware:engineers.approve | اختبار Laravel Feature | `App\Http\Controllers\Auth\Admin\AdminApprovalController@approveEngineer` |
| 3 | PATCH | `/api/admin/engineers/{engineer}/reject` | Authenticate:sanctum; PermissionMiddleware:engineers.reject | اختبار Laravel Feature | `App\Http\Controllers\Auth\Admin\AdminApprovalController@rejectEngineer` |
| 4 | GET | `/api/admin/stores/pending` | Authenticate:sanctum; PermissionMiddleware:stores.view-pending | اختبار Laravel Feature | `App\Http\Controllers\Auth\Admin\AdminApprovalController@pendingStores` |
| 5 | PATCH | `/api/admin/stores/{store}/approve` | Authenticate:sanctum; PermissionMiddleware:stores.approve | اختبار Laravel Feature | `App\Http\Controllers\Auth\Admin\AdminApprovalController@approveStore` |
| 6 | PATCH | `/api/admin/stores/{store}/reject` | Authenticate:sanctum; PermissionMiddleware:stores.reject | اختبار Laravel Feature | `App\Http\Controllers\Auth\Admin\AdminApprovalController@rejectStore` |
| 7 | POST | `/api/auth/forgot-password` | RedirectIfAuthenticated; ThrottleRequests:forgot-password | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Auth\PasswordController@forgotPassword` |
| 8 | POST | `/api/auth/login` | RedirectIfAuthenticated; ThrottleRequests:login | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Auth\AuthenticationController@login` |
| 9 | POST | `/api/auth/logout` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Auth\AuthenticationController@logout` |
| 10 | GET | `/api/auth/me` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Auth\AuthenticationController@me` |
| 11 | POST | `/api/auth/otp/resend` | RedirectIfAuthenticated; ThrottleRequests:otp-resend | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Auth\OtpController@resend` |
| 12 | POST | `/api/auth/otp/verify` | RedirectIfAuthenticated; ThrottleRequests:otp-verify | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Auth\OtpController@verify` |
| 13 | POST | `/api/auth/register` | RedirectIfAuthenticated | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Auth\RegistrationController@register` |
| 14 | POST | `/api/auth/reset-password` | RedirectIfAuthenticated | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Auth\PasswordController@resetPassword` |
| 15 | GET | `/api/brands` | عام | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Catalog\BrandController@index` |
| 16 | POST | `/api/brands` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Catalog\BrandController@store` |
| 17 | GET | `/api/brands/{brand}` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\BrandController@show` |
| 18 | PUT | `/api/brands/{brand}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\BrandController@update` |
| 19 | DELETE | `/api/brands/{brand}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\BrandController@destroy` |
| 20 | GET | `/api/categories` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\CategoryController@index` |
| 21 | POST | `/api/categories` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\CategoryController@store` |
| 22 | GET | `/api/categories/{category}` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\CategoryController@show` |
| 23 | PUT | `/api/categories/{category}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\CategoryController@update` |
| 24 | DELETE | `/api/categories/{category}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\CategoryController@destroy` |
| 25 | GET | `/api/engineer/certificates` | Authenticate:sanctum; RoleMiddleware:engineer | اختبار Laravel Feature | `App\Http\Controllers\Api\Enginner\EngineerCertificateController@index` |
| 26 | POST | `/api/engineer/certificates` | Authenticate:sanctum; RoleMiddleware:engineer; PermissionMiddleware:engineer-certificates.create; EngineerCertificate | اختبار Laravel Feature | `App\Http\Controllers\Api\Enginner\EngineerCertificateController@store` |
| 27 | DELETE | `/api/engineer/certificates/{certificate}` | Authenticate:sanctum; RoleMiddleware:engineer; PermissionMiddleware:engineer-certificates.delete; Authorize:delete,certificate | اختبار Laravel Feature | `App\Http\Controllers\Api\Enginner\EngineerCertificateController@destroy` |
| 28 | POST | `/api/engineer/onboarding` | Authenticate:sanctum; RoleMiddleware:engineer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Auth\Engineer\EngineerOnboardingController@store` |
| 29 | GET | `/api/engineer/onboarding/status` | Authenticate:sanctum; RoleMiddleware:engineer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Auth\Engineer\EngineerOnboardingController@status` |
| 30 | GET | `/api/engineer/portfolio-items` | Authenticate:sanctum; RoleMiddleware:engineer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\PortfolioItemController@index` |
| 31 | POST | `/api/engineer/portfolio-items` | Authenticate:sanctum; RoleMiddleware:engineer; PermissionMiddleware:portfolio-items.create; PortfolioItem | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\PortfolioItemController@store` |
| 32 | PUT | `/api/engineer/portfolio-items/{portfolioItem}` | Authenticate:sanctum; RoleMiddleware:engineer; PermissionMiddleware:portfolio-items.update; Authorize:update,portfolioItem | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\PortfolioItemController@update` |
| 33 | DELETE | `/api/engineer/portfolio-items/{portfolioItem}` | Authenticate:sanctum; RoleMiddleware:engineer; PermissionMiddleware:portfolio-items.delete; Authorize:delete,portfolioItem | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\PortfolioItemController@destroy` |
| 34 | GET | `/api/engineer/profile` | Authenticate:sanctum; RoleMiddleware:engineer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\EngineerProfileController@myProfile` |
| 35 | PUT | `/api/engineer/profile` | Authenticate:sanctum; RoleMiddleware:engineer; PermissionMiddleware:engineer-profiles.update | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\EngineerProfileController@update` |
| 36 | GET | `/api/engineers` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\EngineerProfileController@index` |
| 37 | GET | `/api/engineers/{engineer}` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\EngineerProfileController@show` |
| 38 | GET | `/api/engineers/{engineer}/portfolio-items` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Enginner\PortfolioItemController@publicIndex` |
| 39 | GET | `/api/favorites` | Authenticate:sanctum; RoleMiddleware:customer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Favorite\FavoriteController@index` |
| 40 | POST | `/api/favorites` | Authenticate:sanctum; RoleMiddleware:customer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Favorite\FavoriteController@store` |
| 41 | DELETE | `/api/favorites/{store_product_id}` | Authenticate:sanctum; RoleMiddleware:customer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Favorite\FavoriteController@destroy` |
| 42 | POST | `/api/favorites/{store_product_id}/toggle` | Authenticate:sanctum; RoleMiddleware:customer | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Favorite\FavoriteController@toggle` |
| 43 | GET | `/api/governorates` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\GovernorateController@index` |
| 44 | POST | `/api/governorates` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\GovernorateController@store` |
| 45 | GET | `/api/governorates/{governorate}` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\GovernorateController@show` |
| 46 | PUT | `/api/governorates/{governorate}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\GovernorateController@update` |
| 47 | DELETE | `/api/governorates/{governorate}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\GovernorateController@destroy` |
| 48 | GET | `/api/master-products` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\MasterProductController@index` |
| 49 | POST | `/api/master-products` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\MasterProductController@store` |
| 50 | GET | `/api/master-products/{masterProduct}` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\MasterProductController@show` |
| 51 | PUT | `/api/master-products/{masterProduct}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\MasterProductController@update` |
| 52 | DELETE | `/api/master-products/{masterProduct}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\MasterProductController@destroy` |
| 53 | DELETE | `/api/my/account` | Authenticate:sanctum | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\User\UserController@destroy` |
| 54 | GET | `/api/my/cart` | Authenticate:sanctum; RoleMiddleware:customer | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Cart\CartController@show` |
| 55 | DELETE | `/api/my/cart` | Authenticate:sanctum; RoleMiddleware:customer | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Cart\CartController@destroy` |
| 56 | POST | `/api/my/cart/items` | Authenticate:sanctum; RoleMiddleware:customer | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Cart\CartItemController@store` |
| 57 | PUT | `/api/my/cart/items/{cartItem}` | Authenticate:sanctum; RoleMiddleware:customer | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Cart\CartItemController@update` |
| 58 | DELETE | `/api/my/cart/items/{cartItem}` | Authenticate:sanctum; RoleMiddleware:customer | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Cart\CartItemController@destroy` |
| 59 | GET | `/api/my/notifications` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Notification\NotificationController@index` |
| 60 | PATCH | `/api/my/notifications/read-all` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Notification\NotificationController@markAllAsRead` |
| 61 | PATCH | `/api/my/notifications/{notification}/read` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Notification\NotificationController@markAsRead` |
| 62 | GET | `/api/my/offers` | Authenticate:sanctum; RoleMiddleware:engineer | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\OfferController@myOffers` |
| 63 | GET | `/api/my/orders` | Authenticate:sanctum; RoleMiddleware:customer | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Order\OrderController@index` |
| 64 | POST | `/api/my/orders` | Authenticate:sanctum; PermissionMiddleware:orders.create | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Order\OrderController@store` |
| 65 | GET | `/api/my/orders/{order}` | Authenticate:sanctum; PermissionMiddleware:orders.view; Authorize:view,order | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Order\OrderController@show` |
| 66 | PATCH | `/api/my/orders/{order}/cancel` | Authenticate:sanctum; PermissionMiddleware:orders.cancel; Authorize:cancel,order | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Order\OrderController@cancel` |
| 67 | PUT | `/api/my/profile` | Authenticate:sanctum | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\User\UserController@updateProfile` |
| 68 | GET | `/api/my/quote-requests` | Authenticate:sanctum; RoleMiddleware:customer | اختبار Laravel Feature | `App\Http\Controllers\Api\QuoteRequestController@myRequests` |
| 69 | GET | `/api/my/service-requests` | Authenticate:sanctum; RoleMiddleware:customer | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\ServiceRequestController@myRequests` |
| 70 | GET | `/api/my/store-orders` | Authenticate:sanctum; RoleMiddleware:supplier | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Order\OrderStoreController@index` |
| 71 | GET | `/api/my/store-products` | Authenticate:sanctum; RoleMiddleware:supplier | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Store\StoreProductController@myListings` |
| 72 | GET | `/api/my/store-quote-requests` | Authenticate:sanctum; RoleMiddleware:supplier | اختبار Laravel Feature | `App\Http\Controllers\Api\QuoteRequestController@myStoreRequests` |
| 73 | GET | `/api/my/wallets` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Wallet\UserWalletController@index` |
| 74 | POST | `/api/my/wallets` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Wallet\UserWalletController@store` |
| 75 | GET | `/api/my/wallets/{userWallet}` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Wallet\UserWalletController@show` |
| 76 | PUT | `/api/my/wallets/{userWallet}` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Wallet\UserWalletController@update` |
| 77 | DELETE | `/api/my/wallets/{userWallet}` | Authenticate:sanctum | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Wallet\UserWalletController@destroy` |
| 78 | GET | `/api/notification-templates` | Authenticate:sanctum; PermissionMiddleware:notification-templates.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Notification\NotificationTemplateController@index` |
| 79 | POST | `/api/notification-templates` | Authenticate:sanctum; PermissionMiddleware:notification-templates.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Notification\NotificationTemplateController@store` |
| 80 | GET | `/api/notification-templates/{notificationTemplate}` | Authenticate:sanctum; PermissionMiddleware:notification-templates.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Notification\NotificationTemplateController@show` |
| 81 | PUT | `/api/notification-templates/{notificationTemplate}` | Authenticate:sanctum; PermissionMiddleware:notification-templates.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Notification\NotificationTemplateController@update` |
| 82 | DELETE | `/api/notification-templates/{notificationTemplate}` | Authenticate:sanctum; PermissionMiddleware:notification-templates.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Notification\NotificationTemplateController@destroy` |
| 83 | PUT | `/api/offers/{offer}` | Authenticate:sanctum; PermissionMiddleware:offers.update; EnsureProfileIsApproved; Authorize:update,offer | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\OfferController@update` |
| 84 | DELETE | `/api/offers/{offer}` | Authenticate:sanctum; PermissionMiddleware:offers.delete; EnsureProfileIsApproved; Authorize:delete,offer | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\OfferController@destroy` |
| 85 | PATCH | `/api/offers/{offer}/accept` | Authenticate:sanctum; PermissionMiddleware:offers.accept; Authorize:accept,offer | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\OfferController@accept` |
| 86 | GET | `/api/order-stores/{orderStore}` | Authenticate:sanctum; Authorize:view,orderStore | اختبار Laravel Feature | `App\Http\Controllers\Api\Order\OrderStoreController@show` |
| 87 | PATCH | `/api/order-stores/{orderStore}/confirm-delivery` | Authenticate:sanctum; RoleMiddleware:customer; Authorize:confirmDelivery,orderStore | اختبار Laravel Feature | `App\Http\Controllers\Api\Order\OrderStoreController@confirmDelivery` |
| 88 | PATCH | `/api/order-stores/{orderStore}/status` | Authenticate:sanctum; PermissionMiddleware:orders.manage-status; EnsureProfileIsApproved; Authorize:updateStatus,orderStore | اختبار Laravel Feature | `App\Http\Controllers\Api\Order\OrderStoreController@updateStatus` |
| 89 | POST | `/api/order_payments` | Authenticate:sanctum; RoleMiddleware:customer | اختبار Laravel Feature | `App\Http\Controllers\Api\Payment\OrderPaymentController@store` |
| 90 | GET | `/api/order_payments/{orderPayment}` | Authenticate:sanctum; Authorize:view,orderPayment | اختبار Laravel Feature | `App\Http\Controllers\Api\Payment\OrderPaymentController@show` |
| 91 | PATCH | `/api/order_payments/{orderPayment}/status` | Authenticate:sanctum; PermissionMiddleware:order-payments.verify | اختبار Laravel Feature | `App\Http\Controllers\Api\Payment\OrderPaymentController@updateStatus` |
| 92 | GET | `/api/orders/{order}/payments` | Authenticate:sanctum; Authorize:view,order | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Payment\OrderPaymentController@index` |
| 93 | POST | `/api/product-images` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\ProductImageController@store` |
| 94 | DELETE | `/api/product-images/{productImage}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\ProductImageController@destroy` |
| 95 | PATCH | `/api/product-images/{productImage}/feature` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\ProductImageController@markFeatured` |
| 96 | POST | `/api/product-specifications` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\ProductSpecificationController@store` |
| 97 | PUT | `/api/product-specifications/{productSpecification}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\ProductSpecificationController@update` |
| 98 | DELETE | `/api/product-specifications/{productSpecification}` | Authenticate:sanctum; PermissionMiddleware:catalog.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Catalog\ProductSpecificationController@destroy` |
| 99 | POST | `/api/quote-requests` | Authenticate:sanctum; PermissionMiddleware:quote-requests.create | اختبار Laravel Feature | `App\Http\Controllers\Api\QuoteRequestController@store` |
| 100 | PATCH | `/api/quote-requests/{quoteRequest}/accept` | Authenticate:sanctum; PermissionMiddleware:quote-requests.accept; Authorize:accept,quoteRequest | اختبار Laravel Feature | `App\Http\Controllers\Api\QuoteRequestController@accept` |
| 101 | PATCH | `/api/quote-requests/{quoteRequest}/reject` | Authenticate:sanctum; PermissionMiddleware:quote-requests.reject; Authorize:reject,quoteRequest | اختبار Laravel Feature | `App\Http\Controllers\Api\QuoteRequestController@reject` |
| 102 | PATCH | `/api/quote-requests/{quoteRequest}/respond` | Authenticate:sanctum; PermissionMiddleware:quote-requests.respond; EnsureProfileIsApproved; Authorize:respond,quoteRequest | اختبار Laravel Feature | `App\Http\Controllers\Api\QuoteRequestController@respond` |
| 103 | POST | `/api/service-requests` | Authenticate:sanctum; PermissionMiddleware:service-requests.create | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\ServiceRequestController@store` |
| 104 | GET | `/api/service-requests/open` | Authenticate:sanctum; PermissionMiddleware:service-requests.view-open; EnsureProfileIsApproved | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\ServiceRequest\ServiceRequestController@openRequests` |
| 105 | PUT | `/api/service-requests/{serviceRequest}` | Authenticate:sanctum; PermissionMiddleware:service-requests.update; Authorize:update,serviceRequest | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\ServiceRequestController@update` |
| 106 | PATCH | `/api/service-requests/{serviceRequest}/cancel` | Authenticate:sanctum; PermissionMiddleware:service-requests.cancel; Authorize:cancel,serviceRequest | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\ServiceRequestController@cancel` |
| 107 | POST | `/api/service-requests/{serviceRequest}/offers` | Authenticate:sanctum; PermissionMiddleware:offers.create; EnsureProfileIsApproved | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\OfferController@store` |
| 108 | GET | `/api/service-requests/{serviceRequest}/offers` | Authenticate:sanctum; RoleMiddleware:customer; Authorize:view,serviceRequest | اختبار Laravel Feature | `App\Http\Controllers\Api\ServiceRequest\OfferController@requestOffers` |
| 109 | GET | `/api/service-types` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\ServiceRequest\ServiceTypeController@index` |
| 110 | GET | `/api/settings` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Settings\PlatformSettingController@show` |
| 111 | PUT | `/api/settings` | Authenticate:sanctum; PermissionMiddleware:settings.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Settings\PlatformSettingController@update` |
| 112 | GET | `/api/store-products` | عام | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreProductController@index` |
| 113 | POST | `/api/store-products` | Authenticate:sanctum; PermissionMiddleware:store-products.create; EnsureProfileIsApproved; StoreProduct | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreProductController@store` |
| 114 | GET | `/api/store-products/{storeProduct}` | عام | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreProductController@show` |
| 115 | PUT | `/api/store-products/{storeProduct}` | Authenticate:sanctum; PermissionMiddleware:store-products.update; EnsureProfileIsApproved; Authorize:update,storeProduct | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreProductController@update` |
| 116 | DELETE | `/api/store-products/{storeProduct}` | Authenticate:sanctum; PermissionMiddleware:store-products.delete; EnsureProfileIsApproved; Authorize:delete,storeProduct | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreProductController@destroy` |
| 117 | POST | `/api/store-ratings` | Authenticate:sanctum; PermissionMiddleware:store-ratings.create | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Store\StoreRatingController@store` |
| 118 | PUT | `/api/store-ratings/{storeRating}` | Authenticate:sanctum; PermissionMiddleware:store-ratings.update | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Store\StoreRatingController@update` |
| 119 | DELETE | `/api/store-ratings/{storeRating}` | Authenticate:sanctum; PermissionMiddleware:store-ratings.delete | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Store\StoreRatingController@destroy` |
| 120 | GET | `/api/store_payouts` | Authenticate:sanctum; PermissionMiddleware:store-payouts.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Payment\StorePayoutController@index` |
| 121 | POST | `/api/store_payouts` | Authenticate:sanctum; PermissionMiddleware:store-payouts.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Payment\StorePayoutController@store` |
| 122 | GET | `/api/store_payouts/{storePayout}` | Authenticate:sanctum; PermissionMiddleware:store-payouts.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Payment\StorePayoutController@show` |
| 123 | PUT | `/api/store_payouts/{storePayout}` | Authenticate:sanctum; PermissionMiddleware:store-payouts.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Payment\StorePayoutController@update` |
| 124 | GET | `/api/stores` | عام | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\Store\StoreController@index` |
| 125 | POST | `/api/stores` | Authenticate:sanctum; RoleMiddleware:supplier; PermissionMiddleware:stores.create; Store | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Auth\Store\StoreOnboardingController@store` |
| 126 | GET | `/api/stores/{store}` | عام | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreController@show` |
| 127 | PUT | `/api/stores/{store}` | Authenticate:sanctum; PermissionMiddleware:stores.update; Authorize:update,store | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreController@update` |
| 128 | GET | `/api/stores/{store}/ratings` | عام | اختبار Laravel Feature | `App\Http\Controllers\Api\Store\StoreRatingController@storeRatings` |
| 129 | GET | `/api/users` | Authenticate:sanctum; PermissionMiddleware:users.manage | طلب HTTP فعلي (حالة ممثلة) | `App\Http\Controllers\Api\User\UserController@index` |
| 130 | GET | `/api/users/{user}` | Authenticate:sanctum; PermissionMiddleware:users.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\User\UserController@show` |
| 131 | PATCH | `/api/users/{user}/status` | Authenticate:sanctum; PermissionMiddleware:users.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\User\UserController@updateStatus` |
| 132 | GET | `/api/wallet_providers` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Wallet\WalletProviderController@index` |
| 133 | POST | `/api/wallet_providers` | Authenticate:sanctum; PermissionMiddleware:wallet-providers.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Wallet\WalletProviderController@store` |
| 134 | GET | `/api/wallet_providers/{walletProvider}` | عام | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Wallet\WalletProviderController@show` |
| 135 | PUT | `/api/wallet_providers/{walletProvider}` | Authenticate:sanctum; PermissionMiddleware:wallet-providers.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Wallet\WalletProviderController@update` |
| 136 | DELETE | `/api/wallet_providers/{walletProvider}` | Authenticate:sanctum; PermissionMiddleware:wallet-providers.manage | فحص كود فقط — غير منفذ HTTP | `App\Http\Controllers\Api\Wallet\WalletProviderController@destroy` |