# تقرير تدقيق واختبار SolarHub API

تاريخ التنفيذ: 2026-09-29. بيئة التنفيذ: Laravel 12.68 / PHP 8.2.12 / SQLite معزولة في `database/solarhub-testing.sqlite` وخادم محلي `127.0.0.1:8765`.

## نطاق التنفيذ الصريح

- **فحص الكود:** تم جرد `routes/api.php` وملفات `routes/api/*`، Controllers، Form Requests، Services، Policies، Middleware، Seeders، migrations والاختبارات. القائمة الرسمية الناتجة من Laravel تحتوي 136 مسارًا، وهي في `postman/ROUTES-AUDIT-AR.md` مع الـmiddleware وحالة التنفيذ لكل مسار.
- **اختبار Laravel منفذ:** 58 اختبارًا؛ نجح 57 وفشل اختبار القالب `ExampleTest` فقط لأن Windows منع Laravel من كتابة ملف Blade مؤقت داخل `storage/framework/views`. اختبارات الـAPI والصلاحيات والملكية وOTP والإصلاحات الجديدة نجحت.
- **HTTP فعلي منفذ:** أُرسلت طلبات فعلية للخادم المحلي لمسارات عامة، دخول الأدوار، البريد والجوال، 401 و403 و404 و422، السلة والطلبات والمحافظ والإشعارات والموارد المفتوحة، ثم logout وإعادة استخدام الرمز. سجل الخادم محفوظ خارج المشروع في سجل جلسة التدقيق.
- **غير منفذ فعليًا:** لم تُنفذ كل التركيبات الممكنة لكل واحد من المسارات الـ136 عبر Newman؛ Newman/Postman CLI غير مثبت، و`npx newman` لم يكتمل في الشبكة المقيدة. المسارات التي لم يصلها HTTP موسومة بوضوح «فحص كود فقط» في جدول الجرد، وليست نجاحًا مزعومًا.

## أدلة HTTP المختصرة

| الحالة | الطلب | النتيجة |
|---|---|---|
| دخول المشرف بالبريد | `POST /api/auth/login` | 200 |
| دخول العميل بالجوال عبر `login` | `POST /api/auth/login` | 200 |
| بلا رمز | `GET /api/auth/me` | 401 `Unauthenticated` |
| عميل إلى إدارة المستخدمين | `GET /api/users` | 403 |
| المشرف إلى إدارة المستخدمين | `GET /api/users` | 200 |
| متجر غير موجود | `GET /api/stores/999999` | 404 |
| منتج سلة غير موجود وكمية صفر | `POST /api/my/cart/items` | 422 |
| سلة العميل | `GET /api/my/cart` | 200 |
| طلبات العميل | `GET /api/my/orders` | 200 |
| محافظ العميل | `GET /api/my/wallets` | 200 |
| تسجيل الخروج | `POST /api/auth/logout` | 200 |
| إعادة استخدام الرمز المبطل | `GET /api/auth/me` | 401 |

حد المعدل الخاص بالدخول اختُبر في Feature: خمس محاولات غير صحيحة أعادت 422، والسادسة أعادت 429. اختبارات المشروع غطّت منع ملكية الطلبات والمتاجر والمنتجات والشهادات والعروض وعروض الأسعار والمدفوعات، وحالة اعتماد المورد والمهندس.

## جدول Tokens

لا يحتوي أي ملف مصدر على رمز كامل أو كلمة مرور حقيقية. القيم التجريبية تُمرر وقت التشغيل فقط.

| الدور | المستخدم التجريبي | مصدر إنشاء الرمز | متغير Postman | المسارات المستخدمة | نتيجة الصلاحيات | الإبطال بعد logout |
|---|---|---|---|---|---|---|
| admin | `admin@solarhub.com` | `POST /api/auth/login` | `admin_token` | `/users`, `/admin/stores/pending` | 200 للإدارة | 200 logout؛ الرمز حُذف |
| customer | بريد مصنع + الجوال `737…730` | login بالبريد وبالجوال | `customer_token` | `/auth/me`, `/my/cart`, `/my/orders`, `/my/wallets`, `/users` | 200 لموارده و403 للإدارة | 200 ثم 401 عند إعادة الاستخدام |
| supplier | بريد مصنع (كلمة مرور factory وقت التشغيل) | login | `supplier_token` | `/my/store-products`, `/users`, `/my/cart` | 200 لموارده، 403 للإدارة/دور العميل | تم logout بنجاح في الجولة الكاملة |
| engineer | بريد مصنع (كلمة مرور factory وقت التشغيل) | login | `engineer_token` | `/service-requests/open`, `/users` | 200 للمهندس المعتمد، 403 للإدارة | تم logout بنجاح في الجولة الكاملة |

