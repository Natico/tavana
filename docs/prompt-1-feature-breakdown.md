# Prompt 1 Feature Breakdown

این سند Prompt 1 را به فیچرهای کوچک، قابل بررسی، قابل branch و قابل merge تبدیل می‌کند.

## وضعیت فعلی

- ریپوی Git در مسیر `/var/www/art-site` آماده شده است.
- WordPress core و فایل‌های runtime از Git خارج شده‌اند.
- پلاگین `art-cms` بر اساس Prompt 2 ساخته شده است.
- در Prompt 1 نام tentative پلاگین `art-core` بود، اما در Prompt 2 تصمیم پروژه به `art-cms` تغییر کرد. از اینجا به بعد `art-cms` را پلاگین محتوایی پروژه در نظر می‌گیریم مگر اینکه تصمیم جدیدی گرفته شود.
- theme اختصاصی `art-theme` ساخته شده است.
- README ریشه پروژه و فایل deployment template امن ساخته شده‌اند.
- منوهای محتوایی پلاگین زیر parent menu با عنوان `ART` گروه‌بندی شده‌اند.
- همه تغییرات foundation تا این مرحله روی `main` هستند.

## Feature 1: Repository And Ignore Foundation

هدف: مطمئن شویم Git فقط کدهای اختصاصی پروژه را track می‌کند و WordPress core، فایل‌های کانفیگ، uploads، cache و پلاگین/تم‌های ثالث وارد ریپو نمی‌شوند.

کارهای اصلی:

- بررسی ساختار WordPress موجود.
- ساخت یا اصلاح `.gitignore`.
- اطمینان از اینکه `wp-config.php`، `wp-admin/`، `wp-includes/`، uploads و cache وارد Git نمی‌شوند.
- نگه داشتن فقط کدهای اختصاصی پروژه داخل Git.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 2: Content Plugin Foundation

هدف: ساخت پایه پلاگین محتوایی پروژه برای business/content logic.

کارهای اصلی:

- ساخت پلاگین اختصاصی `art-cms`.
- افزودن bootstrap کوچک.
- افزودن کلاس مرکزی plugin.
- ثبت CPTهای اولیه و taxonomyهای اولیه.
- اضافه کردن activation/deactivation hook امن.
- اجرای syntax check.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 3: Project README

هدف: ساخت README ریشه پروژه برای توضیح مسیرها، workflow و محدودیت‌های deployment.

کارهای اصلی:

- توضیح نام پروژه.
- ثبت local URL: `http://art-site.local`.
- ثبت local path: `/var/www/art-site`.
- ثبت PHP requirement: PHP 8.3.
- توضیح مسیر پلاگین: `wp-content/plugins/art-cms`.
- توضیح مسیر theme بعد از ساخت: `wp-content/themes/art-theme`.
- توضیح اینکه WordPress core، uploads، secrets و DB dumps وارد Git نمی‌شوند.
- توضیح workflow توسعه: `main`، feature branch، commit، push، merge.
- هشدار درباره production DB/uploads.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 4: Custom Theme Foundation

هدف: ساخت theme اختصاصی حداقلی `art-theme` برای presentation، بدون پیاده‌سازی full website.

کارهای اصلی:

- ساخت `wp-content/themes/art-theme`.
- افزودن فایل‌های پایه: `style.css`, `functions.php`, `index.php`, `header.php`, `footer.php`.
- ساخت ساختار سبک و قابل رشد مانند `assets/`, `inc/`, `template-parts/`, `templates/`.
- عدم پیاده‌سازی طراحی نهایی، صفحات کامل یا page-builder behavior.
- اجرای PHP syntax check.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 5: Safe cPanel Deployment Template

هدف: آماده‌سازی یک الگوی امن برای deployment آینده از GitHub به cPanel بدون hardcode کردن مسیر production.

کارهای اصلی:

- تصمیم‌گیری درباره نیاز به `.cpanel.yml` یا فقط documentation.
- اگر فایل ساخته شد، آن را template-safe نگه داریم.
- مسیر production واقعی یا secret داخل آن قرار نگیرد.
- تاکید شود deployment فقط کد اختصاصی را منتقل کند، نه DB/uploads.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 6: Admin Menu Organization

