# Prompt 1 Feature Breakdown

این سند Prompt 1 را به فیچرهای کوچک، قابل بررسی، قابل branch و قابل merge تبدیل می‌کند.

## وضعیت فعلی

- ریپوی Git در مسیر `/var/www/art-site` آماده شده است.
- WordPress core و فایل‌های runtime از Git خارج شده‌اند.
- پلاگین `art-cms` بر اساس Prompt 2 ساخته شده است.
- در Prompt 1 نام tentative پلاگین `art-core` بود، اما در Prompt 2 تصمیم پروژه به `art-cms` تغییر کرد. از اینجا به بعد `art-cms` را پلاگین محتوایی پروژه در نظر می‌گیریم مگر اینکه تصمیم جدیدی گرفته شود.
- هنوز theme اختصاصی `art-theme` ساخته نشده است.
- هنوز README ریشه پروژه و فایل deployment template ساخته نشده‌اند.

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

وضعیت: آماده شروع.

## Feature 4: Custom Theme Foundation

هدف: ساخت theme اختصاصی حداقلی `art-theme` برای presentation، بدون پیاده‌سازی full website.

کارهای اصلی:

- ساخت `wp-content/themes/art-theme`.
- افزودن فایل‌های پایه: `style.css`, `functions.php`, `index.php`, `header.php`, `footer.php`.
- ساخت ساختار سبک و قابل رشد مانند `assets/`, `inc/`, `template-parts/`, `templates/`.
- عدم پیاده‌سازی طراحی نهایی، صفحات کامل یا page-builder behavior.
- اجرای PHP syntax check.

وضعیت: انجام نشده.

## Feature 5: Safe cPanel Deployment Template

هدف: آماده‌سازی یک الگوی امن برای deployment آینده از GitHub به cPanel بدون hardcode کردن مسیر production.

کارهای اصلی:

- تصمیم‌گیری درباره نیاز به `.cpanel.yml` یا فقط documentation.
- اگر فایل ساخته شد، آن را template-safe نگه داریم.
- مسیر production واقعی یا secret داخل آن قرار نگیرد.
- تاکید شود deployment فقط کد اختصاصی را منتقل کند، نه DB/uploads.

وضعیت: انجام نشده.

## Feature 6: Admin Menu Organization

هدف: مرتب کردن تجربه ادمین برای محتوای پروژه زیر یک منوی واحد.

کارهای اصلی:

- ساخت یک parent menu مثل `Art CMS`.
- قرار دادن `Products`, `Gallery Items`, `Contact Submissions` زیر همان منو.
- حفظ جدایی data model از presentation.
- عدم افزودن role/capability logic مگر با تصمیم جداگانه.

وضعیت: انجام نشده.

## پیشنهاد فیچر بعدی

بهترین فیچر بعدی `Project README` است، چون قبل از ساخت theme یا deployment template، باید قرارداد پروژه و workflow را در ریشه ریپو بنویسیم.

بعد از آن، مسیر منطقی این است:

1. `feature/project-readme`
2. `feature/art-theme-foundation`
3. `feature/cpanel-deployment-template`
4. `feature/admin-menu-organization`