بعد الجولات بقي صفّان صالحان في `personal_access_tokens` من إعادة تشغيل تسجيل الدخول التشخيصي، لمستخدمين اثنين، و`orphan_tokens=0`. العلاقة morph إلى `App\\Models\\User` سليمة. الرموز التي استُخدمت في logout حُذفت وأعادت 401 لاحقًا.

## العيوب والإصلاحات

1. `database/seeders/AdminUserSeeder.php:30` و`:37`: إضافة `email_verified_at` عند الإنشاء وترقية حساب مشرف قديم غير موثّق، لأن خدمة الدخول ترفض غير الموثّق بـ403.
2. `app/Providers/AppServiceProvider.php:30` و`:36`: تعريف محددي `login` و`forgot-password` اللذين كانت routes تشير إليهما دون تعريف؛ حد الدخول 5/دقيقة والاستعادة 3/دقيقة بمفتاح مجزأ يجمع المعرف وIP.
3. `database/migrations/2026_09_29_000000_create_personal_access_tokens_table.php:11`: إضافة جدول Sanctum المفقود؛ غيابه كان يجعل الدخول الحقيقي يفشل 500 عند `createToken`.
4. `tests/Feature/Auth/AdminLoginTest.php`: اختبار أن المشرف المزروع موثّق ويستطيع إنشاء رمز، واختبار 429 لمحدد الدخول.

## أوامر إعادة التشغيل

```powershell
cd C:\xamppp\htdocs\product_wep\solarhub-api
New-Item -ItemType File database\solarhub-testing.sqlite -Force
$env:APP_ENV='testing'
$env:APP_KEY='base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA='
$env:DB_CONNECTION='sqlite'
$env:DB_DATABASE=(Resolve-Path database\solarhub-testing.sqlite).Path
$env:CACHE_STORE='array'; $env:SESSION_DRIVER='array'; $env:QUEUE_CONNECTION='sync'
$env:MAIL_MAILER='array'; $env:LOG_CHANNEL='null'; $env:FILESYSTEM_DISK='local'
$env:SOLARHUB_ADMIN_PASSWORD='<TEST-ONLY-PASSWORD>'
php artisan migrate:fresh --seed --force
php artisan test --do-not-cache-result
php artisan serve --host=127.0.0.1 --port=8765
```

بعد تشغيل الخادم استورد `postman/SolarHub-API.postman_collection.json` و`postman/SolarHub-Local.postman_environment.json`. املأ كلمات المرور والرموز محليًا فقط، أو نفّذ:

```powershell
newman run postman\SolarHub-API.postman_collection.json -e postman\SolarHub-Local.postman_environment.json
```

## ملاحظات وحدود

- لم يُستخدم `migrate:fresh` على `.env` أو قاعدة قائمة؛ استُهدف ملف SQLite المذكور صراحة.
- اختبارات رفع الملفات عبر HTTP لم تكتمل لكل الموارد بسبب عدم توفر Newman وبسبب اعتماد خدمات التخزين الخارجية في بعض المسارات؛ قواعد MIME والحجم فُحصت من Form Requests فقط حيث لم يُرسل الملف.
- إعادة تعيين كلمة المرور لم تُستكمل end-to-end من بريد فعلي؛ mailer كان `array` لعزل الاختبار. منطق إبطال كل رموز المستخدم بعد reset فُحص في الكود ولم يُسجل كنجاح HTTP.
- لا توجد علامة نجاح للمسارات غير المنفذة؛ جدول المسارات يحددها صراحة.