هدف: مرتب کردن تجربه ادمین برای محتوای پروژه زیر یک منوی واحد.

کارهای اصلی:

- ساخت یک parent menu مثل `Art CMS`.
- قرار دادن `Products`, `Gallery Items`, `Contact Submissions` زیر همان منو.
- حفظ جدایی data model از presentation.
- عدم افزودن role/capability logic مگر با تصمیم جداگانه.

وضعیت: انجام شده و روی `main` merge شده است.

## Remaining Future Features

موارد زیر بخشی از foundation اولیه نبودند یا در promptها صراحتا به عنوان «فعلا پیاده نشود» آمده بودند. هرکدام باید در یک feature branch جدا بررسی و پیاده‌سازی شوند.

## Feature 7: Content Admin Polish

هدف: بهتر کردن تجربه مدیریت محتوا در ادمین وردپرس بدون تغییر مدل داده اصلی.

کارهای احتمالی:

- بازبینی labelها و menu order.
- افزودن ستون‌های ادمین برای CPTها در صورت نیاز.
- بهبود پیام‌ها و راهنمایی‌های admin-only.
- حفظ scope: بدون meta field جدید، role/capability جدید یا frontend template.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 8: Theme Basic Layout

هدف: تبدیل theme پایه `art-theme` از اسکلت خام به layout ساده و قابل استفاده.

کارهای احتمالی:

- بهبود header/footer پایه.
- تعریف container و typography اولیه.
- افزودن ساختار CSS ساده در محدوده theme.
- ساخت templateهای پایه فقط در حد presentation.
- حفظ scope: بدون page-builder behavior و بدون اتصال business logic به theme.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 9: Contact Submission Fields

هدف: مشخص کردن ساختار داده‌ای contact submissionها در ادمین.

کارهای احتمالی:

- تصمیم‌گیری درباره fieldهای لازم.
- افزودن meta box یا ساختار ذخیره‌سازی مناسب.
- نمایش خوانا در admin.
- حفظ scope: هنوز فرم frontend ساخته نشود مگر با feature جداگانه.

وضعیت: انجام شده و روی `main` merge شده است.

## Feature 10: Product And Gallery Meta Planning

هدف: طراحی مرحله بعدی مدل محتوایی محصولات و گالری بدون عجله در پیاده‌سازی.

کارهای احتمالی:

- فهرست کردن meta fieldهای احتمالی برای product و gallery item.
- تعیین اینکه چه چیزهایی taxonomy هستند و چه چیزهایی meta.
- بررسی نیاز به media relationships.
- تبدیل تصمیم‌ها به featureهای کوچک‌تر.

وضعیت: انجام شده و روی branch مربوطه آماده review است.

## Feature 11: Product Basic Fields

هدف: پیاده‌سازی اولین مجموعه تاییدشده فیلدهای محصول بر اساس `docs/product-gallery-meta-planning.md`.

کارهای احتمالی:

- افزودن meta box محصول.
- ذخیره امن subtitle، material، dimensions، production_year و availability_note.
- تصمیم درباره strict یا flexible بودن `production_year`.
- عدم افزودن ecommerce، قیمت‌گذاری، inventory یا frontend template.

وضعیت: انجام نشده.

## Feature 12: Gallery Basic Fields

هدف: پیاده‌سازی اولین مجموعه تاییدشده فیلدهای گالری بر اساس `docs/product-gallery-meta-planning.md`.

کارهای احتمالی:

- افزودن meta box گالری.
- ذخیره امن subtitle، artwork_date، medium، dimensions و credit_line.
- عدم افزودن media relationship پیچیده، ordering یا routing.

وضعیت: انجام نشده.

## Explicitly Deferred Items

این موارد طبق prompt اولیه هنوز عمدا پیاده‌سازی نشده‌اند:

- multilingual
- SEO
- template system
- validation
- ordering
- duplicate handling
- custom routing
- writer role / custom capability logic
- media relationships
- contact form
- custom frontend pages
